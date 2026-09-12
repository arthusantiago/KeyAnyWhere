<?php
declare(strict_types=1);

namespace App\Log\Engine;

use App\Model\Table\LogsTable;
use Cake\Log\Engine\BaseLog;
use Cake\Log\Log;
use Stringable;

class DatabaseLog extends BaseLog
{
    /**
     * Constructor.
     *
     * @param array<string, mixed> $config Config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level The log level.
     * @param \Stringable|string $message The log message.
     * @param array<mixed> $context Additional information about the logged message.
     * @return void
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $log = new LogsTable();
        $novoLog = $log->newEmptyEntity();
        $novoLog = $log->patchEntity($novoLog, $context['dados']->toArray());

        if ($log->save($novoLog) == false) {
            $mensagensErro = [];
            $erros = $novoLog->getErrors();
            array_walk_recursive(
                $erros,
                function ($msg, $tipoErro) use (&$mensagensErro): void {
                    $mensagensErro[] = $msg;
                },
            );

            Log::warning(
                'Erro ao salvar no BD os dados do log: ' . $novoLog . ' | Erros: ' . implode(',', $mensagensErro),
            );
        }
    }
}
