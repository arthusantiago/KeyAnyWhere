<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Throwable;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

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

    public function linkEncurtado(int $tamanho): ?string
    {
        if (empty($this->link))
        {
            return null;
        }

        if (strlen($this->link) > $tamanho)
        {
            return substr($this->link, 0, $tamanho) . '...';
        }else{
            return $this->link;
        }
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
        return sodium_bin2hex(
            sodium_crypto_secretbox(
                $textoPuro,
                sodium_hex2bin(env('IV')),
                sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
            )
        );
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
        return sodium_bin2hex(
            sodium_crypto_secretbox(
                $textoPuro,
                sodium_hex2bin(env('IV')),
                sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
            )
        );
    }

    /**
     * Retorna a senha descriptografada
     *
     * @access	public
     * @return	string
     */
    public function passwordDescrip(): string
    {
        try{
            $return = sodium_crypto_secretbox_open(
                sodium_hex2bin($this->password),
                sodium_hex2bin(env('IV')),
                sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
            );

            if($return === false){
                $return = 'Não foi possivel descriptografar a senha.';
            }
        }catch(Throwable $ex){
            $return = 'Erro ao descriptografar a senha';
        }

        return $return;
    }

    /**
     * Retorna o usuario descriptografado
     *
     * @access	public
     * @return	string
     */
    public function usernameDescrip(): string
    {
        try{
            $return = sodium_crypto_secretbox_open(
                sodium_hex2bin($this->username),
                sodium_hex2bin(env('IV')),
                sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
            );

            if($return === false){
                $return = 'Não foi possivel descriptografar a senha.';
            }
        }catch(Throwable $ex){
            $return = 'Erro ao descriptografar o usuário';
        }
        return $return;
    }
}
