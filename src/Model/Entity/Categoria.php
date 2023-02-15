<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Categoria Entity
 *
 * @property int $id
 * @property string $nome
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Entrada[] $entradas
 */
class Categoria extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'nome' => true,
        'created' => true,
        'modified' => true,
        'entradas' => true
    ];


    public function nomeEncurtado(int $tamanho): string
    {
        if (strlen($this->nome) > $tamanho)
        {
            return substr($this->nome, 0, $tamanho) . ' ...';
        }else{
            return $this->nome;
        }
    }
}
