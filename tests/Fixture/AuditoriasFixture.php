<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AuditoriasFixture
 *
 * Começa vazia: os registros vêm do AuditoriaBehavior durante o teste. Declarar a
 * fixture garante que a tabela seja limpa entre os testes.
 */
class AuditoriasFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [];
        parent::init();
    }
}
