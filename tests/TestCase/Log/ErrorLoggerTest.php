<?php
declare(strict_types=1);

namespace App\Test\TestCase\Log;

use App\Log\ErrorLogger;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Error\PhpError;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\HttpException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\InvalidCsrfTokenException;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Closure;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Stringable;
use Throwable;

class ErrorLoggerTest extends TestCase
{
    /**
     * @var list<array{nivel: mixed, mensagem: string, contexto: array<string, mixed>}>
     */
    private array $registros = [];

    protected function setUp(): void
    {
        parent::setUp();
        Router::reload();
    }

    protected function tearDown(): void
    {
        Router::reload();
        parent::tearDown();
    }

    /**
     * Cenário do log de produção (2026-10-12): CSRF inválido saía como `error`, com classe,
     * stack trace, URL e Referer misturados num texto só.
     */
    public function testExcecao4xxViraWarningComCamposEstruturados(): void
    {
        $request = new ServerRequest([
            'url' => '/users/config-inicial',
            'environment' => [
                'REQUEST_METHOD' => 'POST',
                'HTTP_REFERER' => 'https://app.example.com/users/redefinir-senha/token-secreto-123',
            ],
        ]);

        $this->logger()->logException(new InvalidCsrfTokenException('Missing or invalid CSRF cookie.'), $request, true);

        $registro = $this->registros[0];
        $this->assertSame('warning', $registro['nivel']);
        $this->assertSame(
            '[Cake\Http\Exception\InvalidCsrfTokenException] Missing or invalid CSRF cookie.',
            $registro['mensagem'],
        );

        $excecao = $registro['contexto']['excecao'];
        $this->assertSame(InvalidCsrfTokenException::class, $excecao['classe']);
        $this->assertSame(403, $excecao['codigo']);
        $this->assertSame(__FILE__, $excecao['arquivo']);
        $this->assertIsInt($excecao['linha']);
        $this->assertNotEmpty($excecao['trace']);
        $this->assertContainsOnly('string', $excecao['trace']);

        // Referer e URL não vão crus: o caminho pode carregar segredo.
        $this->assertSame('https://app.example.com', $registro['contexto']['referer_origem']);
        $this->assertStringNotContainsString('token-secreto-123', (string)json_encode($registro));
    }

    /**
     * Só o código de uma HttpException/CakeException entre 400 e 499 vira warning: o de uma
     * exceção comum não conta (ex.: um RuntimeException com código 404 continua error).
     *
     * @return array<string, array{\Throwable, string}>
     */
    public static function niveisDasExcecoes(): array
    {
        return [
            'HTTP 400' => [new BadRequestException(), 'warning'],
            'HTTP 499' => [new HttpException('x', 499), 'warning'],
            'HTTP 500' => [new InternalErrorException(), 'error'],
            'CakeException 400' => [new CakeException('x', 400), 'warning'],
            'CakeException 404 (registro não encontrado)' => [new RecordNotFoundException('Registro não encontrado'), 'warning'],
            'exceção comum com código 404' => [new RuntimeException('x', 404), 'error'],
        ];
    }

    #[DataProvider('niveisDasExcecoes')]
    public function testNivelDaExcecao(Throwable $excecao, string $nivel): void
    {
        $this->logger()->logException($excecao);

        $this->assertSame($nivel, $this->registros[0]['nivel']);
    }

    /**
     * No formato `points` do Debugger, cada linha do trace vem como arquivo e linha: o log
     * guarda "arquivo:linha", uma por item, sem cortar o trace.
     */
    public function testTraceNoFormatoPoints(): void
    {
        Configure::write('Error.traceFormat', 'points');
        try {
            $this->logger()->logException(new RuntimeException('falhou'), null, true);
        } finally {
            Configure::delete('Error.traceFormat');
        }

        $trace = $this->registros[0]['contexto']['excecao']['trace'];
        $this->assertGreaterThan(1, count($trace));
        $this->assertMatchesRegularExpression('/\.php:\d+$/', $trace[0]);
        $this->assertContainsOnly('string', $trace);
    }

    public function testExcecaoDaAplicacaoContinuaErrorComExcecaoAnterior(): void
    {
        $anterior = new LogicException('causa original');
        $this->logger()->logException(new RuntimeException('falhou', 0, $anterior));

        $registro = $this->registros[0];
        $this->assertSame('error', $registro['nivel']);
        $this->assertSame(LogicException::class, $registro['contexto']['excecao']['anterior']['classe']);
        $this->assertSame('causa original', $registro['contexto']['excecao']['anterior']['mensagem']);
        $this->assertArrayNotHasKey('trace', $registro['contexto']['excecao']);
    }

    public function testSemRequestNoRouterIncluiDadosDaRequisicaoNoContexto(): void
    {
        $request = new ServerRequest(['url' => '/rota-que-nao-existe', 'environment' => ['REQUEST_METHOD' => 'GET']]);

        $this->logger()->logException(new RuntimeException('antes do roteamento'), $request);

        $requisicao = $this->registros[0]['contexto']['requisicao'];
        $this->assertSame('GET', $requisicao['metodo']);
        $this->assertSame('/rota-que-nao-existe', $requisicao['url']);
    }

    public function testComRequestNoRouterNaoDuplicaDadosDaRequisicao(): void
    {
        $request = new ServerRequest(['url' => '/clientes', 'environment' => ['REQUEST_METHOD' => 'GET']]);
        Router::setRequest($request);

        $this->logger()->logException(new RuntimeException('falhou'), $request);

        // Os dados da requisição já vêm no campo `requisicao` do JsonFormatter.
        $this->assertArrayNotHasKey('requisicao', $this->registros[0]['contexto']);
    }

    /**
     * Erros do PHP (warning, notice...), não exceções: mensagem curta e os detalhes em
     * `contexto.erro`, com o trace como lista.
     */
    public function testErroDoPhpComCamposEstruturados(): void
    {
        $erro = new PhpError(E_WARNING, 'Undefined array key 3', '/app/src/Model/Entity/Telefone.php', 42, [
            ['reference' => 'App\Model\Entity\Telefone::formatado()', 'file' => '/app/src/Model/Entity/Telefone.php', 'line' => 42],
            ['reference' => '[main]'],
        ]);

        $this->logger()->logError($erro, null, true);

        $this->assertSame([[
            'nivel' => 'warning',
            'mensagem' => 'Undefined array key 3',
            'contexto' => ['erro' => [
                'tipo' => 'warning',
                'mensagem' => 'Undefined array key 3',
                'arquivo' => '/app/src/Model/Entity/Telefone.php',
                'linha' => 42,
                'trace' => [
                    'App\Model\Entity\Telefone::formatado() /app/src/Model/Entity/Telefone.php, line 42',
                    '[main]',
                ],
            ]],
        ]], $this->registros);
    }

    /**
     * Mesmo mapeamento do ErrorLogger do CakePHP: deprecated vira debug.
     *
     * @return array<string, array{int, mixed}>
     */
    public static function niveisDosErros(): array
    {
        return [
            'notice' => [E_USER_NOTICE, 'notice'],
            'deprecated vira debug' => [E_USER_DEPRECATED, LOG_DEBUG],
            'erro' => [E_USER_ERROR, 'error'],
        ];
    }

    #[DataProvider('niveisDosErros')]
    public function testNivelDoErroDoPhp(int $codigo, mixed $nivel): void
    {
        $this->logger()->logError(new PhpError($codigo, 'algo'));

        $this->assertSame($nivel, $this->registros[0]['nivel']);
        $this->assertArrayNotHasKey('trace', $this->registros[0]['contexto']['erro'], 'sem includeTrace, sem trace');
    }

    public function testErroDoPhpComRequestForaDoRouterIncluiARequisicao(): void
    {
        $request = new ServerRequest([
            'url' => '/clientes',
            'environment' => ['REQUEST_METHOD' => 'GET', 'HTTP_REFERER' => 'https://app.example.com/x/segredo'],
        ]);

        $this->logger()->logError(new PhpError(E_USER_WARNING, 'algo'), $request);

        $contexto = $this->registros[0]['contexto'];
        $this->assertSame('https://app.example.com', $contexto['referer_origem']);
        $this->assertSame('GET', $contexto['requisicao']['metodo']);
    }

    private function logger(): ErrorLogger
    {
        $registrar = function (mixed $nivel, string $mensagem, array $contexto): void {
            $this->registros[] = ['nivel' => $nivel, 'mensagem' => $mensagem, 'contexto' => $contexto];
        };

        return new class ($registrar) extends ErrorLogger {
            public function __construct(private Closure $registrar)
            {
                parent::__construct();
            }

            /**
             * @param array<string, mixed> $context
             */
            public function log($level, Stringable|string $message, array $context = []): void
            {
                ($this->registrar)($level, (string)$message, $context);
            }
        };
    }
}
