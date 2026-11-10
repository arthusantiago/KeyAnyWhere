<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Auditoria;
use App\Model\Entity\Entrada;
use App\Model\Table\EntradasTable;
use Authentication\Identity;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
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
        'app.Auditorias',
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
     * Validates titulo, username, password, posicao, categoria_id, link, and anotacoes fields
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
            'posicao' => 1,
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

        // Note: Empty values for encrypted fields (username, password, anotacoes) are accepted
        // because they get encrypted and create a non-empty encrypted value.
        // The validator does not have notEmptyString for these fields in 'create' context.

        // Test username exceeding max length (472 chars) - should fail
        $longUsername = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => str_repeat('a', 473),
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($longUsername), 'Should reject username exceeding 472 chars');

        // Test password exceeding max length (472 chars) - should fail
        $longPassword = $this->Entradas->newEntity([
            'titulo' => 'Valid Title',
            'username' => 'user@example.com',
            'password' => str_repeat('a', 473),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertFalse($this->Entradas->save($longPassword), 'Should reject password exceeding 472 chars');

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

    /**
     * Atualizar uma entrada existente deve persistir a alteração no banco e,
     * com identidade autenticada, criar um registro de auditoria de 'edicao'
     * listando 'titulo' como alterado, sem persistir os valores de 'antes'/'depois'.
     *
     * @return void
     */
    public function testAtualizarEntradaComIdentidadeCriaRegistroDeAuditoriaDeEdicao(): void
    {
        $request = (new ServerRequest())->withAttribute('identity', new Identity(['id' => 1]));
        Router::setRequest($request);

        $entrada = $this->Entradas->get(1);
        $entrada = $this->Entradas->patchEntity($entrada, [
            'titulo' => 'Título Atualizado',
            'categoria_id' => $entrada->categoria_id,
            'posicao' => $entrada->posicao,
        ]);
        $resultado = $this->Entradas->save($entrada);
        $this->assertNotFalse($resultado, 'Failed to update entrada: ' . json_encode($entrada->getErrors()));

        $entradaRecarregada = $this->Entradas->get(1);
        $this->assertSame('Título Atualizado', $entradaRecarregada->tituloDescrip());

        $auditorias = $this->getTableLocator()->get('Auditorias');
        $registro = $auditorias->find()
            ->where(['contexto' => 'entrada', 'id_entidade' => 1, 'acao' => 'edicao'])
            ->first();

        $this->assertInstanceOf(Auditoria::class, $registro, 'Nenhum registro de auditoria de edição foi criado.');
        $alteracoes = $registro->alteracoes;
        $this->assertIsArray($alteracoes);
        $this->assertArrayHasKey('titulo', $alteracoes);
        $this->assertSame('', $alteracoes['titulo']['antes']);
        $this->assertSame('', $alteracoes['titulo']['depois']);
    }

    /**
     * O formulário de edição reenvia todos os campos (mesmo os não alterados)
     * pré-preenchidos com o valor atual descriptografado. Como a criptografia
     * usa um nonce aleatório a cada save, o ciphertext desses campos mudaria
     * mesmo sem mudança de texto puro — o que marcaria todos como alterados
     * na auditoria. Alterar somente o título não deve auditar username/password/
     * link/anotacoes.
     *
     * @return void
     */
    public function testAtualizarSomenteTituloNaoAuditaCamposReenviadosSemAlteracao(): void
    {
        $request = (new ServerRequest())->withAttribute('identity', new Identity(['id' => 1]));
        Router::setRequest($request);

        $entrada = $this->Entradas->get(1);
        $entrada = $this->Entradas->patchEntity($entrada, [
            'titulo' => 'Título Atualizado',
            'username' => $entrada->usernameDescrip(),
            'password' => $entrada->passwordDescrip(),
            'link' => $entrada->linkDescrip(),
            'anotacoes' => $entrada->anotacoesDescrip(),
            'categoria_id' => $entrada->categoria_id,
            'posicao' => $entrada->posicao,
        ]);
        $resultado = $this->Entradas->save($entrada);
        $this->assertNotFalse($resultado, 'Failed to update entrada: ' . json_encode($entrada->getErrors()));

        $auditorias = $this->getTableLocator()->get('Auditorias');
        $registro = $auditorias->find()
            ->where(['contexto' => 'entrada', 'id_entidade' => 1, 'acao' => 'edicao'])
            ->first();

        $this->assertInstanceOf(Auditoria::class, $registro, 'Nenhum registro de auditoria de edição foi criado.');
        $alteracoes = $registro->alteracoes;
        $this->assertIsArray($alteracoes);
        $this->assertSame(['titulo'], array_keys($alteracoes));
    }

    /**
     * Excluir uma entrada deve removê-la do banco e, com identidade autenticada,
     * criar um registro de auditoria de 'exclusao'.
     *
     * @return void
     */
    public function testExcluirEntradaComIdentidadeCriaRegistroDeAuditoriaDeExclusao(): void
    {
        $request = (new ServerRequest())->withAttribute('identity', new Identity(['id' => 1]));
        Router::setRequest($request);

        $entrada = $this->Entradas->get(1);
        $resultado = $this->Entradas->delete($entrada);
        $this->assertTrue($resultado, 'Failed to delete entrada.');
        $this->assertFalse($this->Entradas->exists(['id' => 1]));

        $auditorias = $this->getTableLocator()->get('Auditorias');
        $registro = $auditorias->find()
            ->where(['contexto' => 'entrada', 'id_entidade' => 1, 'acao' => 'exclusao'])
            ->first();

        $this->assertInstanceOf(Auditoria::class, $registro, 'Nenhum registro de auditoria de exclusão foi criado.');
    }

    /**
     * reordenar() põe as entradas da categoria em ordem alfabética do título (descriptografado), sem
     * registro de auditoria: é uma reordenação interna.
     *
     * @return void
     */
    public function testReordenarOrdenaPeloTituloSemAuditoria(): void
    {
        foreach (['banco', 'Abacaxi', 'casa'] as $titulo) {
            $this->Entradas->saveOrFail($this->Entradas->newEntity([
                'titulo' => $titulo,
                'username' => '',
                'password' => '',
                'anotacoes' => '',
                'categoria_id' => 1,
            ]), ['auditoria_acao' => false]);
        }
        $auditorias = $this->getTableLocator()->get('Auditorias');
        $antes = $auditorias->find()->count();

        $this->Entradas->reordenar(1);

        $ordem = [];
        foreach ($this->Entradas->find()->where(['categoria_id' => 1])->orderBy(['posicao' => 'ASC'])->all() as $entrada) {
            $this->assertInstanceOf(Entrada::class, $entrada);
            $ordem[] = $entrada->tituloDescrip();
        }
        $esperado = $ordem;
        usort($esperado, 'strcasecmp');
        $this->assertSame($esperado, $ordem);
        $this->assertSame(['Abacaxi', 'banco', 'casa'], array_values(array_intersect($ordem, ['Abacaxi', 'banco', 'casa'])));
        $this->assertSame($antes, $auditorias->find()->count());
    }
}
