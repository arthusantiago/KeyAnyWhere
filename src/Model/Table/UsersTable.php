<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Custom\ValidatorKaw;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\EntradasTable&\Cake\ORM\Association\HasMany $Entradas
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @property \App\Model\Table\SessionsTable&\Cake\ORM\Association\HasMany $Sessions
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Sessions', [
            'foreignKey' => 'user_id',
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
            ->scalar('username')
            ->maxLength('username', 50, 'O nome do usuário pode ter no máximo 50 caracteres')
            ->requirePresence('username')
            ->notEmptyString('username', 'O nome do usuário precisa ser preenchido')
            ->checkXSS('username');

        $validator
            ->email('email', false, 'O e-mail não está em um formato válido.')
            ->maxLength('email', 100, 'O e-mail do usuário pode ter no máximo 100 caracteres')
            ->requirePresence('email')
            ->notEmptyString('email', 'O e-mail precisa ser preenchido')
            ->checkXSS('email');

        $validator
            ->scalar('password')
            ->minLength('password', 12, 'A senha precisa ter no mínimo 12 caracteres')
            ->maxLength('password', 255, 'A senha pode ter no máximo 255 caracteres')
            ->requirePresence('password', 'create', 'A senha precisa ser preenchida')
            ->notEmptyString('password', 'A senha precisa ser preenchida', 'create')
            ->add(
                'password',
                'requiMinPassword',
                [
                'rule' => function ($senha) {
                    $regexs = [
                        '/[a-z]/i',
                        '/\d/mxi',
                        '/[!@#$%\^&*()\-_=+]+/xm',
                    ];

                    foreach ($regexs as $regex) {
                        if (preg_match($regex, $senha) === 0) {
                            return false;
                        }
                    }

                    return true;
                },
                'message' => 'A senha não atende aos requisitos mínimos de segurança',
                ],
            );

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
                ['username'],
                'Já existe um usuário cadastrado com o mesmo nome de usuário',
            ),
            ['errorField' => 'username'],
        );
        $rules->add(
            $rules->isUnique(
                ['email'],
                'Já existe um usuário cadastrado com o mesmo e-mail',
            ),
            ['errorField' => 'email'],
        );
        $rules->add(
            $rules->isUnique(
                ['tfa_secret'],
                'A secret do 2FA coincidiu com outra salva no DB.',
            ),
            'ruleSecret2FAnaoUnica',
            [
                'errorField' => 'tfa_secret',
                'message' => 'A secret do 2FA coincidiu com outra. Tente novamente.',
            ],
        );

        return $rules;
    }
}
