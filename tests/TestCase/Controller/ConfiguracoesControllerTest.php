<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Log\Seguranca;
use App\Model\Entity\Configuracao;
use App\Model\Table\ConfiguracoesTable;
use App\Test\TestCase\Log\CapturaLogSegurancaTrait;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Tela "Configurações gerais" (fuso horário e idioma), somente root.
 *
 * @uses \App\Controller\ConfiguracoesController
 */
class ConfiguracoesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use CsrfTestTrait;
    use AuthenticatedTestTrait;
    use CapturaLogSegurancaTrait;
    use ConfiguracaoKawIsoladaTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Configuracoes',
        'app.Users',
        'app.Sessions',
        'app.Auditorias',
    ];

    /**
     * @return \App\Model\Table\ConfiguracoesTable
     */
    private function configuracoes(): ConfiguracoesTable
    {
        return $this->fetchTable(ConfiguracoesTable::class);
    }

    /**
     * @return void
     */
    public function testGeralExigeAutenticacao(): void
    {
        $this->get('/configuracoes/geral');

        $this->assertTrue(in_array($this->_response?->getStatusCode(), [302, 401], true));
    }

    /**
     * Usuário comum é redirecionado para a home e o acesso vira evento de segurança.
     *
     * @return void
     */
    public function testGeralBloqueadoParaUsuarioNaoRoot(): void
    {
        $this->loginAsNonRootUser();
        $this->get('/configuracoes/geral');

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame('warning', $this->logSeguranca(Seguranca::ACESSO_SOMENTE_ROOT)['nivel']);
    }

    /**
     * Usuário comum também não grava nada por POST.
     *
     * @return void
     */
    public function testGeralPostBloqueadoParaUsuarioNaoRoot(): void
    {
        $this->loginAsNonRootUser();
        $this->enableSecurityToken();
        $this->post('/configuracoes/geral', ['fusoHorario' => 'America/Sao_Paulo', 'idioma' => 'es']);

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame([], $this->configuracoes()->preferenciasRegionais());
    }

    /**
     * O root vê o formulário com os idiomas e os fusos horários.
     *
     * @return void
     */
    public function testGeralComoRootExibeFormulario(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->get('/configuracoes/geral');

        $this->assertResponseOk();
        $this->assertResponseContains('Configurações gerais');
        $this->assertResponseContains('value="es"');
        $this->assertResponseContains('value="America/Sao_Paulo"');
    }

    /**
     * POST válido grava o fuso e o idioma e redireciona para a própria tela.
     *
     * @return void
     */
    public function testGeralPostGravaFusoEIdioma(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->enableSecurityToken();
        $this->post('/configuracoes/geral', ['fusoHorario' => 'America/Sao_Paulo', 'idioma' => 'es']);

        $this->assertRedirect(['controller' => 'Configuracoes', 'action' => 'geral']);
        $this->assertEquals(
            ['fusoHorario' => 'America/Sao_Paulo', 'idioma' => 'es'],
            $this->configuracoes()->preferenciasRegionais(),
        );
    }

    /**
     * POST com fuso inválido não grava nada e mostra o erro.
     *
     * @return void
     */
    public function testGeralPostComFusoInvalidoNaoGrava(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->enableSecurityToken();
        $this->post('/configuracoes/geral', ['fusoHorario' => 'Marte/Olympus_Mons', 'idioma' => 'es']);

        $this->assertResponseOk();
        $this->assertResponseContains('Ocorreu um erro');
        $this->assertSame('', $this->configuracoes()->getValorConfig(Configuracao::REGIONAL_IDIOMA));
        $this->assertSame('', $this->configuracoes()->getValorConfig(Configuracao::REGIONAL_FUSO_HORARIO));
    }

    /**
     * O item do menu aparece para o root.
     *
     * @return void
     */
    public function testMenuDoRootTemConfiguracoesGerais(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->get('/configuracoes/geral');

        $this->assertResponseContains('/configuracoes/geral');
    }

    /**
     * Com `Kaw.Configuracoes.regionalEditavel = false` (o fuso e o idioma vêm de outro lugar), a tela
     * some do menu, o GET volta para a home e o POST não grava.
     *
     * @return void
     */
    public function testTelaDesligadaPorConfiguracao(): void
    {
        Configure::write('Kaw.Configuracoes.regionalEditavel', false);
        try {
            $this->loginAsUser(['root' => true]);
            $this->get('/configuracoes/geral');
            $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
            $this->assertFlashElement('flash/warning');

            $this->enableSecurityToken();
            $this->post('/configuracoes/geral', ['fusoHorario' => 'America/Sao_Paulo', 'idioma' => 'es']);
            $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
            $this->assertSame([], $this->configuracoes()->preferenciasRegionais());

            $this->get('/users/minha-conta');
            $this->assertResponseNotContains('/configuracoes/geral');
        } finally {
            Configure::write('Kaw.Configuracoes.regionalEditavel', true);
        }
    }
}
