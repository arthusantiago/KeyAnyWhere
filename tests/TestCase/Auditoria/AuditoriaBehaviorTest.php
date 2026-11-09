<?php
declare(strict_types=1);

namespace App\Test\TestCase\Auditoria;

use App\Model\Entity\Auditoria;
use App\Model\Entity\Entrada;
use App\Model\Table\AuditoriasTable;
use App\Model\Table\CategoriasTable;
use App\Model\Table\EntradasTable;
use App\Model\Table\UsersTable;
use Authentication\Identity;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * Auditoria ligada às Tables do core (Entradas, Categorias, Users) pelo AuditavelTrait.
 *
 * Testes de Table não têm request HTTP de verdade: a identidade (e o IP) são simulados
 * com Router::setRequest(). O TestCase do CakePHP chama Router::reload() no setUp, então
 * o request simulado não vaza para o teste seguinte.
 *
 * @uses \App\Auditoria\AuditoriaBehavior
 * @uses \App\Auditoria\AuditavelTrait
 */
class AuditoriaBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Auditorias',
        'app.Users',
        'app.Categorias',
        'app.Entradas',
        'app.Sessions',
    ];

    private CategoriasTable $Categorias;

    private EntradasTable $Entradas;

    private AuditoriasTable $Auditorias;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Categorias = $this->fetchTable(CategoriasTable::class);
        $this->Entradas = $this->fetchTable(EntradasTable::class);
        $this->Auditorias = $this->fetchTable(AuditoriasTable::class);
    }

    /**
     * @param int $idUsuario
     * @return void
     */
    private function autenticarComo(int $idUsuario): void
    {
        Router::setRequest(
            (new ServerRequest(['environment' => ['REMOTE_ADDR' => '198.51.100.7']]))
                ->withAttribute('identity', new Identity(['id' => $idUsuario])),
        );
    }

    /**
     * @param string $contexto
     * @param int $idEntidade
     * @return array<\App\Model\Entity\Auditoria>
     */
    private function registros(string $contexto, int $idEntidade): array
    {
        $registros = [];
        $consulta = $this->Auditorias->find()
            ->where(['contexto' => $contexto, 'id_entidade' => $idEntidade])
            ->orderByAsc('id');
        foreach ($consulta as $registro) {
            $this->assertInstanceOf(Auditoria::class, $registro);
            $registros[] = $registro;
        }

        return $registros;
    }

    /**
     * Criação com usuário autenticado: ação, usuário e IP do request, e só os nomes dos
     * campos (valores mascarados; id/created/modified fora do diff).
     *
     * @return void
     */
    public function testCriacaoComIdentidadeRegistraUsuarioIpECamposMascarados(): void
    {
        $this->autenticarComo(1);

        $categoria = $this->Categorias->saveOrFail($this->Categorias->newEntity(['nome' => 'Categoria Auditada']));

        $registros = $this->registros('categoria', $categoria->id);
        $this->assertCount(1, $registros);
        $this->assertInstanceOf(Auditoria::class, $registros[0]);
        $this->assertSame('criacao', $registros[0]->acao);
        $this->assertSame(1, $registros[0]->usuario_id);
        $this->assertSame('198.51.100.7', $registros[0]->ip_origem);
        $this->assertSame(['nome' => ['antes' => '', 'depois' => '']], $registros[0]->alteracoes);
    }

    /**
     * Sem identidade (ex.: primeiro usuário em configInicial) a ação também é auditada,
     * com usuario_id nulo — e o save de negócio não quebra.
     *
     * @return void
     */
    public function testCriacaoSemIdentidadeAuditaComUsuarioNulo(): void
    {
        $categoria = $this->Categorias->saveOrFail($this->Categorias->newEntity(['nome' => 'Sem Identidade']));

        $registros = $this->registros('categoria', $categoria->id);
        $this->assertCount(1, $registros);
        $this->assertNull($registros[0]->usuario_id);
    }

    /**
     * @return void
     */
    public function testEdicaoEExclusaoSaoAuditadas(): void
    {
        $this->autenticarComo(1);

        $categoria = $this->Categorias->get(1);
        $this->Categorias->saveOrFail($this->Categorias->patchEntity($categoria, ['nome' => 'Nome Novo']));

        $edicao = $this->registros('categoria', 1);
        $this->assertCount(1, $edicao);
        $this->assertSame('edicao', $edicao[0]->acao);
        $this->assertSame(['nome'], array_keys((array)$edicao[0]->alteracoes));

        $descartavel = $this->Categorias->saveOrFail($this->Categorias->newEntity(['nome' => 'Descartável']));
        $this->Categorias->deleteOrFail($descartavel);
        $acoes = array_map(fn(Auditoria $a) => $a->acao, $this->registros('categoria', $descartavel->id));
        $this->assertSame(['criacao', 'exclusao'], $acoes);
    }

    /**
     * O formulário de edição reenvia todos os campos, descriptografados. Como a
     * criptografia usa nonce aleatório, sem Entrada::isModified() todos apareceriam como
     * alterados. Alterando só o título, só o título vai para a auditoria.
     *
     * @return void
     */
    public function testEdicaoDeEntradaSoListaOsCamposQueMudaram(): void
    {
        $this->autenticarComo(1);

        $entrada = $this->Entradas->get(1);
        $this->assertInstanceOf(Entrada::class, $entrada);
        $entrada = $this->Entradas->patchEntity($entrada, [
            'titulo' => 'Título Atualizado',
            'username' => $entrada->usernameDescrip(),
            'password' => $entrada->passwordDescrip(),
            'link' => $entrada->linkDescrip(),
            'anotacoes' => $entrada->anotacoesDescrip(),
            'categoria_id' => $entrada->categoria_id,
        ]);
        $this->Entradas->saveOrFail($entrada);

        $registros = $this->registros('entrada', 1);
        $this->assertCount(1, $registros);
        $this->assertSame(['titulo'], array_keys((array)$registros[0]->alteracoes));
    }

    /**
     * `auditoria_acao` com texto registra uma ação de negócio no lugar de 'criacao'/'edicao';
     * com false, suprime o registro.
     *
     * @return void
     */
    public function testAuditoriaAcaoPersonalizadaESuprimida(): void
    {
        $this->autenticarComo(1);

        $categoria = $this->Categorias->get(1);
        $categoria->posicao = 5;
        $this->Categorias->saveOrFail($categoria, ['auditoria_acao' => 'acao_de_negocio']);
        $categoria->posicao = 6;
        $this->Categorias->saveOrFail($categoria, ['auditoria_acao' => false]);

        $acoes = array_map(fn(Auditoria $a) => $a->acao, $this->registros('categoria', 1));
        $this->assertSame(['acao_de_negocio'], $acoes);
    }

    /**
     * CategoriasTable::reordenar() regrava a posição de todas as categorias; é manutenção
     * interna, não ação do usuário, e não pode gerar registros de 'edicao'.
     *
     * @return void
     */
    public function testReordenarCategoriasNaoGeraAuditoria(): void
    {
        $this->autenticarComo(1);
        $this->Categorias->saveOrFail($this->Categorias->newEntity(['nome' => 'Zebra']));
        $antes = $this->Auditorias->find()->count();

        $this->Categorias->reordenar();

        $this->assertSame($antes, $this->Auditorias->find()->count());
    }

    /**
     * Excluir um usuário que aparece na trilha funciona (o banco não tem FK de
     * auditorias.usuario_id) e a trilha mantém o autor.
     *
     * @return void
     */
    public function testExcluirUsuarioComRegistrosDeAuditoriaMantemATrilha(): void
    {
        $users = $this->fetchTable(UsersTable::class);
        $usuario = $users->newEntity([
            'username' => 'usuario.comum',
            'email' => 'usuario.comum@example.com',
            'password' => 'Aa1!' . bin2hex(random_bytes(8)),
        ]);
        $users->saveOrFail($usuario);

        $this->autenticarComo($usuario->id);
        $categoria = $this->Categorias->saveOrFail($this->Categorias->newEntity(['nome' => 'Feita Pelo Comum']));

        $this->autenticarComo(1);
        $this->assertTrue($users->delete($usuario));

        $registros = $this->registros('categoria', $categoria->id);
        $this->assertCount(1, $registros);
        $this->assertSame($usuario->id, $registros[0]->usuario_id);

        $exclusao = $this->Auditorias->find()
            ->where(['contexto' => 'user', 'id_entidade' => $usuario->id, 'acao' => 'exclusao'])
            ->first();
        $this->assertInstanceOf(Auditoria::class, $exclusao);
        $this->assertSame(1, $exclusao->usuario_id);
    }
}
