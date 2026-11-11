<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AuditoriasCreatedSemDefaultBanco extends BaseMigration
{
    /**
     * Change Method.
     *
     * Remove o DEFAULT CURRENT_TIMESTAMP do banco: `created` passa a ser
     * preenchido pelo TimestampBehavior (PHP), como o resto do app — o
     * DEFAULT do Postgres usava o timezone da sessão de banco (sempre UTC,
     * ver config/app.php), independente do timezone configurado no app
     * (App.defaultTimezone), o que fazia os horários exibidos ficarem incorretos.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('auditorias')
            ->changeColumn('created', 'datetime', ['default' => null])
            ->update();
    }
}
