<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Auditoria\RegistroAuditoria;
use App\Model\Table\AuditoriasTable;
use Cake\Log\Engine\ArrayLog;
use Cake\Log\Log;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AuditoriasTable Test Case
 *
 * Na aplicação, os registros só são criados pelo callback do AuditoriaBehavior (ver
 * EntradasTable/CategoriasTable/UsersTable) — nenhum controller expõe edição ou exclusão
 * desta tabela. Aqui ficam a validação, as regras e o `registrar()`, isolados do behavior.
 *
 * @uses \App\Model\Table\AuditoriasTable
 */
class AuditoriasTableTest extends TestCase
{
    /**
     * @var \App\Model\Table\AuditoriasTable
     */
    protected AuditoriasTable $Auditorias;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Auditorias',
        'app.Users',
    ];

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Auditorias') ? [] : ['className' => AuditoriasTable::class];
        /** @var \App\Model\Table\AuditoriasTable $auditorias */
        $auditorias = $this->getTableLocator()->get('Auditorias', $config);
        $this->Auditorias = $auditorias;
    }

    /**
     * @return void
     */
    public function testValidationDefaultComDadosValidos(): void
    {
        $auditoria = $this->Auditorias->newEntity([
            'contexto' => 'entrada',
            'id_entidade' => 1,
            'acao' => 'criacao',
            'usuario_id' => 1,
            'alteracoes' => ['titulo' => ['antes' => null, 'depois' => 'Novo título']],
        ]);

        $resultado = $this->Auditorias->save($auditoria);
        $this->assertNotFalse($resultado, 'Failed to save auditoria: ' . json_encode($auditoria->getErrors()));
    }

    /**
     * Campos obrigatórios ausentes (contexto, acao, usuario_id) devem falhar.
     *
     * @return void
     */
    public function testValidationDefaultRejeitaCamposObrigatoriosAusentes(): void
    {
        $semContexto = $this->Auditorias->newEntity([
            'id_entidade' => 1,
            'acao' => 'criacao',
            'usuario_id' => 1,
        ]);
        $this->assertFalse($this->Auditorias->save($semContexto), 'Should reject missing contexto');

        $semAcao = $this->Auditorias->newEntity([
            'contexto' => 'entrada',
            'id_entidade' => 1,
            'usuario_id' => 1,
        ]);
        $this->assertFalse($this->Auditorias->save($semAcao), 'Should reject missing acao');

        $semUsuario = $this->Auditorias->newEntity([
            'contexto' => 'entrada',
            'id_entidade' => 1,
            'acao' => 'criacao',
        ]);
        $this->assertFalse($this->Auditorias->save($semUsuario), 'Should reject missing usuario_id');
    }

    /**
     * Sem FK no banco, a regra existsIn é o que garante, na gravação, que o usuário existe.
     *
     * @return void
     */
    public function testBuildRulesRejeitaUsuarioInexistente(): void
    {
        $auditoria = $this->Auditorias->newEntity([
            'contexto' => 'entrada',
            'id_entidade' => 1,
            'acao' => 'criacao',
            'usuario_id' => 9999,
        ]);

        $this->assertFalse($this->Auditorias->save($auditoria), 'Should enforce existsIn rule on usuario_id');
    }

    /**
     * Ação sem usuário autenticado (ex.: primeiro usuário em configInicial) é auditada com
     * usuario_id nulo.
     *
     * @return void
     */
    public function testUsuarioIdNuloEhAceito(): void
    {
        $auditoria = $this->Auditorias->newEntity([
            'contexto' => 'user',
            'id_entidade' => 1,
            'acao' => 'criacao',
            'usuario_id' => null,
        ]);

        $this->assertNotFalse($this->Auditorias->save($auditoria), (string)json_encode($auditoria->getErrors()));
        $this->assertNull($this->Auditorias->get($auditoria->id)->usuario_id);
    }

    /**
     * @return void
     */
    public function testRegistrarSemMascaraGuardaAntesEDepois(): void
    {
        $this->assertTrue($this->Auditorias->registrar($this->registro(1)));

        $auditoria = $this->Auditorias->find()->orderByDesc('id')->firstOrFail();
        $this->assertSame('entrada', $auditoria->get('contexto'));
        $this->assertSame('edicao', $auditoria->get('acao'));
        $this->assertSame(1, $auditoria->get('usuario_id'));
        $this->assertSame('127.0.0.1', $auditoria->get('ip_origem'));
        $this->assertSame(
            ['password' => ['antes' => 'segredo-antigo', 'depois' => 'segredo-novo']],
            $auditoria->get('alteracoes'),
        );
    }

    /**
     * Com máscara (como todas as Tables do KAW usam), fica só o nome dos campos.
     *
     * @return void
     */
    public function testRegistrarComMascaraGuardaSoONomeDosCampos(): void
    {
        $this->assertTrue($this->Auditorias->registrar($this->registro(1), mascararAlteracoes: true));

        $auditoria = $this->Auditorias->find()->orderByDesc('id')->firstOrFail();
        $this->assertSame(['password' => ['antes' => '', 'depois' => '']], $auditoria->get('alteracoes'));
    }

    /**
     * Falha ao registrar não lança exceção (o save de negócio não pode ser desfeito):
     * vira Log::error.
     *
     * @return void
     */
    public function testRegistrarComFalhaLogaErroSemLancarExcecao(): void
    {
        Log::setConfig('captura', ['className' => ArrayLog::class, 'levels' => ['error']]);

        try {
            $this->assertFalse($this->Auditorias->registrar($this->registro(9999)));

            $engine = Log::engine('captura');
            $this->assertInstanceOf(ArrayLog::class, $engine);
            $logs = $engine->read();
        } finally {
            Log::drop('captura');
        }

        $this->assertCount(1, $logs);
        $this->assertStringContainsString('Falha ao salvar auditoria', $logs[0]);
        $this->assertSame(0, $this->Auditorias->find()->count());
    }

    /**
     * @param int|null $usuarioId Usuário do registro.
     * @return \App\Auditoria\RegistroAuditoria
     */
    private function registro(?int $usuarioId): RegistroAuditoria
    {
        return new RegistroAuditoria(
            'entrada',
            1,
            'edicao',
            $usuarioId,
            ['password' => ['antes' => 'segredo-antigo', 'depois' => 'segredo-novo']],
            '127.0.0.1',
        );
    }
}
