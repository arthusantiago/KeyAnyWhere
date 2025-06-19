<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Session Entity
 *
 * @property string $id É o ID da sessão, o mesmo utilizado pelo PHP
 * @property \App\Model\Entity\uuid $id_secundario Usado para manipular o registro em operações no banco de dados
 * @property string|resource|null $data
 * @property int|null $expires
 * @property int|null $user_id
 * @property string|null user_agent
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User|null $user
 * @property string|null $user_agent
 * @property string $navegador
 * @property string $sistema_operacional
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
    protected array $_accessible = [
        'id_secundario' => false,
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
        'linux',
        'android',
        'iphone',
        'mac',
        'windows',
        'mobile',
    ];

    /**
     * Sinaliza que o objeto estanciado é a representação da sessão corrente onde ele
     * está sendo manipulado.
     *
     * @var bool $esteDispositivo
     */
    public bool $esteDispositivo = false;

    /**
     * Propriedade virtual 'navegador'. Para acessar: $session->navegador
     * Documentação do CakePHP sobre accessors-mutators:
     * https://book.cakephp.org/4/en/orm/entities.html#accessors-mutators
     *
     * @access protected
     * @return string
     * @see \App\Model\Entity\Session::$navegador
     */
    protected function _getNavegador(): string
    {
        $navegadorSessao = $this->buscaNaString($this->navegadores, $this->user_agent) ?? 'Desconhecido';

        return ucfirst($navegadorSessao);
    }

    /**
     * Propriedade virtual 'sistema_operacional'. Para acessar: $session->sistema_operacional
     * Documentação do CakePHP sobre accessors-mutators:
     * https://book.cakephp.org/4/en/orm/entities.html#accessors-mutators
     *
     * @access protected
     * @return string
     * @see \App\Model\Entity\Session::$sistema_operacional
     */
    protected function _getSistemaOperacional(): string
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
     * @return string|bool Retorna o primeiro registro encontrado. Se nada for encontrado retorna false
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

    /**
     * Verifica se a sessão excedeu o tempo de inatividade
     *
     * @access public
     * @param int $maxTimeInactiv Tempo máximo de inatividade
     * @return bool Retorna TRUE quando a sessão é considerada ativa, FALSE caso contrário.
     */
    public function estaAtiva(int $maxTimeInactiv): bool
    {
        // Time Without user Interaction = (Current time - Time of Last Interaction)
        $twi = time() - $this->expires;

        return $twi >= $maxTimeInactiv ? false : true;
    }
}
