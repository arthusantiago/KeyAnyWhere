<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Permite auditoria sem usuário autenticado (`usuario_id` nulo).
 *
 * Alguns saves legítimos acontecem antes de existir qualquer usuário/identidade — ex.: o
 * primeiro usuário root criado em `UsersController::configInicial()`. Com a coluna NOT NULL,
 * o AuditoriaBehavior enviava usuario_id nulo e o registro era descartado na
 * validação ("Falha ao salvar auditoria").
 */
class AuditoriasUsuarioIdOpcional extends BaseMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('auditorias')
            ->changeColumn('usuario_id', 'integer', ['null' => true])
            ->update();
    }
}
