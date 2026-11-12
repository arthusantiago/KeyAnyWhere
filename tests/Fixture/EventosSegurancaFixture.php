<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\Database\Connection;
use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventosSegurancaFixture
 *
 * Começa vazia: os registros vêm do App\Log\Engine\EventosSegurancaLog durante o teste.
 */
class EventosSegurancaFixture extends TestFixture
{
    /**
     * @var string
     */
    public string $table = 'eventos_seguranca';

    /**
     * @return void
     */
    public function init(): void
    {
        $this->records = [];
        parent::init();
    }

    /**
     * Limpa a tabela antes do teste. O TruncateStrategy só a limpa no fim dos testes que declaram esta
     * fixture, e todo teste que grava um evento de segurança (um login errado, um XSS recusado) grava
     * nela, mesmo sem declará-la: sem isto, o teste seguinte encontrava as linhas dele, conforme a ordem.
     *
     * @param \Cake\Datasource\ConnectionInterface $connection
     * @return bool
     */
    public function insert(ConnectionInterface $connection): bool
    {
        if ($connection instanceof Connection) {
            $connection->execute('DELETE FROM ' . $this->sourceName());
        }

        return parent::insert($connection);
    }
}
