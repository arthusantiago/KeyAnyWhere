<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemovendoSubcategoria extends AbstractMigration
{
    public function up(): void
    {
        //removendo a referencia
        $this->table('entradas')
            ->removeColumn('subcategoria_id')
            ->save();

        //removendo a tabela
        $this->table('subcategoria')->drop()->save();
    }

    public function down(): void
    {
    }
}
