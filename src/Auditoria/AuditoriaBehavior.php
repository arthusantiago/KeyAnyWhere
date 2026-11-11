<?php
declare(strict_types=1);

namespace App\Auditoria;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use RuntimeException;

/**
 * Registra auditoria de negócio (quem fez o quê, quando, quais campos mudaram)
 * escutando os eventos afterSave/afterDelete do ORM. A persistência em si fica
 * no callback injetado na config — este behavior não conhece schema nem conexão.
 *
 * Config obrigatória: 'contexto', 'callback', 'usuarioIdCallback'.
 * Config opcional: 'camposIgnorados' (default: id/created/modified), 'ipOrigemCallback'.
 *
 * Ação customizada: passe $options['auditoria_acao'] no save() para registrar
 * uma ação de negócio específica em vez do CRUD genérico derivado
 * automaticamente ('criacao'/'edicao').
 *
 * Save/delete sem auditoria: passe $options['auditoria_acao'] = false para
 * suprimir o registro por completo (ex.: saves internos de manutenção que
 * não representam uma ação de negócio, como CategoriasTable::reordenar()).
 *
 * Normalmente ligado pelo AuditavelTrait, não direto.
 */
class AuditoriaBehavior extends Behavior
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'contexto' => null,
        'camposIgnorados' => ['id', 'created', 'modified'],
        'callback' => null,
        'usuarioIdCallback' => null,
        'ipOrigemCallback' => null,
    ];

    /**
     * isNew() já reflete o pós-save (sempre false) quando afterSave dispara —
     * por isso o estado "era nova?" é capturado antes, em beforeSave.
     */
    private bool $entidadeEraNova = false;

    /**
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \ArrayObject<string, mixed> $options
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->entidadeEraNova = $entity->isNew();
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \ArrayObject<string, mixed> $options
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (($options['auditoria_acao'] ?? null) === false) {
            return;
        }

        $acao = $options['auditoria_acao'] ?? ($this->entidadeEraNova ? 'criacao' : 'edicao');
        $this->registrar($entity, (string)$acao, $this->calcularAlteracoesSave($entity));
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \ArrayObject<string, mixed> $options
     * @return void
     */
    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (($options['auditoria_acao'] ?? null) === false) {
            return;
        }

        $acao = $options['auditoria_acao'] ?? 'exclusao';
        $this->registrar($entity, (string)$acao, $this->calcularAlteracoesDelete($entity));
    }

    /**
     * Diff dos campos sujos (getDirty) contra o valor original (getOriginal),
     * ignorando os campos configurados em 'camposIgnorados'.
     *
     * Numa criação, `antes` é sempre forçado para null — Entity::getOriginal()
     * não distingue "nunca existiu" de "não mudou", então sem esse tratamento
     * ele cai de volta para o valor atual e toda criação apareceria como se
     * `antes` e `depois` fossem idênticos em todo campo.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return array<string, array{antes: mixed, depois: mixed}>
     */
    private function calcularAlteracoesSave(EntityInterface $entity): array
    {
        $ignorados = $this->getConfig('camposIgnorados');
        $alteracoes = [];

        foreach ($entity->getDirty() as $campo) {
            if (in_array($campo, $ignorados, true)) {
                continue;
            }

            $alteracoes[$campo] = [
                'antes' => $this->entidadeEraNova ? null : $entity->getOriginal($campo),
                'depois' => $entity->get($campo),
            ];
        }

        return $alteracoes;
    }

    /**
     * Numa exclusão não há "depois" — captura o valor de cada campo (exceto
     * os ignorados) como estava no momento da exclusão.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return array<string, array{antes: mixed, depois: mixed}>
     */
    private function calcularAlteracoesDelete(EntityInterface $entity): array
    {
        $ignorados = $this->getConfig('camposIgnorados');
        $alteracoes = [];

        foreach ($entity->toArray() as $campo => $valor) {
            if (in_array($campo, $ignorados, true)) {
                continue;
            }

            $alteracoes[$campo] = [
                'antes' => $valor,
                'depois' => null,
            ];
        }

        return $alteracoes;
    }

    /**
     * Monta o RegistroAuditoria com base na config e invoca o callback.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @param string $acao
     * @param array<string, array{antes: mixed, depois: mixed}> $alteracoes
     * @return void
     */
    private function registrar(EntityInterface $entity, string $acao, array $alteracoes): void
    {
        $contexto = $this->getConfig('contexto');
        $callback = $this->getConfig('callback');
        $usuarioIdCallback = $this->getConfig('usuarioIdCallback');
        $ipOrigemCallback = $this->getConfig('ipOrigemCallback');

        if ($contexto === null || $callback === null || $usuarioIdCallback === null) {
            throw new RuntimeException(
                "AuditoriaBehavior requer as configs 'contexto', 'callback' e 'usuarioIdCallback'.",
            );
        }

        $primaryKey = $this->table()->getPrimaryKey();
        $campoChave = is_array($primaryKey) ? $primaryKey[0] : $primaryKey;

        // Sem identidade no request o callback retorna null — fazer (int) aqui
        // viraria usuario_id 0, um usuário que não existe (a regra existsIn
        // rejeitaria e o registro seria perdido).
        $usuarioId = $usuarioIdCallback();

        $registro = new RegistroAuditoria(
            $contexto,
            (int)$entity->get($campoChave),
            $acao,
            $usuarioId === null || $usuarioId === '' ? null : (int)$usuarioId,
            $alteracoes,
            $ipOrigemCallback !== null ? $ipOrigemCallback() : null,
        );

        $callback($registro);
    }
}
