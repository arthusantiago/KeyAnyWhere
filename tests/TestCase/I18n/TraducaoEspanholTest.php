<?php
declare(strict_types=1);

namespace App\Test\TestCase\I18n;

use App\Model\Custom\ValidatorKaw;
use Cake\I18n\I18n;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use function Cake\I18n\__;

/**
 * O espanhol do core (resources/locales/es/default.po).
 */
class TraducaoEspanholTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    private string $localeOriginal;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->localeOriginal = I18n::getLocale();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        I18n::setLocale($this->localeOriginal);
        parent::tearDown();
    }

    /**
     * Frases soltas e as mensagens de validação (com o nome do campo também traduzido).
     *
     * @return void
     */
    public function testFrasesDoCoreSaoTraduzidas(): void
    {
        I18n::setLocale('es');

        $this->assertSame('Guardar', __('Salvar'));
        $this->assertSame('Configuración general', __('Configurações gerais'));
        $this->assertStringNotContainsString(
            'precisa ser informado',
            ValidatorKaw::mensagemErroPadrao('requirePresence', ['Senha']),
        );
    }

    /**
     * Em pt_BR o texto é o próprio código.
     *
     * @return void
     */
    public function testEmPortuguesOTextoEhOProprioCodigo(): void
    {
        I18n::setLocale('pt_BR');

        $this->assertSame('Salvar', __('Salvar'));
    }

    /**
     * A tela de login sai em espanhol quando o idioma da instalação é o espanhol.
     *
     * @return void
     */
    public function testTelaDeLoginEmEspanhol(): void
    {
        I18n::setLocale('es');
        $this->get('/users/login');

        $this->assertResponseOk();
        $this->assertResponseContains(__('Segundo fator de autenticação'));
        $this->assertResponseNotContains('Segundo fator de autenticação');
    }
}
