<?php
declare(strict_types=1);

namespace App\Model\Table;

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
 * @method \App\Model\Entity\IpsBloqueado get($primaryKey, $options = [])
 * @method \App\Model\Entity\IpsBloqueado findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\IpsBloqueado patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\IpsBloqueado|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\IpsBloqueado saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
            ->ip('ip', 'O IP informado é inválido')
            ->requirePresence('ip', 'create', 'O IP precisa ser informado')
            ->notEmptyString('ip', 'O IP precisa ser informado');

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
                'O IP já está cadastrado'
            ),
            ['errorField' => 'ip']
        );

        return $rules;
    }
}
