<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\Database\Connection;
use Cake\Database\Driver\Postgres;
use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * Base fixture that keeps PostgreSQL identity/serial sequences in sync
 * after inserting records with an explicit 'id'.
 *
 * TRUNCATE ... RESTART IDENTITY resets the sequence to 1, but an explicit
 * 'id' in $records is inserted without touching the sequence, so the next
 * nextval() (e.g. from Table::save()) collides with that row's id. Fixtures
 * with an integer/serial 'id' column should extend this instead of
 * Cake\TestSuite\Fixture\TestFixture directly.
 */
abstract class AppFixture extends TestFixture
{
    public function insert(ConnectionInterface $connection): bool
    {
        $result = parent::insert($connection);

        if ($this->records && $connection instanceof Connection && $connection->getDriver() instanceof Postgres) {
            $this->syncPostgresSequence($connection);
        }

        return $result;
    }

    private function syncPostgresSequence(Connection $connection): void
    {
        if (!in_array('id', $this->_schema->columns(), true)) {
            return;
        }

        $table = $this->sourceName();
        $quotedTable = $connection->getDriver()->quoteIdentifier($table);

        $connection->execute(sprintf(
            "SELECT setval(pg_get_serial_sequence('%s', 'id'), COALESCE((SELECT MAX(id) FROM %s), 1))
             WHERE pg_get_serial_sequence('%s', 'id') IS NOT NULL",
            $table,
            $quotedTable,
            $table,
        ));
    }
}
