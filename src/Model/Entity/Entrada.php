<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Throwable;

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
 * @property int $user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
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
    protected $_accessible = [
        'titulo' => true,
        'password' => true,
        'username' => true,
        'link' => true,
        'anotacoes' => true,
        'categoria_id' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'categoria' => true
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
    ];

    public function linkEncurtado(int $tamanho = 70): string
    {
        return substr($this->descriptografar($this->link), 0, $tamanho);
    }

    /**
     * Mutator usado para criptografar a senha
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     */
    protected function _setPassword(string $textoPuro): string
    {
        return $this->criptografar($textoPuro);
    }

    /**
     * Retorna a senha descriptografada
     *
     * @access	public
     * @return	string
     */
    public function passwordDescrip(): string
    {
        return $this->descriptografar($this->password);
    }

    /**
     * Mutator usado para criptografar o usuario
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     */
    protected function _setUsername(string $textoPuro): string
    {
        return $this->criptografar($textoPuro);
    }

    /**
     * Retorna o usuario descriptografado
     *
     * @access	public
     * @return	string
     */
    public function usernameDescrip(): string
    {
        return $this->descriptografar($this->username);
    }

    /**
     * Mutator usado para criptografar o titulo
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     */
    protected function _setTitulo(string $textoPuro): string
    {
        return $this->criptografar($textoPuro);
    }

    /**
     * Retorna o titulo descriptografado
     *
     * @access	public
     * @return	string
     */
    public function tituloDescrip(): string
    {
        return $this->descriptografar($this->titulo);
    }

    /**
     * Mutator usado para criptografar o link
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     */
    protected function _setLink(string $textoPuro): string
    {
        return $this->criptografar($textoPuro);
    }

    /**
     * Retorna o link descriptografado
     *
     * @access	public
     * @return	string
     */
    public function linkDescrip(): string
    {
        return $this->descriptografar($this->link);
    }

    /**
     * Mutator usado para criptografar as anotações
     *
     * @access protected
     * @param string $textoPuro
     * @return string
     */
    protected function _setAnotacoes(string $textoPuro): string
    {
        return $this->criptografar($textoPuro);
    }

    /**
     * Retorna as anotações descriptografadas
     *
     * @access	public
     * @return	string
     */
    public function anotacoesDescrip(): string
    {
        return $this->descriptografar($this->anotacoes);
    }

    public function criptografar(string $textoPuro): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = sodium_crypto_secretbox(
            $textoPuro,
            $nonce,
            sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
        );

        sodium_memzero($textoPuro);

        return sodium_bin2hex($nonce . $encrypted);
    }

    public function descriptografar(string $dado): string
    {
        try {
            $decoded = sodium_hex2bin($dado);
            sodium_memzero($dado);

            $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null);
            sodium_memzero($decoded);
        } catch (\Throwable $ex) {
            return 'Erro ao obter as informações para descriptografar: ' . $ex->getMessage();
        }

        try {
            $return = sodium_crypto_secretbox_open(
                $ciphertext,
                $nonce,
                sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
            );

            sodium_memzero($ciphertext);
            sodium_memzero($nonce);

            if ($return === false) {
                $return = 'Não foi possivel descriptografar.';
            }
        } catch (Throwable $ex) {
            $return = 'Erro ao descriptografar: ' . $ex->getMessage();
        }

        return $return;
    }
}
