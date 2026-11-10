<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Remove a tabela `ips_bloqueados`: a funcionalidade "IPs Bloqueados" (bloqueio automático
 * do IP após 3 erros de login no dia, tela de listagem/cadastro e página de bloqueio) deixou
 * de existir em 2026-11-09. O evento C1-3 continua sendo registrado no log
 * de segurança, só sem a reação de bloquear o IP.
 */
class RemoveIpsBloqueados extends BaseMigration
{
    /**
     * @return void
     */
    public function up(): void
    {
        $this->table('ips_bloqueados')->drop()->save();
    }

    /**
     * Recria a tabela vazia, como na migration inicial (os IPs bloqueados não voltam).
     *
     * @return void
     */
    public function down(): void
    {
        $this->table('ips_bloqueados')
            ->addColumn('ip', 'string', ['limit' => 39, 'null' => false])
            ->addColumn('created', 'datetime')
            ->addColumn('modified', 'datetime')
            ->create();
    }
}
