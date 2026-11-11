<?php
declare(strict_types=1);

namespace App\Test\TestCase\Log;

use App\Log\JsonFormatter;
use App\Log\Seguranca;
use Cake\Log\Engine\ArrayLog;
use Cake\Log\Log;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * Captura em memória o que vai para o escopo `seguranca`, com o mesmo JsonFormatter do config/app.php,
 * para os testes conferirem evento, nível e contexto de cada linha.
 */
trait CapturaLogSegurancaTrait
{
    /**
     * @return void
     */
    #[Before]
    protected function capturarLogSeguranca(): void
    {
        Log::drop('captura_seguranca');
        Log::setConfig('captura_seguranca', [
            'className' => ArrayLog::class,
            'scopes' => [Seguranca::ESCOPO],
            'levels' => [],
            'formatter' => ['className' => JsonFormatter::class, 'app' => 'keyanywhere'],
        ]);
    }

    /**
     * @return void
     */
    #[After]
    protected function pararCapturaLogSeguranca(): void
    {
        Log::drop('captura_seguranca');
    }

    /**
     * Linhas gravadas no escopo `seguranca`, já decodificadas.
     *
     * @return list<array<string, mixed>>
     */
    protected function logsSeguranca(): array
    {
        $engine = Log::engine('captura_seguranca');
        $this->assertInstanceOf(ArrayLog::class, $engine);

        return array_map(
            fn(string $linha): array => (array)json_decode($linha, true, 512, JSON_THROW_ON_ERROR),
            array_values($engine->read()),
        );
    }

    /**
     * A linha do evento informado; falha o teste se não houver exatamente uma.
     *
     * @param string $evento
     * @return array<string, mixed>
     */
    protected function logSeguranca(string $evento): array
    {
        $linhas = array_values(array_filter(
            $this->logsSeguranca(),
            fn(array $linha): bool => ($linha['contexto']['evento'] ?? null) === $evento,
        ));
        $this->assertCount(1, $linhas, "Esperava uma linha do evento {$evento} no escopo seguranca.");

        return $linhas[0];
    }
}
