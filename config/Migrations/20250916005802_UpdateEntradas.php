<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class UpdateEntradas extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('entradas')
            ->addColumn('posicao', 'integer', ['null' => true, 'default' => 0])
            ->update();
    }
}
