<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Criptografia\Criptografia;
use Cake\ORM\Entity;

/**
 * Entrada Entity
 *
 * @property int $id
 * @property string $titulo
 * @property string $username
 * @property string $password
 * @property string|null $link
 * @property string $anotacoes
 * @property int $categoria_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Categoria $categoria
 */
class Entrada extends Entity
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
        'titulo' => true,
        'password' => true,
        'username' => true,
        'link' => true,
        'anotacoes' => true,
        'categoria_id' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'categoria' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected array $_hidden = [
        'password',
    ];

    public function linkEncurtado(int $tamanho = 70): string
    {
        return substr(Criptografia::descriptografar($this->link), 0, $tamanho);
    }

    /**
     * Mutator usado para criptografar a senha
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     * @see \App\Model\Entity\Entrada::$password
     */
    protected function _setPassword(string $textoPuro): string
    {
        return Criptografia::criptografar($textoPuro);
    }

    /**
     * Retorna a senha descriptografada
     *
     * @access public
     * @return string
     */
    public function passwordDescrip(): string
    {
        return Criptografia::descriptografar($this->password);
    }

    /**
     * Mutator usado para criptografar o usuario
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     * @see \App\Model\Entity\Entrada::$username
     */
    protected function _setUsername(string $textoPuro): string
    {
        return Criptografia::criptografar($textoPuro);
    }

    /**
     * Retorna o usuario descriptografado
     *
     * @access public
     * @return string
     */
    public function usernameDescrip(): string
    {
        return Criptografia::descriptografar($this->username);
    }

    /**
     * Mutator usado para criptografar o titulo
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     * @see \App\Model\Entity\Entrada::$titulo
     */
    protected function _setTitulo(string $textoPuro): string
    {
        return Criptografia::criptografar($textoPuro);
    }

    /**
     * Retorna o titulo descriptografado
     *
     * @access public
     * @return string
     */
    public function tituloDescrip(): string
    {
        return Criptografia::descriptografar($this->titulo);
    }

    /**
     * Mutator usado para criptografar o link
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     * @see \App\Model\Entity\Entrada::$link
     */
    protected function _setLink(string $textoPuro): string
    {
        return Criptografia::criptografar($textoPuro);
    }

    /**
     * Retorna o link descriptografado
     *
     * @access public
     * @return string
     */
    public function linkDescrip(): string
    {
        return Criptografia::descriptografar($this->link);
    }

    /**
     * Mutator usado para criptografar as anotações
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     * @see \App\Model\Entity\Entrada::$anotacoes
     */
    protected function _setAnotacoes(string $textoPuro): string
    {
        return Criptografia::criptografar($textoPuro);
    }

    /**
     * Retorna as anotações descriptografadas
     *
     * @access public
     * @return string
     */
    public function anotacoesDescrip(): string
    {
        return Criptografia::descriptografar($this->anotacoes);
    }
}
