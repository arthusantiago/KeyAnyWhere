<?php
declare(strict_types=1);

namespace App\Test\TestCase\Log;

use App\Log\Seguranca;
use Cake\Log\Engine\ArrayLog;
use Cake\Log\Log;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Log\Seguranca
 */
class SegurancaTest extends TestCase
{
    use CapturaLogSegurancaTrait;

    /**
     * Formato da linha: nome do evento entre colchetes na mensagem e também no contexto, no escopo
     * `seguranca`, com o nível pedido e os dados variáveis no contexto.
     *
     * @return void
     */
    public function testRegistrarGravaNoEscopoSegurancaComEventoNaMensagemENoContexto(): void
    {
        Seguranca::registrar('warning', Seguranca::ACESSO_SOMENTE_ROOT, 'Descrição fixa', ['campo' => 'nome']);

        $linha = $this->logSeguranca(Seguranca::ACESSO_SOMENTE_ROOT);
        $this->assertSame('warning', $linha['nivel']);
        $this->assertSame('[acesso_somente_root] Descrição fixa', $linha['mensagem']);
        $this->assertSame('seguranca', $linha['contexto']['scope']);
        $this->assertSame('nome', $linha['contexto']['campo']);
        $this->assertSame('keyanywhere', $linha['app']);
        $this->assertArrayHasKey('requisicao', $linha);
    }

    /**
     * Um motor por nível com `scopes => null` (como os do config/app.php) não recebe o log de
     * segurança: ele vai só para o motor `seguranca`, sem duplicar.
     *
     * @return void
     */
    public function testMotorSemEscopoNaoRecebeOLogDeSeguranca(): void
    {
        Log::setConfig('sem_escopo', ['className' => ArrayLog::class, 'scopes' => null, 'levels' => []]);

        try {
            Seguranca::registrar('notice', Seguranca::LOGIN_2FA_INVALIDO, 'Descrição fixa');
            $engine = Log::engine('sem_escopo');
            $this->assertInstanceOf(ArrayLog::class, $engine);
            $this->assertSame([], $engine->read());
        } finally {
            Log::drop('sem_escopo');
        }

        $this->logSeguranca(Seguranca::LOGIN_2FA_INVALIDO);
    }
}
