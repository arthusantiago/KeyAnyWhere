<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class UserDeleteCascadeEntradas extends AbstractMigration
{
    /**
     * Removendo do BD a relação entre o usuário e a entrada criada
     *
     * @access	public
     * @return	void
     */
    public function up(): void
    {
        $this->table('entradas')
        ->dropForeignKey('user_id')
        ->removeColumn('user_id')
        ->save();
    }

    public function down(): void
    {
    }
}
