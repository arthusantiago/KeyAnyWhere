<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventosSegurancaFixture
 *
 * Começa vazia: os registros vêm do App\Log\Engine\EventosSegurancaLog durante o teste. Declarar a
 * fixture garante que a tabela seja limpa entre os testes.
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
}
