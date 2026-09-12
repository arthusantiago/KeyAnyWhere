<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InsecurePasswordsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InsecurePasswordsTable Test Case
 */
class InsecurePasswordsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\InsecurePasswordsTable
     */
    protected $InsecurePasswords;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.InsecurePasswords',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('InsecurePasswords')
            ? []
            : ['className' => InsecurePasswordsTable::class];
        $this->InsecurePasswords = $this->getTableLocator()->get('InsecurePasswords', $config);
    }

    /**
     * Gera um valor aleatório para uso nos testes que não precisam de uma senha
     * específica do catálogo (não é um segredo real e, por ser aleatório, não
     * colide com a senha '123456' da fixture).
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
        unset($this->InsecurePasswords);

        parent::tearDown();
    }

    /**
     * Uma senha válida deve ser salva com sucesso.
     *
     * @return void
     */
    public function testValidationDefaultComSenhaValida(): void
    {
        $senha = $this->InsecurePasswords->newEntity(['password' => self::gerarSenhaDeTeste()]);

        $resultado = $this->InsecurePasswords->save($senha);
        $this->assertNotFalse($resultado, 'Failed to save insecure password: ' . json_encode($senha->getErrors()));
    }

    /**
     * password é obrigatório; ausente deve falhar.
     *
     * @return void
     */
    public function testValidationDefaultRejeitaPasswordAusente(): void
    {
        $semPassword = $this->InsecurePasswords->newEntity([]);

        $this->assertFalse($this->InsecurePasswords->save($semPassword), 'Should reject missing password');
    }

    /**
     * buildRules deve impedir duas ocorrências da mesma senha no catálogo.
     *
     * @return void
     */
    public function testBuildRulesRejeitaPasswordDuplicada(): void
    {
        // '123456' já existe na fixture.
        $duplicada = $this->InsecurePasswords->newEntity(['password' => '123456']);

        $this->assertFalse($this->InsecurePasswords->save($duplicada), 'Should enforce unique password constraint');
    }

    /**
     * Replica a consulta usada por EntradasController::senhaInsegura() — uma senha
     * presente no catálogo (em minúsculas) deve ser encontrada; uma ausente, não.
     *
     * @return void
     */
    public function testConsultaPorSenhaSeguindoOMesmoPadraoDoController(): void
    {
        $encontrada = $this->InsecurePasswords->find()
            ->where(['password' => strtolower('123456')])
            ->first();
        $this->assertNotNull($encontrada, 'Senha presente no catálogo deveria ser encontrada.');

        $naoEncontrada = $this->InsecurePasswords->find()
            ->where(['password' => strtolower(self::gerarSenhaDeTeste())])
            ->first();
        $this->assertNull($naoEncontrada, 'Senha ausente do catálogo não deveria ser encontrada.');
    }
}
