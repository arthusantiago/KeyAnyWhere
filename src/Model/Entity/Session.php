<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Session Entity
 *
 * @property string $id
 * @property string|resource|null $data
 * @property int|null $expires
 * @property int|null $user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Session extends Entity
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
        'data' => true,
        'expires' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    protected function _setData($data)
    {
        if ($data && is_string($data)) {
            $propriedadeIdUser = '"id";i:';
            $posicao = stripos($data, $propriedadeIdUser);
            if ($posicao) {
                $idUser = substr($data, $posicao + 7, 1);
                $this->setAutomaticoUserId($idUser);
            }
        }

        return $data;
    }

    protected function setAutomaticoUserId($id)
    {
        $this->user_id = $id;
    }
}
