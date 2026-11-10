<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Configuracao;
use App\Model\Table\ConfiguracoesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ConfiguracoesTable Test Case
 */
class ConfiguracoesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ConfiguracoesTable
     */
    protected ConfiguracoesTable $Configuracoes;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Configuracoes',
        'app.Auditorias',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Configuracoes') ? [] : ['className' => ConfiguracoesTable::class];
        $tabela = $this->getTableLocator()->get('Configuracoes', $config);
        $this->assertInstanceOf(ConfiguracoesTable::class, $tabela);
        $this->Configuracoes = $tabela;
    }

    /**
     * Test validationDefault method - IMPORTANT VALIDATION TEST
     * Validates configuration key and value fields
     *
     * @return void
     * @link \App\Model\Table\ConfiguracoesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        // Test valid configuration
        $config = $this->Configuracoes->newEntity([
            'chave' => 'app.setting.email',
            'valor' => 'admin@example.com',
        ]);

        $result = $this->Configuracoes->save($config);
        $this->assertNotFalse($result, 'Failed to save valid configuration: ' . json_encode($config->getErrors()));

        // Test empty chave - should fail
        $invalidChave = $this->Configuracoes->newEntity([
            'chave' => '',
            'valor' => 'some_value',
        ]);

        $this->assertFalse($this->Configuracoes->save($invalidChave), 'Should reject empty chave');

        // Test empty valor - should fail
        $invalidValor = $this->Configuracoes->newEntity([
            'chave' => 'app.setting.timeout',
            'valor' => '',
        ]);

        $this->assertFalse($this->Configuracoes->save($invalidValor), 'Should reject empty valor');

        // Test missing chave - should fail
        $noChave = $this->Configuracoes->newEntity([
            'valor' => 'some_value',
        ]);

        $this->assertFalse($this->Configuracoes->save($noChave), 'Should reject configuration without chave');
    }

    /**
     * Test buildRules method - IMPORTANT VALIDATION TEST
     * Validates unique constraint on chave field
     *
     * @return void
     * @link \App\Model\Table\ConfiguracoesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        // Create first configuration
        $chave = 'config.unique.' . bin2hex(random_bytes(8));
        $config1 = $this->Configuracoes->newEntity([
            'chave' => $chave,
            'valor' => 'first_value',
        ]);

        $result1 = $this->Configuracoes->save($config1);
        $this->assertNotFalse($result1, 'Failed to save first configuration');

        // Try to create duplicate chave - should fail
        $config2 = $this->Configuracoes->newEntity([
            'chave' => $chave, // Duplicate chave
            'valor' => 'second_value',
        ]);

        $this->assertFalse($this->Configuracoes->save($config2), 'Should enforce unique chave constraint');
    }

    /**
     * Test getValorConfig method - IMPORTANT UTILITY METHOD TEST
     * Retrieves configuration value by key
     *
     * @return void
     * @link \App\Model\Table\ConfiguracoesTable::getValorConfig()
     */
    public function testGetValorConfig(): void
    {
        // Create a test configuration
        $chave = 'test.config.key.' . bin2hex(random_bytes(4));
        $expectedValor = 'test_value_' . bin2hex(random_bytes(4));

        $config = $this->Configuracoes->newEntity([
            'chave' => $chave,
            'valor' => $expectedValor,
        ]);

        $this->Configuracoes->save($config);

        // Test retrieving existing configuration
        $result = $this->Configuracoes->getValorConfig($chave);
        $this->assertEquals($expectedValor, $result, 'Should retrieve correct configuration value');

        // Test retrieving non-existent configuration - should return empty string
        $nonExistent = $this->Configuracoes->getValorConfig('non.existent.key');
        $this->assertEquals('', $nonExistent, 'Should return empty string for non-existent configuration');
    }

    /**
     * Atualizar uma configuração existente deve persistir a alteração no banco.
     *
     * @return void
     */
    public function testAtualizarConfiguracaoPersisteAlteracoes(): void
    {
        $config = $this->Configuracoes->newEntity([
            'chave' => 'config.original.' . bin2hex(random_bytes(4)),
            'valor' => 'valor_original',
        ]);
        $this->Configuracoes->save($config);

        $config = $this->Configuracoes->patchEntity($config, ['valor' => 'valor_atualizado']);
        $resultado = $this->Configuracoes->save($config);
        $this->assertNotFalse($resultado, 'Failed to update configuration: ' . json_encode($config->getErrors()));

        $configRecarregada = $this->Configuracoes->get($config->id);
        $this->assertSame('valor_atualizado', $configRecarregada->valor);
    }

    /**
     * Excluir uma configuração existente deve removê-la do banco.
     *
     * @return void
     */
    public function testExcluirConfiguracaoRemoveDoBanco(): void
    {
        $config = $this->Configuracoes->newEntity([
            'chave' => 'config.descartavel.' . bin2hex(random_bytes(4)),
            'valor' => 'valor',
        ]);
        $this->Configuracoes->save($config);
        $id = $config->id;

        $resultado = $this->Configuracoes->delete($config);
        $this->assertTrue($resultado, 'Failed to delete configuration.');
        $this->assertFalse($this->Configuracoes->exists(['id' => $id]));
    }

    /**
     * salvarValorConfig() cria a configuração na primeira vez e atualiza o mesmo registro depois.
     *
     * @return void
     * @uses \App\Model\Table\ConfiguracoesTable::salvarValorConfig()
     */
    public function testSalvarValorConfigCriaEDepoisAtualiza(): void
    {
        $criada = $this->Configuracoes->salvarValorConfig(Configuracao::REGIONAL_IDIOMA, 'es');
        $this->assertInstanceOf(Configuracao::class, $criada);

        $atualizada = $this->Configuracoes->salvarValorConfig(Configuracao::REGIONAL_IDIOMA, 'pt_BR');
        $this->assertInstanceOf(Configuracao::class, $atualizada);

        $this->assertSame($criada->id, $atualizada->id);
        $this->assertSame(1, $this->Configuracoes->find()->where(['chave' => Configuracao::REGIONAL_IDIOMA])->count());
        $this->assertSame('pt_BR', $this->Configuracoes->getValorConfig(Configuracao::REGIONAL_IDIOMA));
    }

    /**
     * salvarValorConfig() devolve false quando o valor é inválido (vazio).
     *
     * @return void
     */
    public function testSalvarValorConfigComValorVazioFalha(): void
    {
        $this->assertFalse($this->Configuracoes->salvarValorConfig(Configuracao::REGIONAL_IDIOMA, ''));
    }

    /**
     * Sem nada gravado, não há preferências; depois, só as chaves regionais entram.
     *
     * @return void
     * @uses \App\Model\Table\ConfiguracoesTable::preferenciasRegionais()
     */
    public function testPreferenciasRegionais(): void
    {
        $this->assertSame([], $this->Configuracoes->preferenciasRegionais());

        $this->Configuracoes->salvarValorConfig(Configuracao::REGIONAL_FUSO_HORARIO, 'America/Sao_Paulo');
        $this->Configuracoes->salvarValorConfig(Configuracao::REGIONAL_IDIOMA, 'es');
        $this->Configuracoes->salvarValorConfig('outro.modulo.qualquer', 'valor');

        $this->assertEquals(
            ['fusoHorario' => 'America/Sao_Paulo', 'idioma' => 'es'],
            $this->Configuracoes->preferenciasRegionais(),
        );
    }

    /**
     * A tela "Configurações gerais" só aceita fusos da IANA e idiomas com tradução.
     *
     * @return void
     * @uses \App\Model\Table\ConfiguracoesTable::validationRegional()
     */
    public function testValidationRegional(): void
    {
        $validador = $this->Configuracoes->getValidator('regional');

        $this->assertSame([], $validador->validate(['fusoHorario' => 'America/Sao_Paulo', 'idioma' => 'es']));

        $erros = $validador->validate(['fusoHorario' => 'Marte/Olympus_Mons', 'idioma' => 'fr']);
        $this->assertArrayHasKey('inList', $erros['fusoHorario']);
        $this->assertArrayHasKey('inList', $erros['idioma']);

        $erros = $validador->validate([]);
        $this->assertArrayHasKey('fusoHorario', $erros);
        $this->assertArrayHasKey('idioma', $erros);
    }
}
