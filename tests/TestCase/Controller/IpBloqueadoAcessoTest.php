<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * RN: um IP presente em 'ips_bloqueados' deve receber a página de bloqueio em qualquer
 * controller — inclusive nos que sobrescrevem beforeFilter() e chamam
 * parent::beforeFilter() (Users, Entradas, Categorias, IpsBloqueados).
 *
 * @uses \App\Controller\AppController::beforeFilter()
 */
class IpBloqueadoAcessoTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * IP bloqueado na app.IpsBloqueados fixture.
     */
    private const IP_BLOQUEADO = '203.0.113.10';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.IpsBloqueados',
        'app.Users',
        'app.Entradas',
        'app.Categorias',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->configRequest(['environment' => ['REMOTE_ADDR' => self::IP_BLOQUEADO]]);
    }

    /**
     * Rotas autenticadas atendidas por controllers que sobrescrevem beforeFilter().
     *
     * @return array<string, array{string}>
     */
    public static function rotasAutenticadasProvider(): array
    {
        return [
            'Entradas' => ['/entradas/add'],
            'Categorias' => ['/categorias'],
            'IpsBloqueados' => ['/ips-bloqueados'],
            'Users (somente root)' => ['/users'],
        ];
    }

    /**
     * @param string $url
     * @return void
     */
    #[DataProvider('rotasAutenticadasProvider')]
    public function testIpBloqueadoRecebePaginaDeBloqueio(string $url): void
    {
        $this->loginAsUser();
        $this->get($url);

        $this->assertResponseOk();
        $this->assertResponseContains('Seu IP foi bloqueado');
    }

    /**
     * A tela de login (action sem autenticação do UsersController) também deve ser bloqueada.
     *
     * @return void
     */
    public function testIpBloqueadoNaoAcessaLogin(): void
    {
        $this->get('/users/login');

        $this->assertResponseOk();
        $this->assertResponseContains('Seu IP foi bloqueado');
    }

    /**
     * Um IP bloqueado não pode executar actions que alteram dados: além de exibir a
     * página de bloqueio, a action não deve rodar (nada é gravado no banco).
     *
     * @return void
     */
    public function testIpBloqueadoNaoExecutaActionDeEscrita(): void
    {
        $categorias = $this->getTableLocator()->get('Categorias');
        $totalAntes = $categorias->find()->count();

        $this->loginAsUser();
        $this->post('/categorias/add', ['nome' => 'Categoria De Ip Bloqueado']);

        $this->assertResponseOk();
        $this->assertResponseContains('Seu IP foi bloqueado');
        $this->assertSame($totalAntes, $categorias->find()->count());
    }

    /**
     * Um IP não bloqueado não deve ver a página de bloqueio.
     *
     * @return void
     */
    public function testIpNaoBloqueadoAcessaNormalmente(): void
    {
        $this->configRequest(['environment' => ['REMOTE_ADDR' => '198.51.100.1']]);
        $this->loginAsUser();
        $this->get('/categorias');

        $this->assertResponseOk();
        $this->assertResponseNotContains('Seu IP foi bloqueado');
    }
}
