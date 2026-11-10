<?php
declare(strict_types=1);

namespace App\Test\TestCase\I18n;

use App\I18n\Regional;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @uses \App\I18n\Regional
 */
class RegionalTest extends TestCase
{
    private string $fusoOriginal;

    private string $localeOriginal;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fusoOriginal = date_default_timezone_get();
        $this->localeOriginal = I18n::getLocale();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        date_default_timezone_set($this->fusoOriginal);
        I18n::setLocale($this->localeOriginal);
        ini_set('intl.default_locale', $this->localeOriginal);
        parent::tearDown();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function acceptLanguageProvider(): array
    {
        return [
            'sem cabeçalho' => ['', 'pt_BR'],
            'espanhol' => ['es-AR,es;q=0.9', 'es'],
            'português de Portugal cai no pt_BR' => ['pt-PT,pt;q=0.9', 'pt_BR'],
            'idioma sem tradução' => ['fr-FR,fr;q=0.9', 'pt_BR'],
            'valor inválido' => ['%%%', 'pt_BR'],
        ];
    }

    /**
     * @param string $acceptLanguage
     * @param string $esperado
     * @return void
     */
    #[DataProvider('acceptLanguageProvider')]
    public function testIdiomaDoNavegador(string $acceptLanguage, string $esperado): void
    {
        $request = new ServerRequest(['environment' => ['HTTP_ACCEPT_LANGUAGE' => $acceptLanguage]]);

        $this->assertSame($esperado, Regional::idiomaDoNavegador($request));
    }

    /**
     * Fuso e idioma válidos são aplicados ao PHP, ao Configure e ao I18n.
     *
     * @return void
     */
    public function testAplicarComPreferenciasValidas(): void
    {
        Configure::write('App.defaultTimezone', 'UTC');

        Regional::aplicar(['fusoHorario' => 'America/Sao_Paulo', 'idioma' => 'es']);

        $this->assertSame('America/Sao_Paulo', date_default_timezone_get());
        $this->assertSame('America/Sao_Paulo', Configure::read('App.defaultTimezone'));
        $this->assertSame('es', Configure::read('App.defaultLocale'));
        $this->assertSame('es', I18n::getLocale());
    }

    /**
     * Valores fora da lista (ou ausentes) são ignorados: vale o padrão do config/app.php.
     *
     * @return void
     */
    public function testAplicarIgnoraValoresInvalidos(): void
    {
        date_default_timezone_set('UTC');
        $localeAntes = I18n::getLocale();

        Regional::aplicar(['fusoHorario' => 'Marte/Olympus_Mons', 'idioma' => 'fr']);
        Regional::aplicar([]);

        $this->assertSame('UTC', date_default_timezone_get());
        $this->assertSame($localeAntes, I18n::getLocale());
    }

    /**
     * @return void
     */
    public function testFusosHorariosSaoOsDaIana(): void
    {
        $fusos = Regional::fusosHorarios();

        $this->assertContains('America/Sao_Paulo', $fusos);
        $this->assertContains('UTC', $fusos);
        $this->assertNotContains('Marte/Olympus_Mons', $fusos);
    }
}
