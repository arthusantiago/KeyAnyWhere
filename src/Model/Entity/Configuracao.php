<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Configuracao Entity
 *
 * ## O que salvar na tabela de configuração?
 * Informações utilizadas para alterar o comportamento do sistema, como o fuso horário e o idioma.
 *
 * ## Onde cada coisa deve ficar?
 * Na entity Configuracao deve ficar a **definição** da configuração (a chave, numa constante).
 * Exemplo: `public const REGIONAL_IDIOMA = 'regional.idioma';`
 *
 * Os valores que a configuração pode assumir ficam com quem a usa (ex.: `App\I18n\Regional::IDIOMAS`).
 *
 * ## Como definir a chave de uma configuração?
 * Estrutura de nome que uma configuração deve seguir:
 * - Usar camelCase.
 * - As partes são separadas por ponto (.)
 * - Deve haver uma hierarquia entre as partes.
 * - - A parte mais à esquerda é o "módulo" ou "contexto" da configuração.
 * - - A parte mais à direita é o nome "real" da configuração.
 * - Cada parte deve ser descritiva e indicar claramente o que a configuração representa.
 * - Não usar abreviações ou siglas que não sejam amplamente reconhecidas.
 *
 * Exemplos:
 * - regional.fusoHorario
 * - regional.idioma
 *
 * @property int $id
 * @property string $chave
 * @property string $valor
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Configuracao extends Entity
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
        'chave' => true,
        'valor' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Fuso horário da instalação (identificador da IANA, ex.: `America/Sao_Paulo`).
     *
     * @var string REGIONAL_FUSO_HORARIO
     */
    public const REGIONAL_FUSO_HORARIO = 'regional.fusoHorario';

    /**
     * Idioma da interface (uma das chaves de `App\I18n\Regional::IDIOMAS`).
     *
     * @var string REGIONAL_IDIOMA
     */
    public const REGIONAL_IDIOMA = 'regional.idioma';
}
