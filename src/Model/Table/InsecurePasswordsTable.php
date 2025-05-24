<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Custom\ValidatorKaw;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InsecurePasswords Model
 *
 * @method \App\Model\Entity\InsecurePassword newEmptyEntity()
 * @method \App\Model\Entity\InsecurePassword newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\InsecurePassword[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\InsecurePassword get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\InsecurePassword findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\InsecurePassword patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\InsecurePassword[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\InsecurePassword|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\InsecurePassword saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\InsecurePassword[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\InsecurePassword>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\InsecurePassword[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\InsecurePassword> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\InsecurePassword[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\InsecurePassword>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\InsecurePassword[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\InsecurePassword> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class InsecurePasswordsTable extends Table
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

        $this->setTable('insecure_passwords');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('password')
            ->maxLength('password', 100)
            ->requirePresence('password', 'create')
            ->notEmptyString('password')
            ->checkXSS('password');

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
            $rules->isUnique(
                ['password'],
                'Esta senha já está cadastrada',
            ),
            ['errorField' => 'password'],
        );

        return $rules;
    }
}
