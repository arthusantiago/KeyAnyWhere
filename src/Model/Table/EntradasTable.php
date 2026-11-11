<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Auditoria\AuditavelTrait;
use App\Model\Custom\ValidatorKaw;
use App\Model\Entity\Entrada;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Entradas Model
 *
 * @property \App\Model\Table\CategoriasTable&\Cake\ORM\Association\BelongsTo $Categorias
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\Entrada newEmptyEntity()
 * @method \App\Model\Entity\Entrada newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Entrada[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Entrada get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Entrada findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Entrada patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Entrada[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Entrada|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Entrada saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Entrada>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Entrada> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Entrada>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Entrada> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class EntradasTable extends Table
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

        $this->setTable('entradas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Mascara antes/depois: este app guarda segredos (credenciais) e a trilha de
        // auditoria é imutável — guarda só o nome dos campos alterados.
        $this->adicionarAuditoria('entrada', mascararAlteracoes: true);

        $this->belongsTo('Categorias', [
            'foreignKey' => 'categoria_id',
            'joinType' => 'INNER',
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
            ->scalar('titulo')
            ->maxLength('titulo', 87, ValidatorKaw::mensagemErroPadrao('maxLength', ['Título', 87]))
            ->requirePresence('titulo', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Título']))
            ->notEmptyString('titulo', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['Título']))
            ->checkXSS('titulo', ValidatorKaw::mensagemErroPadrao('checkXSS', ['Título']));

        $validator
            ->scalar('username')
            ->maxLength('username', 472, ValidatorKaw::mensagemErroPadrao('maxLength', ['Usuário', 472]))
            ->requirePresence('username', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Usuário']))
            ->checkXSS('username', ValidatorKaw::mensagemErroPadrao('checkXSS', ['Usuário']));

        $validator
            ->scalar('password')
            ->maxLength('password', 472, ValidatorKaw::mensagemErroPadrao('maxLength', ['Senha', 472]))
            ->requirePresence('password', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Senha']));

        $validator
            ->numeric('posicao', ValidatorKaw::mensagemErroPadrao('numeric', ['posicao']))
            ->notEmptyString('posicao', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['posicao']));

        $validator
            ->numeric('categoria_id', ValidatorKaw::mensagemErroPadrao('numeric', ['Categoria']))
            ->requirePresence('categoria_id', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['Categoria']))
            ->notEmptyString('categoria_id', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['Categoria']));

        $validator
            ->url('link', ValidatorKaw::mensagemErroPadrao('url', ['Link']))
            ->urlWithProtocol('link', ValidatorKaw::mensagemErroPadrao('urlWithProtocol', ['Link']))
            ->maxLength('link', 400, ValidatorKaw::mensagemErroPadrao('maxLength', ['Link', 400]))
            ->allowEmptyString('link')
            ->checkXSS('link', ValidatorKaw::mensagemErroPadrao('checkXSS', ['Link']));

        $validator
            ->maxLength('anotacoes', 1000, ValidatorKaw::mensagemErroPadrao('maxLength', ['Anotações', 1000]))
            ->allowEmptyString('anotacoes')
            ->checkXSS('anotacoes', ValidatorKaw::mensagemErroPadrao('checkXSS', ['Anotações']));

        return $validator;
    }

    /**
     * Faz a validação dos dados recebidos na requisição para copiar dados para a área de transferência.
     *
     * @access public
     * @param \App\Model\Custom\ValidatorKaw $validator Validator instance.
     * @return \App\Model\Custom\ValidatorKaw
     */
    public function validationClipboard(Validator $validator): Validator
    {
        $validator
            ->requirePresence('id', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['id']))
            ->notEmptyString('id', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['id']))
            ->maxLength('id', 5, ValidatorKaw::mensagemErroPadrao('maxLength', ['id', 5]))
            ->integer('id', ValidatorKaw::mensagemErroPadrao('integer', ['id']))
            ->checkXSS('id', ValidatorKaw::mensagemErroPadrao('checkXSS', ['id']));

        $validator
            ->requirePresence('type', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['type']))
            ->notEmptyString('type', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['type']))
            ->maxLength('type', 8, ValidatorKaw::mensagemErroPadrao('maxLength', ['type', 8]))
            ->inList('type', ['password', 'user'], ValidatorKaw::mensagemErroPadrao('inList', ['type']))
            ->checkXSS('type', ValidatorKaw::mensagemErroPadrao('checkXSS', ['type']));

        return $validator;
    }

    /**
     * Faz a validação dos dados recebidos na requisição para buscar por entradas.
     *
     * @access public
     * @param \App\Model\Custom\ValidatorKaw $validator Validator instance.
     * @return \App\Model\Custom\ValidatorKaw
     */
    public function validationBusca(Validator $validator): Validator
    {
        $validator
            ->requirePresence('stringBusca', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['stringBusca']))
            ->notEmptyString('stringBusca', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['stringBusca']))
            ->checkXSS('stringBusca', ValidatorKaw::mensagemErroPadrao('checkXSS', ['stringBusca']));

        return $validator;
    }

    /**
     * Faz a validação dos dados recebidos na requisição para verificar se a senha está na lista de senhas inseguras.
     *
     * @access public
     * @param \App\Model\Custom\ValidatorKaw $validator Validator instance.
     * @return \App\Model\Custom\ValidatorKaw
     */
    public function validationSenhaInsegura(Validator $validator): Validator
    {
        $validator
            ->scalar('password')
            ->maxLength('password', 88, ValidatorKaw::mensagemErroPadrao('maxLength', ['password', 88]))
            // `true`, não 'create': o controller valida com `validate($dados, false)` (não é um registro novo).
            ->requirePresence('password', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['password']))
            ->notEmptyString('password', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['password']));

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(
            $rules->existsIn(
                'categoria_id',
                'Categorias',
                ValidatorKaw::mensagemErroPadrao('existsIn', ['Categoria', 'Categorias']),
            ),
            ['errorField' => 'categoria_id'],
        );

        return $rules;
    }

    /**
     * Define a `posicao` das entradas da categoria pela ordem alfabética do título. Os títulos são
     * criptografados no banco, então a ordenação é feita aqui, com os títulos descriptografados.
     *
     * Grava só a `posicao`, com `updateAll()`: é uma reordenação interna, não uma ação do usuário, e não
     * deve gerar registro de auditoria nem mudar o `modified` das entradas.
     *
     * @param int $categoriaId
     * @return void
     */
    public function reordenar(int $categoriaId): void
    {
        $titulos = [];
        $entradas = $this->find()
            ->select(['id', 'titulo'])
            ->where(['categoria_id' => $categoriaId])
            ->all();
        foreach ($entradas as $entrada) {
            if ($entrada instanceof Entrada) {
                $titulos[] = ['id' => $entrada->id, 'titulo' => $entrada->tituloDescrip()];
            }
        }
        unset($entradas);

        usort($titulos, fn(array $a, array $b): int => strcasecmp($a['titulo'], $b['titulo']) ?: $a['id'] <=> $b['id']);

        foreach ($titulos as $posicao => $item) {
            $this->updateAll(['posicao' => $posicao], ['id' => $item['id']]);
        }
        unset($titulos);
    }
}
