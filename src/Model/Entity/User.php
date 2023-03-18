<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;
use App\Model\Entity\Entrada;
use PragmaRX\Google2FA\Google2FA;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $google2fa_secret
 * @property bool $google2fa_ativo
 * @property int $LENGTH_SECRET_2FA
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Entrada[] $entradas
 */
class User extends Entity
{
    const LENGTH_SECRET_2FA = 32;

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
        'username' => true,
        'email' => true,
        'password' => true,
        'created' => true,
        'modified' => true,
        'entradas' => true,
        'google2fa_ativo' => true
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
        'google2fa_secret',
        'google2fa_ativo'
    ];

    /**
     * Método que gera o hash da senha informada
     * Esse método é invocado automaticamente ao setar valores na propriedade 'password'
     *
     * @param string $password Senha informada no frontend
     * @see https://book.cakephp.org/4/en/orm/entities.html#accessors-mutators
     */
    protected function _setPassword(string $password = ''): string
    {
        if (strlen($password) > 0) {
            $password = (new DefaultPasswordHasher())->hash($password);
        } else {
            $password = $this->password;
        }

        return $password;
    }

    protected function _setGoogle2faSecret(string $secret): string
    {
        return (new Entrada)->criptografar($secret);
    }

    public function descripSecret2FA(): string
    {
        return (new Entrada)->descriptografar($this->google2fa_secret);
    }

    public function geraSecret2FA()
    {
        return (new Google2FA)->generateSecretKey(User::LENGTH_SECRET_2FA);
    }

    public function valida2fa(string $secret): bool
    {
        return (new Google2FA())->verifyKey($this->descripSecret2FA(), $secret);
    }
}
