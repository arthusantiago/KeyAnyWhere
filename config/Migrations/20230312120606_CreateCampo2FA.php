<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateCampo2FA extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->table('users')
        ->addColumn('tfa_secret', 'string', ['limit' => 144, 'null' => true, 'default' => null])
        ->update();
    }
}
