<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CategoriasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CategoriasTable Test Case
 */
class CategoriasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CategoriasTable
     */
    protected $Categorias;

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
        $config = $this->getTableLocator()->exists('Categorias') ? [] : ['className' => CategoriasTable::class];
        $this->Categorias = $this->getTableLocator()->get('Categorias', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Categorias);

        parent::tearDown();
    }

    /**
     * Test validationDefault method - valid nome should pass
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithValidNome(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => 'Categoria Válida',
        ]);

        $result = $this->Categorias->save($categoria);
        $this->assertNotFalse($result, 'Failed to save categoria: ' . json_encode($categoria->getErrors()));
    }

    /**
     * Test validationDefault method - empty nome should fail
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithEmptyNomeFails(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => '',
        ]);

        $this->assertFalse($this->Categorias->save($categoria));
        $this->assertArrayHasKey('nome', $categoria->getErrors());
    }

    /**
     * Test validationDefault method - null nome should fail
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithNullNomeFails(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => null,
        ]);

        $this->assertFalse($this->Categorias->save($categoria));
        $this->assertArrayHasKey('nome', $categoria->getErrors());
    }

    /**
     * Test validationDefault method - nome exceeding 88 characters should fail
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithLongNomeFails(): void
    {
        $longNome = str_repeat('a', 89); // Max is 88

        $categoria = $this->Categorias->newEntity([
            'nome' => $longNome,
        ]);

        $this->assertFalse($this->Categorias->save($categoria));
        $this->assertArrayHasKey('nome', $categoria->getErrors());
    }

    /**
     * Test validationDefault method - nome with exactly 88 characters should pass
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithExactlyMaxLengthNome(): void
    {
        $nomeExact = str_repeat('a', 88); // Exactly max

        $categoria = $this->Categorias->newEntity([
            'nome' => $nomeExact,
        ]);

        $result = $this->Categorias->save($categoria);
        $this->assertNotFalse($result, 'Failed to save categoria: ' . json_encode($categoria->getErrors()));
    }

    /**
     * Test validationDefault method - XSS payload should fail
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithXSSPayloadFails(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => '<script>alert("XSS")</script>',
        ]);

        $this->assertFalse($this->Categorias->save($categoria));
        $this->assertArrayHasKey('nome', $categoria->getErrors());
    }

    /**
     * Test validationDefault method - XSS event handler payload should fail
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithEventHandlerXSSPayloadFails(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => '<img src=x onerror="alert(1)">',
        ]);

        $this->assertFalse($this->Categorias->save($categoria));
        $this->assertArrayHasKey('nome', $categoria->getErrors());
    }

    /**
     * Test validationDefault method - legitimate HTML entities should pass
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithHTMLEntities(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => 'Categoria & Empresa "Test"',
        ]);

        $result = $this->Categorias->save($categoria);
        $this->assertNotFalse($result, 'Failed to save categoria: ' . json_encode($categoria->getErrors()));
    }

    /**
     * Test validationDefault method - numeric nome should pass
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithNumericNome(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => '123456',
        ]);

        $result = $this->Categorias->save($categoria);
        $this->assertNotFalse($result, 'Failed to save categoria: ' . json_encode($categoria->getErrors()));
    }

    /**
     * Test validationDefault method - special characters should pass
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::validationDefault()
     */
    public function testValidationDefaultWithSpecialCharacters(): void
    {
        $categoria = $this->Categorias->newEntity([
            'nome' => 'Banco@2024 #Principal!',
        ]);

        $result = $this->Categorias->save($categoria);
        $this->assertNotFalse($result, 'Failed to save categoria: ' . json_encode($categoria->getErrors()));
    }

    /**
     * Test reordenar() - reorders categorias alphabetically by decrypted nome
     *
     * @return void
     * @uses \App\Model\Table\CategoriasTable::reordenar()
     */
    public function testReordenarOrdenaAlfabeticamentePeloNomeDescriptografado(): void
    {
        $zebra = $this->Categorias->newEntity(['nome' => 'Zebra']);
        $this->Categorias->save($zebra);
        $abelha = $this->Categorias->newEntity(['nome' => 'Abelha']);
        $this->Categorias->save($abelha);

        $this->Categorias->reordenar();

        $zebraRecarregada = $this->Categorias->get($zebra->id);
        $abelhaRecarregada = $this->Categorias->get($abelha->id);

        $this->assertLessThan($zebraRecarregada->posicao, $abelhaRecarregada->posicao);
    }
}
