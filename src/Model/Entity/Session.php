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

    protected function _setData($dadosSessao)
    {
        if ($dadosSessao) {
            $dadosUsuario = $this->obterDadosUsuarioDaSessao($dadosSessao);
            if ($dadosUsuario) {
                $dadosUsuario = unserialize($dadosUsuario);
                $this->user_id = $dadosUsuario['id'];
            }
        }
        return $dadosSessao;
    }

    /**
     * A partir de uma string que contem a representação de vários objetos,
     * o método recupera somente as informações do usuário vinculado a sessão.
     *
     * @access private
     * @param string $dadosSessao
     * @return string Propriedades do usuário vinculado a sessão
     */
    private function obterDadosUsuarioDaSessao(string $dadosSessao): string
    {
        $entityUser = 'App\Model\Entity\User';
        $posicaoEntityUser = stripos($dadosSessao, $entityUser);

        if ($posicaoEntityUser) {
            $posicaoPropsUser = strlen($entityUser) + 24;
            $iniciarNaPosicao = $posicaoEntityUser + $posicaoPropsUser;
            return substr($dadosSessao, $iniciarNaPosicao);
        }
        return '';
    }
}
