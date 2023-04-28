<?php

namespace App\Log\Engine;
use Cake\Log\Engine\BaseLog;
use App\Model\Table\LogsTable;
use Cake\Log\Log;

class DatabaseLog extends BaseLog
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function log($level, string|\Stringable $message, array $context = [])
    {
        $log = new LogsTable();
        $novoLog = $log->newEmptyEntity();
        $novoLog = $log->patchEntity($novoLog, $context['dados']);

        if ($log->save($novoLog) == false) {
            $mensagensErro = [];
            $erros = $novoLog->getErrors();
            array_walk_recursive(
                $erros,
                function ($msg, $tipoErro) use (&$mensagensErro)
                {
                    $mensagensErro[] = $msg;
                }
            );

            Log::warning('Erro ao salvar no BD os dados do log: ' . $novoLog . ' | Erros: ' . implode(',', $mensagensErro));
        }
    }
}
?>