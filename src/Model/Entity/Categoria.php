<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Criptografia\Criptografia;
use Cake\ORM\Entity;

/**
 * Categoria Entity
 *
 * @property int $id
 * @property string $nome
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Entrada[] $entradas
 * @property int|null $posicao
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
    protected array $_accessible = [
        'nome' => true,
        'created' => true,
        'modified' => true,
        'entradas' => true,
        'posicao' => true,
    ];

    /**
     * Mutator usado para criptografar o nome
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     * @see \App\Model\Entity\Categoria::$nome
     */
    protected function _setNome(string $textoPuro): string
    {
        return Criptografia::criptografar($textoPuro);
    }

    /**
     * Retorna o nome descriptografado
     *
     * @access public
     * @return string
     */
    public function nomeDescrip(): string
    {
        return Criptografia::descriptografar($this->nome);
    }

    public function nomeEncurtado(int $tamanho = 35): string
    {
        $nomeDescrip = $this->nomeDescrip();

        if (strlen($nomeDescrip) > $tamanho) {
            $nomeDescrip = substr($nomeDescrip, 0, $tamanho) . ' ...';
        }

        return $nomeDescrip;
    }
}
