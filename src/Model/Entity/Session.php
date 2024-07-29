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
 * @property string|null user_agent
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
        'user_agent' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    private $navegadores = [
        'firefox',
        'chrome',
        'edge',
        'opera',
        'safari',
        'samsung',
        'webview',
    ];

    private $sistemasOperacionais = [
        'windows',
        'linux',
        'mac',
        'iphone',
        'android',
        'mobile'
    ];

    protected function _getNavegador()
    {
        $navegadorSessao = $this->buscaNaString($this->navegadores, $this->user_agent) ?? 'Desconhecido';
        return ucfirst($navegadorSessao);
    }

    protected function _getSistemaOperacional()
    {
        $sistOperaSessao = $this->buscaNaString($this->sistemasOperacionais, $this->user_agent) ?? 'Desconhecido';
        return ucfirst($sistOperaSessao);
    }

    /**
     * Verifica se na string existe um dos itens procurados.
     *
     * @access protected
     * @param array $itensProcurados Conjunto de itens que serão procurados na string
     * @param string $string Que ira sofrer a busca.
     * @return string|boolean Retorna o primeiro registro encontrado. Se nada for encontrado retorna false
     */
    protected function buscaNaString(array $itensProcurados, string $string): string|bool
    {
        foreach ($itensProcurados as $item) {
            if (stripos($string, $item)) {
                return $item;
            }
        }
        return false;
    }
}
