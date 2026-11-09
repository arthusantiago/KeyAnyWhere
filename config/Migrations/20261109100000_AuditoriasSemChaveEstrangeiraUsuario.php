<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Remove a chave estrangeira `auditorias.usuario_id → users.id`.
 *
 * Com ela (ON DELETE RESTRICT), excluir um usuário que já fez qualquer ação — e por isso
 * aparece em `auditorias.usuario_id` — falhava no banco ("Restrict violation") e a exclusão
 * terminava em erro 500. As alternativas tinham custo pior:
 * - ON DELETE SET NULL apagaria o autor dos registros (a trilha é imutável e "quem fez" é
 *   justamente o que ela guarda; nulo já significa "ação sem login");
 * - ON DELETE CASCADE apagaria a trilha do usuário.
 *
 * Sem a FK, o `usuario_id` continua gravado depois da exclusão (a tela mostra
 * "usuário excluído (#id)"), e a integridade na gravação continua garantida pela regra
 * `existsIn(['usuario_id'], 'Users')` da AuditoriasTable. O índice
 * `idx_auditorias_usuario_id` continua.
 */
class AuditoriasSemChaveEstrangeiraUsuario extends BaseMigration
{
    /**
     * @return void
     */
    public function up(): void
    {
        $this->table('auditorias')
            ->dropForeignKey('usuario_id', 'fk_auditorias_usuario_id')
            ->update();
    }

    /**
     * Recria a FK. Falha se houver registro de auditoria de um usuário já excluído.
     *
     * @return void
     */
    public function down(): void
    {
        $this->table('auditorias')
            ->addForeignKey('usuario_id', 'users', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'RESTRICT',
                'name' => 'fk_auditorias_usuario_id',
            ])
            ->update();
    }
}
