<?php
declare(strict_types=1);

namespace App\Auditoria;

use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use LogicException;

/**
 * Liga o AuditoriaBehavior numa Table com a configuração padrão do KAW, em vez de
 * repetir o mesmo bloco de config em cada Table auditada:
 * - persistência via `registrar()` da Table de auditoria (RegistradorAuditoriaInterface);
 * - usuário: `identity` do request atual (plugin Authentication) — null sem login;
 * - IP: `clientIp()` do request atual.
 *
 * Uso:
 *
 *     class EntradasTable extends Table
 *     {
 *         use AuditavelTrait;
 *
 *         public function initialize(array $config): void
 *         {
 *             $this->adicionarAuditoria('entrada', mascararAlteracoes: true);
 *         }
 *     }
 *
 * @mixin \Cake\ORM\Table
 */
trait AuditavelTrait
{
    /**
     * @param string $contexto Valor da coluna `auditorias.contexto` (ex.: 'entrada').
     * @param bool $mascararAlteracoes Ver RegistradorAuditoriaInterface::registrar().
     * @param string $tabelaAuditorias Alias da Table que persiste a auditoria.
     * @return void
     */
    protected function adicionarAuditoria(
        string $contexto,
        bool $mascararAlteracoes = false,
        string $tabelaAuditorias = 'Auditorias',
    ): void {
        $this->addBehavior('Auditoria', [
            'className' => AuditoriaBehavior::class,
            'contexto' => $contexto,
            'callback' => function (RegistroAuditoria $registro) use ($mascararAlteracoes, $tabelaAuditorias): void {
                // Resolvida só na hora de registrar: buscar no initialize() poderia entrar em
                // recursão (Users é auditada e Auditorias belongsTo Users).
                $registrador = TableRegistry::getTableLocator()->get($tabelaAuditorias);
                if (!$registrador instanceof RegistradorAuditoriaInterface) {
                    throw new LogicException(sprintf(
                        'A Table "%s" precisa implementar %s (ver RegistraAuditoriaTrait).',
                        $tabelaAuditorias,
                        RegistradorAuditoriaInterface::class,
                    ));
                }

                $registrador->registrar($registro, $mascararAlteracoes);
            },
            'usuarioIdCallback' => fn() => Router::getRequest()?->getAttribute('identity')?->getIdentifier(),
            'ipOrigemCallback' => fn() => Router::getRequest()?->clientIp(),
        ]);
    }
}
