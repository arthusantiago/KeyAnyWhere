<?php
declare(strict_types=1);

namespace App\Test\TestCase\Log;

use App\Log\ContextoRequisicao;
use App\Log\JsonFormatter;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use PHPUnit\Framework\TestCase;

class JsonFormatterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Router::reload();
        ContextoRequisicao::reiniciar();
    }

    protected function tearDown(): void
    {
        Router::reload();
        ContextoRequisicao::reiniciar();
        parent::tearDown();
    }

    public function testSemRequisicaoHttpRegistraSoOId(): void
    {
        $linha = $this->formatar();

        $this->assertSame(['id'], array_keys($linha['requisicao']));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $linha['requisicao']['id']);
    }

    public function testMesmoIdEmTodasAsLinhasDaRequisicao(): void
    {
        $this->assertSame($this->formatar()['requisicao']['id'], $this->formatar()['requisicao']['id']);
    }

    public function testUsaUniqueIdDoApacheQuandoDisponivel(): void
    {
        $_SERVER['UNIQUE_ID'] = 'ZQx1-apache-unique-id';

        try {
            $this->assertSame('ZQx1-apache-unique-id', $this->formatar()['requisicao']['id']);
        } finally {
            unset($_SERVER['UNIQUE_ID']);
        }
    }

    public function testRequisicaoRoteadaRegistraRotaEUsuarioSemAUrl(): void
    {
        Router::setRequest($this->request('/users/redefinir-senha/token-secreto-123')
            ->withParam('controller', 'Users')
            ->withParam('action', 'redefinirSenha')
            ->withAttribute('identity', $this->identidade(7)));

        $linha = $this->formatar();

        $this->assertSame('POST', $linha['requisicao']['metodo']);
        $this->assertSame('Users::redefinirSenha', $linha['requisicao']['rota']);
        $this->assertSame('10.0.0.5', $linha['requisicao']['ip']);
        $this->assertSame(7, $linha['requisicao']['usuario_id']);
        $this->assertArrayNotHasKey('url', $linha['requisicao']);
        $this->assertStringNotContainsString('token-secreto-123', json_encode($linha, JSON_THROW_ON_ERROR));
    }

    public function testRotaComPrefixo(): void
    {
        Router::setRequest($this->request('/api/v1/clientes')
            ->withParam('prefix', 'Api/V1')
            ->withParam('controller', 'Clientes')
            ->withParam('action', 'index'));

        $this->assertSame('Api/V1/Clientes::index', $this->formatar()['requisicao']['rota']);
    }

    public function testRequisicaoSemRotaRegistraCaminhoSemQueryString(): void
    {
        Router::setRequest($this->request('/caminho-inexistente?token=abc'));

        $linha = $this->formatar();

        $this->assertSame('/caminho-inexistente', $linha['requisicao']['url']);
        $this->assertNull($linha['requisicao']['usuario_id']);
    }

    public function testTimestampComMilissegundosNoFusoDeCampoGrande(): void
    {
        $linha = $this->formatar();

        $this->assertMatchesRegularExpression(
            '/^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}\\.\\d{3}-04:00$/',
            $linha['timestamp'],
        );
    }

    public function testTimestampNaoDependeDoFusoPadraoDoPhp(): void
    {
        $fusoAnterior = date_default_timezone_get();
        date_default_timezone_set('Europe/Madrid');

        try {
            $this->assertStringEndsWith('-04:00', $this->formatar()['timestamp']);
        } finally {
            date_default_timezone_set($fusoAnterior);
        }
    }

    public function testScopeVazioEhOmitido(): void
    {
        $linha = json_decode((new JsonFormatter())->format('info', 'teste', ['chave' => 'valor', 'scope' => []]), true);

        $this->assertSame(['chave' => 'valor'], $linha['contexto']);
    }

    public function testScopePreenchidoEhMantido(): void
    {
        $linha = json_decode((new JsonFormatter())->format('info', 'teste', ['scope' => ['api']]), true);

        $this->assertSame(['api'], $linha['contexto']['scope']);
    }

    public function testCampoRequisicaoPodeSerDesativado(): void
    {
        $linha = json_decode((new JsonFormatter(['requisicao' => false]))->format('info', 'teste'), true);

        $this->assertArrayNotHasKey('requisicao', $linha);
    }

    public function testMensagemComUtf8InvalidoNaoPerdeALinha(): void
    {
        $linha = json_decode((new JsonFormatter())->format('error', "falha \xB1\x31"), true);

        $this->assertIsArray($linha);
        $this->assertStringStartsWith('falha ', $linha['mensagem']);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatar(): array
    {
        return json_decode((new JsonFormatter())->format('info', 'teste', ['chave' => 'valor']), true);
    }

    /**
     * Mesma interface do Authentication\Identity (getIdentifier), sem depender do plugin.
     */
    private function identidade(int $id): object
    {
        return new class ($id) {
            public function __construct(private int $id)
            {
            }

            public function getIdentifier(): int
            {
                return $this->id;
            }
        };
    }

    private function request(string $url): ServerRequest
    {
        return new ServerRequest([
            'url' => $url,
            'environment' => ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '10.0.0.5'],
        ]);
    }
}
