<?php
declare(strict_types=1);

namespace App\Log\Engine;

use App\Model\Table\LogsTable;
use Cake\Log\Engine\BaseLog;
use Cake\Log\Log;
use Stringable;

class DatabaseLog extends BaseLog
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function log($level, string|Stringable $message, array $context = []): void
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

            Log::warning('Erro ao salvar no BD os dados do log: ' . $novoLog . ' | Erros: ' . implode(',', $mensagensErro));
        }
    }
}
