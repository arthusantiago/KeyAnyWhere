<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Entradas Model
 *
 * @property \App\Model\Table\CategoriasTable&\Cake\ORM\Association\BelongsTo $Categorias
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\Entrada newEmptyEntity()
 * @method \App\Model\Entity\Entrada newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Entrada[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Entrada get($primaryKey, $options = [])
 * @method \App\Model\Entity\Entrada findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Entrada patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Entrada[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Entrada|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Entrada saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('titulo')
            ->maxLength('titulo', 100)
            ->requirePresence('titulo', 'create')
            ->notEmptyString('titulo');

        $validator
            ->scalar('username')
            ->maxLength('username', 100)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('password')
            ->maxLength('password', 256)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('link')
            ->maxLength('link', 500)
            ->allowEmptyString('link');

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
        $rules->add($rules->existsIn('user_id', 'Users', 'Usuário não localizado'), ['errorField' => 'user_id']);

        return $rules;
    }
}
