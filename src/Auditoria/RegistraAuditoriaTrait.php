<?php
declare(strict_types=1);

namespace App\Auditoria;

use Cake\Log\Log;

/**
 * Implementação padrão de RegistradorAuditoriaInterface, usada pela AuditoriasTable.
 *
 * A Table que usa este trait precisa da associação `Users` e da regra
 * `existsIn(['usuario_id'], 'Users')`: sem a regra, um `usuario_id` inexistente vira erro
 * de banco, que não é capturado aqui e desfaria o save de negócio. Com a regra, vira
 * `Log::error` e o save de negócio continua. `usuario_id` nulo (ação sem login) passa.
 *
 * @mixin \Cake\ORM\Table
 */
trait RegistraAuditoriaTrait
{
    /**
     * - `atomic => false`: roda dentro do afterSave do save de negócio. Um savepoint próprio
     *   que falhasse na validação corromperia a transação externa (NestedTransactionRollbackException).
     * - Falha não lança exceção — vira `Log::error`, porque um registro perdido é um buraco
     *   permanente na trilha de auditoria.
     * - `$mascararAlteracoes`: o KAW guarda segredos (credenciais) e a trilha é imutável;
     *   com a máscara, ficam só os nomes dos campos alterados.
     *
     * @param \App\Auditoria\RegistroAuditoria $registro Registro montado pelo behavior.
     * @param bool $mascararAlteracoes Troca antes/depois por '' mantendo só os nomes dos campos.
     * @return bool
     */
    public function registrar(RegistroAuditoria $registro, bool $mascararAlteracoes = false): bool
    {
        $dados = $registro->toArray();
        if ($mascararAlteracoes) {
            foreach (array_keys($dados['alteracoes']) as $campo) {
                $dados['alteracoes'][$campo] = ['antes' => '', 'depois' => ''];
            }
        }

        $auditoria = $this->newEntity($dados);
        if ($this->save($auditoria, ['atomic' => false])) {
            return true;
        }

        Log::error('Falha ao salvar auditoria', [
            'entidade' => $registro->getContexto(),
            'id_entidade' => $registro->getIdEntidade(),
            'acao' => $registro->getAcao(),
            'usuario_id' => $registro->getUsuarioId(),
            'erros' => $auditoria->getErrors(),
        ]);

        return false;
    }
}
