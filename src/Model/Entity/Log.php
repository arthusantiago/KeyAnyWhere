<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Log\Gerenciador\LogLevelInt;

/**
 * Log Entity
 *
 * @property int $id
 * @property string|null $evento
 * @property int $nivel_severidade
 * @property string|null $recurso
 * @property string|null $ip_origem
 * @property string|null $usuario
 * @property string $mensagem
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Log extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'evento' => true,
        'nivel_severidade' => true,
        'recurso' => true,
        'ip_origem' => true,
        'usuario' => true,
        'mensagem' => true,
        'analisado' => true,
        'created' => true,
        'modified' => true,
    ];

    public function stringNivelSeveridade(): string
    {
        return ucfirst(LogLevelInt::toString($this->nivel_severidade));
    }
}
