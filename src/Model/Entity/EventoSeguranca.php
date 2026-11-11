<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Log\Seguranca;
use Cake\ORM\Entity;
use function Cake\I18n\__;

/**
 * Um evento de segurança mostrado ao usuário (tabela `eventos_seguranca`). Gravado só pelo
 * App\Log\Engine\EventosSegurancaLog, nunca editado pela aplicação.
 *
 * @property int $id
 * @property string $evento Uma das constantes de App\Log\Seguranca.
 * @property string $nivel Nível PSR-3 (`notice`, `warning`, `alert`).
 * @property int|null $usuario_id A conta afetada.
 * @property string|null $ip
 * @property \Cake\I18n\DateTime $created
 * @property \App\Model\Entity\User|null $user
 */
class EventoSeguranca extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'evento' => true,
        'nivel' => true,
        'usuario_id' => true,
        'ip' => true,
        'created' => true,
    ];

    /**
     * Descrição do evento para a tela.
     *
     * @return string
     */
    public function titulo(): string
    {
        return match ($this->evento) {
            Seguranca::LOGIN_CREDENCIAL_INVALIDA => __('Login com senha incorreta'),
            Seguranca::LOGIN_2FA_INVALIDO => __('Login com código do segundo fator incorreto'),
            Seguranca::ACESSO_SOMENTE_ROOT => __('Tentativa de acessar uma tela de administrador'),
            Seguranca::XSS_DETECTADO => __('Conteúdo malicioso bloqueado'),
            default => $this->evento,
        };
    }

    /**
     * Classe do Bootstrap para o nível (badge na tela).
     *
     * @return string
     */
    public function classeNivel(): string
    {
        return match ($this->nivel) {
            'alert', 'critical', 'emergency' => 'text-bg-danger',
            'warning', 'error' => 'text-bg-warning',
            default => 'text-bg-secondary',
        };
    }
}
