<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EntradasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EntradasTable Test Case
 */
class EntradasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EntradasTable
     */
    protected $Entradas;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Categorias',
        'app.Entradas',
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
        $config = $this->getTableLocator()->exists('Entradas') ? [] : ['className' => EntradasTable::class];
        $this->Entradas = $this->getTableLocator()->get('Entradas', $config);
    }

    /**
     * Gera um valor aleatório para uso como senha nos testes (não é um segredo real,
     * apenas evita ter no código-fonte uma string estática com "cara" de senha).
     *
     * @return string
     */
    private static function gerarSenhaDeTeste(): string
    {
        return 'senha_teste_' . bin2hex(random_bytes(6));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Entradas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method - IMPORTANT VALIDATION TEST
     * Validates titulo, username, password, categoria_id, link, and anotacoes fields
     *
     * @return void
     * @uses \App\Model\Table\EntradasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        // Test valid entrada data (anotacoes is NOT NULL, so must provide a value)
        $entrada = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'link' => 'https://example.com',
            'anotacoes' => '', // Empty string is allowed for NOT NULL varchar
        ]);

        $result = $this->Entradas->save($entrada);
        $this->assertNotFalse($result, 'Failed to save valid entrada: ' . json_encode($entrada->getErrors()));

        // Test empty titulo - should fail
        $invalidTitulo = $this->Entradas->newEntity([
            'titulo' => '',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($invalidTitulo), 'Should reject empty titulo');

        // Test titulo exceeding max length (87 chars) - should fail
        $longTitulo = $this->Entradas->newEntity([
            'titulo' => str_repeat('a', 88),
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($longTitulo), 'Should reject titulo exceeding 87 chars');

        // Test empty username - should fail (unlike pago, community requires notEmptyString here)
        $emptyUsername = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => '',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($emptyUsername), 'Should reject empty username');

        // Test username exceeding max length (88 chars) - should fail
        $longUsername = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => str_repeat('a', 89),
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($longUsername), 'Should reject username exceeding 88 chars');

        // Test empty password - should fail
        $emptyPassword = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => '',
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($emptyPassword), 'Should reject empty password');

        // Test password exceeding max length (88 chars) - should fail
        $longPassword = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => str_repeat('a', 89),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($longPassword), 'Should reject password exceeding 88 chars');

        // Test missing categoria_id - should fail
        $noCategoryId = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($noCategoryId), 'Should reject missing categoria_id');

        // Test empty categoria_id - should fail
        $emptyCategoryId = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => '',
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($emptyCategoryId), 'Should reject empty categoria_id');

        // Test invalid link format (not a URL) - should fail
        $invalidLink = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'link' => 'not-a-url',
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($invalidLink), 'Should reject invalid URL format');

        // Test link without protocol - should fail
        $linkNoProtocol = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'link' => 'example.com',
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($linkNoProtocol), 'Should reject URL without protocol');

        // Test link exceeding max length (400 chars) - should fail
        $longLink = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'link' => 'https://' . str_repeat('a', 394) . '.com',
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($longLink), 'Should reject link exceeding 400 chars');

        // Test anotacoes exceeding max length (1000 chars) - should fail
        $longAnotacoes = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'anotacoes' => str_repeat('a', 1001),
        ]);

        $this->assertFalse($this->Entradas->save($longAnotacoes), 'Should reject anotacoes exceeding 1000 chars');
    }

    /**
     * Test buildRules method - IMPORTANT VALIDATION TEST
     * Validates foreign key constraint on categoria_id
     *
     * @return void
     * @uses \App\Model\Table\EntradasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        // Test valid entrada with existing categoria_id
        $entrada = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1, // Exists in fixture
            'anotacoes' => '',
        ]);

        $result = $this->Entradas->save($entrada);
        $this->assertNotFalse($result, 'Failed to save entrada with valid categoria_id');

        // Test entrada with non-existent categoria_id - should fail
        $invalidCategoryId = $this->Entradas->newEntity([
            'titulo' => 'Invalid Category',
            'username' => 'user@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 9999, // Does not exist
            'anotacoes' => '',
        ]);

        $this->assertFalse(
            $this->Entradas->save($invalidCategoryId),
            'Should enforce foreign key constraint on categoria_id',
        );
    }
}
