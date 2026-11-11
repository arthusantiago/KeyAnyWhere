<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Auditoria\AuditavelTrait;
use App\Model\Custom\ValidatorKaw;
use App\Model\Entity\User;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use function Cake\I18n\__;

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

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Mascara antes/depois: este app guarda segredo (credenciais, chaves de API) e a trilha de
        // auditoria é imutável — guarda só o nome dos campos alterados.
        $this->adicionarAuditoria('user', mascararAlteracoes: true);

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
            ->maxLength('username', 50, ValidatorKaw::mensagemErroPadrao('maxLength', ['Nome', 50]))
            ->requirePresence('username', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Nome']))
            ->notEmptyString('username', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['Nome']))
            ->checkXSS('username', ValidatorKaw::mensagemErroPadrao('checkXSS', ['Nome']));

        $validator
            ->email('email', false, ValidatorKaw::mensagemErroPadrao('email', ['E-mail']))
            ->maxLength('email', 100, ValidatorKaw::mensagemErroPadrao('maxLength', ['E-mail', 100]))
            ->requirePresence('email', ValidatorKaw::mensagemErroPadrao('requirePresence', ['E-mail']))
            ->notEmptyString('email', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['E-mail']))
            ->checkXSS('email', ValidatorKaw::mensagemErroPadrao('checkXSS', ['E-mail']));

        $validator
            ->scalar('password')
            ->minLength('password', 12, ValidatorKaw::mensagemErroPadrao('minLength', ['Senha', 12]))
            ->maxLength('password', 255, ValidatorKaw::mensagemErroPadrao('maxLength', ['Senha', 255]))
            ->requirePresence('password', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Senha']))
            ->notEmptyString('password', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['Senha']), 'create')
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
                'message' => __('A senha não atende aos requisitos mínimos de segurança'),
                ],
            );

        return $validator;
    }

    /**
     * Validation rules for generating a new QR Code for Two Factor Authentication (2FA).
     *
     * @param \App\Model\Custom\ValidatorKaw $validator Validator instance.
     * @return \App\Model\Custom\ValidatorKaw
     */
    public function validationQrCode2fa(Validator $validator): Validator
    {
        $validator
            ->requirePresence('idUser', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['idUser']))
            ->notEmptyString('idUser', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['idUser']))
            ->integer('idUser', ValidatorKaw::mensagemErroPadrao('integer', ['idUser']));

        $validator
            ->requirePresence('novoQrCode', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['novoQrCode']))
            ->notEmptyString('novoQrCode', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['novoQrCode']))
            ->boolean('novoQrCode', ValidatorKaw::mensagemErroPadrao('boolean', ['novoQrCode']));

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
                ValidatorKaw::mensagemErroPadrao('isUnique', ['Nome']),
            ),
            ['errorField' => 'username'],
        );
        $rules->add(
            $rules->isUnique(
                ['email'],
                ValidatorKaw::mensagemErroPadrao('isUnique', ['E-mail']),
            ),
            ['errorField' => 'email'],
        );
        $rules->add(
            $rules->isUnique(
                ['tfa_secret'],
                ValidatorKaw::mensagemErroPadrao('isUnique', ['tfa_secret']),
            ),
            'ruleSecret2FAnaoUnica',
            [
                'errorField' => 'tfa_secret',
                'message' => __('A secret do 2FA coincidiu com outra. Tente novamente.'),
            ],
        );

        return $rules;
    }

    ############################ REGRAS DE NEGOCIO ############################

    /**
     * RN: Método que verifica se é preciso executar a configuração inicial do sistema.
     * Criando o primeiro usuário e ativando o 2FA.
     *
     * @access public
     * @return bool
     */
    public function configInicialSistema(): bool
    {
        $primeiroUsuario = $this
            ->find()
            ->orderByAsc('id')
            ->first();

        // Sem usuário, ou com o primeiro usuário ainda sem o 2FA ativo.
        return !$primeiroUsuario instanceof User || !$primeiroUsuario->tfa_ativo;
    }
}
