<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Custom\ValidatorKaw;
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
            ->maxLength('titulo', 87, 'A título pode ter no máximo 87 caracteres')
            ->requirePresence('titulo', 'create', 'O título precisa ser informado')
            ->notEmptyString('titulo', 'O título não pode estar vazio')
            ->checkXSS('titulo');

        $validator
            ->scalar('username')
            ->maxLength('username', 88, 'O username pode ter no máximo 88 caracteres')
            ->requirePresence('username', 'create', 'O username precisa ser informado')
            ->notEmptyString('username', 'O username não pode estar vazio')
            ->checkXSS('username');

        $validator
            ->scalar('password')
            ->maxLength('password', 88, 'A senha pode ter no máximo 88 caracteres')
            ->requirePresence('password', 'create', 'A senha precisa ser informada')
            ->notEmptyString('password', 'A senha não pode estar vazia');

        $validator
            ->numeric('categoria_id', 'Precisa informar um ID de categoria válido')
            ->requirePresence('categoria_id', true, 'O ID da categoria precisa ser informado')
            ->notEmptyString('categoria_id', 'O ID da categoria precisa ser informado');

        $validator
            ->url('link', 'O link precisa ser uma URL válida')
            ->urlWithProtocol('link', 'O link precisa ter o protocolo, exemplo: http:// ou https://')
            ->maxLength('link', 400, 'O link pode ter no máximo 210 caracteres')
            ->allowEmptyString('link')
            ->checkXSS('link');

        $validator
            ->maxLength('anotacoes', 1000, 'A anotação pode ter no máximo 1000 caracteres')
            ->allowEmptyString('anotacoes')
            ->checkXSS('anotacoes');

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
        $rules->add($rules->existsIn('categoria_id', 'Categorias', 'Categoria não encontrada'), ['errorField' => 'categoria_id']);

        return $rules;
    }
}
