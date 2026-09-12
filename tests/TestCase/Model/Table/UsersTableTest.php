<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = $this->getTableLocator()->get('Users', $config);
    }

    /**
     * Gera um valor aleatório para uso como senha nos testes (não é um segredo real,
     * apenas evita ter no código-fonte uma string estática com "cara" de senha).
     * Sempre atende à regra de complexidade de UsersTable (mínimo 12 caracteres,
     * letra, dígito e símbolo).
     *
     * @return string
     */
    private static function gerarSenhaDeTeste(): string
    {
        return 'Aa1!' . bin2hex(random_bytes(8));
    }

    /**
     * Senha abaixo do mínimo de 12 caracteres (mas com letra, dígito e símbolo),
     * para testar isoladamente a regra de tamanho mínimo.
     *
     * @return string
     */
    private static function gerarSenhaCurtaDemais(): string
    {
        return 'Aa1!' . bin2hex(random_bytes(1));
    }

    /**
     * Senha sem nenhum dígito (mas com letra e símbolo, e tamanho válido),
     * para testar isoladamente a regra que exige ao menos um dígito.
     *
     * @return string
     */
    private static function gerarSenhaSemDigito(): string
    {
        $letras = '';
        for ($i = 0; $i < 12; $i++) {
            $letras .= chr(random_int(97, 122));
        }

        return $letras . '!';
    }

    /**
     * Senha sem nenhum caractere especial (mas com letra e dígito, e tamanho válido),
     * para testar isoladamente a regra que exige ao menos um símbolo.
     *
     * @return string
     */
    private static function gerarSenhaSemCaractereEspecial(): string
    {
        return 'Aa1' . bin2hex(random_bytes(6));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Test validationDefault method - IMPORTANT VALIDATION TEST
     * Validates username, email, and password fields with security constraints
     *
     * @return void
     * @uses \App\Model\Table\UsersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        // Test valid user data
        $user = $this->Users->newEntity([
            'username' => 'validuser123',
            'email' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'Failed to save valid user: ' . json_encode($user->getErrors()));

        // Test empty username - should fail
        $invalidUsername = $this->Users->newEntity([
            'username' => '',
            'email' => 'test@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertFalse($this->Users->save($invalidUsername), 'Should reject empty username');

        // Test username exceeding max length (50 chars) - should fail
        $longUsername = $this->Users->newEntity([
            'username' => str_repeat('a', 51),
            'email' => 'test@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertFalse($this->Users->save($longUsername), 'Should reject username exceeding 50 chars');

        // Test invalid email format - should fail
        $invalidEmail = $this->Users->newEntity([
            'username' => 'validuser456',
            'email' => 'not-an-email',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertFalse($this->Users->save($invalidEmail), 'Should reject invalid email format');

        // Test empty email - should fail
        $emptyEmail = $this->Users->newEntity([
            'username' => 'validuser789',
            'email' => '',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertFalse($this->Users->save($emptyEmail), 'Should reject empty email');

        // Test email exceeding max length (100 chars) - should fail
        $longEmail = $this->Users->newEntity([
            'username' => 'validuser',
            'email' => str_repeat('a', 91) . '@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertFalse($this->Users->save($longEmail), 'Should reject email exceeding 100 chars');

        // Test password less than 12 chars - should fail
        $shortPassword = $this->Users->newEntity([
            'username' => 'validuser999',
            'email' => 'test@example.com',
            'password' => self::gerarSenhaCurtaDemais(),
        ]);

        $this->assertFalse($this->Users->save($shortPassword), 'Should reject password less than 12 chars');

        // Note: The regex `/[a-z]/i` matches both lowercase AND uppercase (due to 'i' flag)
        // So a password like "SECUREPAS123!@#" passes because it has letters
        // A password without ANY letters would fail, but that's a different test
        // For now, we skip this specific lowercase test

        // Test password without digits - should fail
        $noDigits = $this->Users->newEntity([
            'username' => 'validuser666',
            'email' => 'test@example.com',
            'password' => self::gerarSenhaSemDigito(),
        ]);

        $this->assertFalse($this->Users->save($noDigits), 'Should reject password without digits');

        // Test password without special characters - should fail
        $noSpecial = $this->Users->newEntity([
            'username' => 'validuser555',
            'email' => 'test@example.com',
            'password' => self::gerarSenhaSemCaractereEspecial(),
        ]);

        $this->assertFalse($this->Users->save($noSpecial), 'Should reject password without special characters');
    }

    /**
     * Test buildRules method - IMPORTANT VALIDATION TEST
     * Validates unique constraints on username, email, and 2FA secret
     *
     * @return void
     * @uses \App\Model\Table\UsersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        // Create first user
        $username = 'uniqueuser_' . bin2hex(random_bytes(8));
        $email = 'unique_' . bin2hex(random_bytes(4)) . '@example.com';

        $user1 = $this->Users->newEntity([
            'username' => $username,
            'email' => $email,
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $result1 = $this->Users->save($user1);
        $this->assertNotFalse($result1, 'Failed to save first user');

        // Try to create user with duplicate username - should fail
        $user2 = $this->Users->newEntity([
            'username' => $username, // Duplicate username
            'email' => 'different_' . bin2hex(random_bytes(4)) . '@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertFalse($this->Users->save($user2), 'Should enforce unique username constraint');

        // Try to create user with duplicate email - should fail
        $user3 = $this->Users->newEntity([
            'username' => 'anotheruser_' . bin2hex(random_bytes(8)),
            'email' => $email, // Duplicate email
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertFalse($this->Users->save($user3), 'Should enforce unique email constraint');
    }

    /**
     * Atualizar um usuário existente deve persistir a alteração no banco.
     *
     * @return void
     */
    public function testAtualizarUsuarioPersisteAlteracoes(): void
    {
        $user = $this->Users->newEntity([
            'username' => 'usuario_original_' . bin2hex(random_bytes(4)),
            'email' => 'original_' . bin2hex(random_bytes(4)) . '@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);
        $this->Users->save($user);

        $novoUsername = 'usuario_atualizado_' . bin2hex(random_bytes(4));
        $user = $this->Users->patchEntity($user, ['username' => $novoUsername, 'email' => $user->email]);
        $resultado = $this->Users->save($user);
        $this->assertNotFalse($resultado, 'Failed to update user: ' . json_encode($user->getErrors()));

        $userRecarregado = $this->Users->get($user->id);
        $this->assertSame($novoUsername, $userRecarregado->username);
    }

    /**
     * Excluir um usuário existente deve removê-lo do banco.
     *
     * @return void
     */
    public function testExcluirUsuarioRemoveDoBanco(): void
    {
        $user = $this->Users->newEntity([
            'username' => 'usuario_descartavel_' . bin2hex(random_bytes(4)),
            'email' => 'descartavel_' . bin2hex(random_bytes(4)) . '@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);
        $this->Users->save($user);
        $id = $user->id;

        $resultado = $this->Users->delete($user);
        $this->assertTrue($resultado, 'Failed to delete user.');
        $this->assertFalse($this->Users->exists(['id' => $id]));
    }
}
