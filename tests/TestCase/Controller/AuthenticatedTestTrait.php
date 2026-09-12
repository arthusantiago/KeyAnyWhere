<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\User;
use Cake\Datasource\ConnectionManager;

/**
 * Pré-carrega a sessão de testes de integração com uma identidade autenticada,
 * simulando o que `Authentication.Session` leria de uma sessão real após login
 * (ver Authenticator\SessionAuthenticator::authenticate(), sessionKey 'Auth').
 */
trait AuthenticatedTestTrait
{
    /**
     * Pré-carrega a sessão como um usuário autenticado.
     *
     * @param array<string, mixed> $overrides Sobrescreve campos do usuário padrão (id=1, root=true da app.Users fixture).
     * @return void
     */
    protected function loginAsUser(array $overrides = []): void
    {
        $userData = $overrides + [
            'id' => 1,
            'username' => 'usuario.teste',
            'email' => 'usuario.teste@example.com',
            'root' => true,
            'tfa_ativo' => false,
        ];

        $this->session(['Auth' => new User($userData)]);
        // A maioria das actions de escrita (add/edit/delete) usa FormProtectionComponent —
        // sem isso, um POST/PUT/DELETE autenticado ainda seria rejeitado com 400.
        $this->enableSecurityToken();

        // App\Middleware\SessionsKawMiddleware lê a linha da sessão atual na tabela
        // 'sessions' (persistida normalmente pelo DatabaseSession handler no fim de um
        // request real de login). Como aqui a sessão é injetada diretamente, sem passar
        // por um login de verdade, essa linha nunca chega a existir — garantimos ela aqui
        // para o middleware não quebrar com um valor nulo.
        $this->garantirLinhaDeSessao((int)$userData['id']);
    }

    /**
     * Garante que exista uma linha na tabela 'sessions' para o id de sessão usado pelos
     * testes de integração (fixo em 'cli', ver tests/bootstrap.php), já com user_id/user_agent
     * preenchidos — do contrário App\Middleware\SessionsKawMiddleware tentaria lê-la como nula.
     *
     * A identidade injetada em loginAsUser() é simulada e pode não corresponder a uma linha
     * real em 'users' (ex.: loginAsNonRootUser() usa id=2, que não existe na app.Users fixture)
     * — nesse caso user_id fica nulo para não violar a foreign key.
     *
     * @param int $userId
     * @return void
     */
    private function garantirLinhaDeSessao(int $userId): void
    {
        $connection = ConnectionManager::get('test');
        $usuarioExiste = (bool)$connection->execute(
            'SELECT 1 FROM users WHERE id = :id',
            ['id' => $userId],
        )->fetch();

        $connection->execute(
            'INSERT INTO sessions (id, user_id, user_agent, created, modified) '
            . 'VALUES (:id, :user_id, :user_agent, NOW(), NOW()) '
            . 'ON CONFLICT (id) DO UPDATE SET user_id = EXCLUDED.user_id, user_agent = EXCLUDED.user_agent',
            ['id' => 'cli', 'user_id' => $usuarioExiste ? $userId : null, 'user_agent' => 'PHPUnit'],
        );
    }

    /**
     * Pré-carrega a sessão como um usuário autenticado não-root.
     *
     * A app.Users fixture só tem o usuário root (id=1); para que a linha de sessão
     * (ver garantirLinhaDeSessao()) referencie um user_id válido, garantimos aqui uma
     * segunda linha real na tabela 'users' para esse id.
     *
     * @param array<string, mixed> $overrides Sobrescreve campos do usuário padrão.
     * @return void
     */
    protected function loginAsNonRootUser(array $overrides = []): void
    {
        $id = (int)($overrides['id'] ?? 2);
        $this->garantirUsuarioExiste($id);

        $this->loginAsUser($overrides + [
            'id' => $id,
            'username' => 'usuario.comum',
            'email' => 'usuario.comum@example.com',
            'root' => false,
        ]);
    }

    /**
     * Garante que exista uma linha real na tabela 'users' para o id informado, com o
     * mínimo de colunas exigidas (não-root), sem sobrescrever uma linha já existente.
     *
     * @param int $id
     * @return void
     */
    private function garantirUsuarioExiste(int $id): void
    {
        ConnectionManager::get('test')->execute(
            'INSERT INTO users (id, username, email, password, tfa_ativo, root, created, modified) '
            . 'VALUES (:id, :username, :email, :password, false, false, NOW(), NOW()) '
            . 'ON CONFLICT (id) DO NOTHING',
            [
                'id' => $id,
                'username' => 'usuario.comum.' . $id,
                'email' => 'usuario.comum.' . $id . '@example.com',
                'password' => password_hash('SenhaFixture123!@#', PASSWORD_BCRYPT),
            ],
        );
    }
}
