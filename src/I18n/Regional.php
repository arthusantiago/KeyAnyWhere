<?php
declare(strict_types=1);

namespace App\I18n;

use Cake\Core\Configure;
use Cake\Database\TypeFactory;
use Cake\I18n\I18n;
use DateTimeZone;
use Locale;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Idioma e fuso horário da instalação (tela "Configurações gerais", tabela `configuracoes`).
 *
 * Os valores escolhidos pelo root são aplicados no config/bootstrap.php, a cada requisição. Sem
 * nada gravado, valem o `App.defaultLocale` e o `App.defaultTimezone` do config/app.php.
 */
class Regional
{
    /**
     * Idiomas com tradução (resources/locales/<idioma>/default.po; o pt_BR é o texto do código).
     *
     * @var array<string, string>
     */
    public const IDIOMAS = [
        'pt_BR' => 'Português (Brasil)',
        'es' => 'Español (América Latina)',
    ];

    public const IDIOMA_PADRAO = 'pt_BR';

    /**
     * Fusos horários aceitos (identificadores da IANA, como `America/Sao_Paulo`).
     *
     * @return array<int, string>
     */
    public static function fusosHorarios(): array
    {
        return DateTimeZone::listIdentifiers();
    }

    /**
     * Idioma suportado que melhor atende ao `Accept-Language` do navegador. Usado onde ainda não há
     * configuração gravada (a configuração inicial, antes do primeiro usuário).
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     */
    public static function idiomaDoNavegador(ServerRequestInterface $request): string
    {
        $acceptLanguage = $request->getHeaderLine('Accept-Language');
        if ($acceptLanguage === '') {
            return self::IDIOMA_PADRAO;
        }

        $preferido = Locale::acceptFromHttp($acceptLanguage) ?: self::IDIOMA_PADRAO;
        $idioma = Locale::lookup(array_keys(self::IDIOMAS), $preferido, true, self::IDIOMA_PADRAO);

        return $idioma !== '' && isset(self::IDIOMAS[$idioma]) ? $idioma : self::IDIOMA_PADRAO;
    }

    /**
     * Aplica o fuso horário e o idioma gravados. Valor vazio ou fora da lista é ignorado (vale o
     * padrão do config/app.php).
     *
     * @param array{fusoHorario?: string, idioma?: string} $preferencias
     * @return void
     */
    public static function aplicar(array $preferencias): void
    {
        $fuso = $preferencias['fusoHorario'] ?? '';
        if ($fuso !== '' && in_array($fuso, self::fusosHorarios(), true)) {
            date_default_timezone_set($fuso);
            Configure::write('App.defaultTimezone', $fuso);

            /*
             * A consulta que leu as preferências já construiu (e guardou para o resto da requisição)
             * os tipos de data/hora do ORM, cada um com o `date_default_timezone_get()` daquele
             * momento como fuso de exibição. Sem descartá-los, toda coluna datetime lida depois
             * (ex.: auditorias.created) seria exibida no fuso anterior.
             */
            $mapaTipos = TypeFactory::getMap();
            if (is_array($mapaTipos)) {
                TypeFactory::setMap($mapaTipos);
            }
        }

        $idioma = $preferencias['idioma'] ?? '';
        if (isset(self::IDIOMAS[$idioma])) {
            Configure::write('App.defaultLocale', $idioma);
            ini_set('intl.default_locale', $idioma);
            I18n::setLocale($idioma);
        }
    }
}
