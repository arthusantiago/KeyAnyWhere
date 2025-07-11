<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Custom\ValidatorKaw;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * IpsBloqueados Model
 *
 * @method \App\Model\Entity\IpsBloqueado newEmptyEntity()
 * @method \App\Model\Entity\IpsBloqueado newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\IpsBloqueado findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IpsBloqueado>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IpsBloqueado> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IpsBloqueado>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IpsBloqueado> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class IpsBloqueadosTable extends Table
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

        $this->setTable('ips_bloqueados');
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
            ->ip('ip', 'O IP informado é inválido')
            ->requirePresence('ip', 'create', 'O IP precisa ser informado')
            ->notEmptyString('ip', 'O IP precisa ser informado')
            ->checkXSS('ip');

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
                ['ip'],
                'O IP já está cadastrado',
            ),
            ['errorField' => 'ip'],
        );

        return $rules;
    }

    public function findUltimosBloqueados(Query $query, array $options)
    {
        $query
            ->select(['ip', 'created'])
            ->orderByDesc('created')
            ->limit(7);

        return $query;
    }
}
