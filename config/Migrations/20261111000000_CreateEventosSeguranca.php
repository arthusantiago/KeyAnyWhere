<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Eventos de segurança para os usuários (tela "Eventos de segurança" e bloco na home): uma linha por
 * evento do escopo `seguranca` (ver App\Log\Engine\EventosSegurancaLog), só com o que interessa mostrar.
 */
class CreateEventosSeguranca extends BaseMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        $this->table('eventos_seguranca')
            ->addColumn('evento', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('nivel', 'string', ['limit' => 10, 'null' => false])
            // A conta afetada: quem estava logado ou, no login, a dona do e-mail digitado (se existir).
            ->addColumn('usuario_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('ip', 'string', ['limit' => 45, 'null' => true, 'default' => null])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addIndex(['usuario_id', 'created'])
            ->addIndex(['created'])
            ->create();
    }
}
