<?php
declare(strict_types=1);

namespace App\Test\TestCase\Log\Engine;

use App\Log\Seguranca;
use App\Model\Entity\EventoSeguranca;
use App\Model\Table\EventosSegurancaTable;
use Authentication\Identity;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * Cada evento do escopo `seguranca` vira uma linha na tabela `eventos_seguranca` (engine registrado no
 * config/bootstrap.php).
 *
 * @uses \App\Log\Engine\EventosSegurancaLog
 */
class EventosSegurancaLogTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.EventosSeguranca',
    ];

    /**
     * @param \Authentication\Identity|null $identidade
     * @return void
     */
    private function requisicao(?Identity $identidade = null): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '203.0.113.7']]);
        if ($identidade !== null) {
            $request = $request->withAttribute('identity', $identidade);
        }
        Router::setRequest($request);
    }

    /**
     * @return array<\App\Model\Entity\EventoSeguranca>
     */
    private function eventos(): array
    {
        $eventos = [];
        foreach ($this->fetchTable(EventosSegurancaTable::class)->find()->orderByAsc('id') as $evento) {
            $this->assertInstanceOf(EventoSeguranca::class, $evento);
            $eventos[] = $evento;
        }

        return $eventos;
    }

    /**
     * @return void
     */
    public function testEngineRegistradoNoBootstrap(): void
    {
        $this->assertContains('eventos_seguranca', Log::configured());
    }

    /**
     * No login (sem usuário logado), a conta afetada é a dona do e-mail digitado; o e-mail não é gravado.
     *
     * @return void
     */
    public function testLoginErradoGravaAContaDoEmailDigitado(): void
    {
        $this->requisicao();

        Seguranca::registrar('notice', Seguranca::LOGIN_CREDENCIAL_INVALIDA, 'Senha errada', [
            'email' => 'USUARIO.FIXTURE@example.com',
        ]);

        $eventos = $this->eventos();
        $this->assertCount(1, $eventos);
        $this->assertInstanceOf(EventoSeguranca::class, $eventos[0]);
        $this->assertSame(Seguranca::LOGIN_CREDENCIAL_INVALIDA, $eventos[0]->evento);
        $this->assertSame('notice', $eventos[0]->nivel);
        $this->assertSame(1, $eventos[0]->usuario_id);
        $this->assertSame('203.0.113.7', $eventos[0]->ip);
        $this->assertNotNull($eventos[0]->created);
    }

    /**
     * E-mail que não existe: o evento é gravado sem conta.
     *
     * @return void
     */
    public function testLoginComEmailInexistenteGravaSemConta(): void
    {
        $this->requisicao();

        Seguranca::registrar('notice', Seguranca::LOGIN_CREDENCIAL_INVALIDA, 'Senha errada', [
            'email' => 'ninguem@example.com',
        ]);

        $eventos = $this->eventos();
        $this->assertCount(1, $eventos);
        $this->assertNull($eventos[0]->usuario_id);
    }

    /**
     * Com usuário logado, a conta afetada é a dele.
     *
     * @return void
     */
    public function testComUsuarioLogadoGravaAContaDele(): void
    {
        $this->requisicao(new Identity(['id' => 1]));

        Seguranca::registrar('warning', Seguranca::ACESSO_SOMENTE_ROOT, 'Acesso negado');

        $eventos = $this->eventos();
        $this->assertCount(1, $eventos);
        $this->assertSame(1, $eventos[0]->usuario_id);
        $this->assertSame('warning', $eventos[0]->nivel);
    }

    /**
     * Log do escopo sem nome de evento (não veio do Seguranca::registrar()) não vira linha.
     *
     * @return void
     */
    public function testSemEventoNaoGrava(): void
    {
        $this->requisicao();

        Log::write('notice', 'Outra coisa', ['scope' => Seguranca::ESCOPO]);

        $this->assertSame([], $this->eventos());
    }

    /**
     * Logs sem o escopo `seguranca` não chegam ao engine.
     *
     * @return void
     */
    public function testLogForaDoEscopoNaoGrava(): void
    {
        $this->requisicao();

        Log::write('warning', 'Aviso qualquer', ['evento' => 'qualquer']);

        $this->assertSame([], $this->eventos());
    }
}
