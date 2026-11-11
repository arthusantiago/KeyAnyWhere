<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class UpdateEntradasUsername extends BaseMigration
{
    public function up(): void
    {
        $this->table('entradas')
            ->changeColumn('username', 'string', ['null' => true, 'default' => null, 'limit' => 1024])
            ->changeColumn('password', 'string', ['null' => true, 'default' => null, 'limit' => 1024])
            ->update();
    }

    public function down(): void
    {
    }
}
