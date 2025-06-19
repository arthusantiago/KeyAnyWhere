<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Criptografia\Criptografia;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;
use PragmaRX\Google2FA\Google2FA;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string|null $tfa_secret
 * @property bool $tfa_ativo
 * @property bool $root Se o usuário e administrador
 * @property int $LENGTH_SECRET_2FA
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Entrada[] $entradas
 * @property \App\Model\Entity\Session[] $sessions
 */
class User extends Entity
{
    public const LENGTH_SECRET_2FA = 32;

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
        'username' => true,
        'email' => true,
        'password' => true,
        'created' => true,
        'modified' => true,
        'entradas' => true,
        'tfa_secret' => false,
        'tfa_ativo' => false,
        'root' => false,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected array $_hidden = [
        'password',
        'tfa_secret',
        'tfa_ativo',
        'root',
    ];

    public function usernameEncurtado(int $tamanho = 15): string
    {
        $complemento = strlen($this->username) > $tamanho ? '(...)' : '';

        return substr($this->username, 0, $tamanho) . $complemento;
    }

    /**
     * @param string $password
     * @see https://book.cakephp.org/4/en/orm/entities.html#accessors-mutators
     * @see \App\Model\Entity\User::$password
     */
    protected function _setPassword(string $password): string
    {
        return (new DefaultPasswordHasher())->hash($password);
    }

    protected function _setTfaSecret(string $secret): string
    {
        return Criptografia::criptografar($secret);
    }

    public function descripSecret2FA(): string
    {
        return Criptografia::descriptografar($this->tfa_secret);
    }

    public function geraSecret2FA()
    {
        return (new Google2FA())->generateSecretKey(User::LENGTH_SECRET_2FA);
    }

    public function valida2fa(string $secret): bool
    {
        return (new Google2FA())->verifyKey($this->descripSecret2FA(), $secret);
    }

    protected function _setEmail(string $email): string
    {
        return strtolower($email);
    }
}
