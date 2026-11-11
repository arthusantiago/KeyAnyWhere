<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Auditoria\AuditavelTrait;
use App\Model\Custom\ValidatorKaw;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Categorias Model
 *
 * @property \App\Model\Table\EntradasTable&\Cake\ORM\Association\HasMany $Entradas
 * @method \App\Model\Entity\Categoria newEmptyEntity()
 * @method \App\Model\Entity\Categoria newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Categoria[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Categoria get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Categoria findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Categoria patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Categoria[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Categoria|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Categoria saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Categoria[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Categoria>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Categoria[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Categoria> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Categoria[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Categoria>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Categoria[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Categoria> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class CategoriasTable extends Table
{
    use AuditavelTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('categorias');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Mascara antes/depois: este app guarda segredos (credenciais) e a trilha de
        // auditoria é imutável — guarda só o nome dos campos alterados.
        $this->adicionarAuditoria('categoria', mascararAlteracoes: true);

        $this->hasMany('Entradas', [
            'foreignKey' => 'categoria_id',
        ]);

        $this->_validatorClass = ValidatorKaw::class;
    }

    /**
     * Default validation rules.
     *
     * @param \App\Model\Custom\ValidatorKaw $validator Validator instance.
     * @return \App\Model\Custom\ValidatorKaw
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 88, ValidatorKaw::mensagemErroPadrao('maxLength', ['Nome', 88]))
            ->requirePresence('nome', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Nome']))
            ->notEmptyString('nome', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['Nome']))
            ->checkXSS('nome', ValidatorKaw::mensagemErroPadrao('checkXSS', ['Nome']));

        return $validator;
    }

    /**
     * Reordena as categorias no BD.
     * Por padrão as categorias são ordenadas alfabeticamente.
     *
     * @access public
     * @return void
     */
    public function reordenar(): void
    {
        $categorias = $this
            ->find('all')
            ->select(['id', 'nome'])
            ->toArray();
        $novaOrdenacao = [];

        foreach ($categorias as $categoria) {
            $novaOrdenacao[] = $categoria->nomeDescrip() . '-' . $categoria->id;
            unset($categoria);
        }
        unset($categorias);
        sort($novaOrdenacao, SORT_STRING | SORT_FLAG_CASE);

        foreach ($novaOrdenacao as $novaPosicao => $nomeCate) {
            $posicao = strripos($nomeCate, '-');
            $idCategoria = substr($nomeCate, ++$posicao);
            $categoria = $this->get($idCategoria);
            $categoria->posicao = $novaPosicao;
            // Reordenação interna, não é ação do usuário: sem isso, cada criação/edição de
            // categoria geraria registros de 'edicao' a mais na auditoria.
            $this->save($categoria, ['auditoria_acao' => false]);
            unset($nomeCate, $categoria);
        }
        unset($novaOrdenacao);
    }
}
