<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use function Cake\I18n\__;

/**
 * Auditoria Entity
 *
 * Registro de auditoria de negócio — imutável por design (criado só pelo
 * AuditoriaBehavior, nunca editado nem excluído pela aplicação).
 *
 * @property int $id
 * @property string $contexto
 * @property int $id_entidade
 * @property string $acao
 * @property int|null $usuario_id
 * @property array<string, mixed>|null $alteracoes
 * @property string|null $ip_origem
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\User|null $user
 */
class Auditoria extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'contexto' => true,
        'id_entidade' => true,
        'acao' => true,
        'usuario_id' => true,
        'alteracoes' => true,
        'ip_origem' => true,
        'created' => true,
        'user' => true,
    ];

    /**
     * Quem fez a ação, para exibição. Precisa da associação `user` carregada (contain).
     *
     * `usuario_id` nulo é ação sem login (ex.: configuração inicial). Um `usuario_id` sem
     * usuário correspondente é de alguém excluído depois: o banco não tem FK de propósito,
     * para a trilha manter o autor (ver a migration 20261109100000).
     *
     * @return string
     */
    public function descricaoUsuario(): string
    {
        if ($this->user !== null) {
            return $this->user->username;
        }

        if ($this->usuario_id === null) {
            return __('Sem usuário autenticado');
        }

        return __('Usuário excluído (#{0})', $this->usuario_id);
    }
}
