<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Auditoria\RegistraAuditoriaTrait;
use App\Auditoria\RegistradorAuditoriaInterface;
use App\Model\Custom\ValidatorKaw;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Auditorias Model
 *
 * Tabela só de INSERT e SELECT: sem edição nem exclusão pela aplicação, de propósito.
 * Os registros são criados só pelo callback do AuditoriaBehavior de cada Table auditada,
 * via `registrar()` (RegistraAuditoriaTrait). `usuario_id` nulo = ação sem usuário
 * autenticado (ex.: criação do primeiro usuário em `UsersController::configInicial()`).
 *
 * Sem edição/exclusão de propósito: só os métodos de criação e leitura têm o tipo declarado.
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 * @method \App\Model\Entity\Auditoria newEmptyEntity()
 * @method \App\Model\Entity\Auditoria newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Auditoria get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Auditoria|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Auditoria saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 */
class AuditoriasTable extends Table implements RegistradorAuditoriaInterface
{
    use RegistraAuditoriaTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('auditorias');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        // `created` preenchido no PHP (fuso do app), não por DEFAULT do banco (sessão em UTC).
        $this->addBehavior('Timestamp');

        $this->_validatorClass = ValidatorKaw::class;

        $this->belongsTo('Users', [
            'foreignKey' => 'usuario_id',
        ]);
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
            ->scalar('contexto')
            ->maxLength('contexto', 100, 'O contexto pode ter no máximo 100 caracteres')
            ->requirePresence('contexto', 'create', 'O contexto precisa ser informado')
            ->notEmptyString('contexto', 'O contexto precisa ser informado')
            ->checkXSS('contexto');

        $validator
            ->integer('id_entidade', 'O registro precisa ser um número inteiro')
            ->requirePresence('id_entidade', 'create', 'O registro precisa ser informado')
            ->notEmptyString('id_entidade', 'O registro precisa ser informado');

        $validator
            ->scalar('acao')
            ->maxLength('acao', 50, 'A ação pode ter no máximo 50 caracteres')
            ->requirePresence('acao', 'create', 'A ação precisa ser informada')
            ->notEmptyString('acao', 'A ação precisa ser informada')
            ->checkXSS('acao');

        $validator
            ->integer('usuario_id', 'O usuário precisa ser um número inteiro')
            ->requirePresence('usuario_id', 'create', 'O usuário precisa ser informado')
            ->allowEmptyString('usuario_id');

        $validator
            ->allowEmptyArray('alteracoes');

        $validator
            ->scalar('ip_origem')
            ->maxLength('ip_origem', 45, 'O IP de origem pode ter no máximo 45 caracteres')
            ->allowEmptyString('ip_origem')
            ->checkXSS('ip_origem');

        return $validator;
    }

    /**
     * Garante, na gravação, que o `usuario_id` existe (o banco não tem FK, ver a migration
     * 20261109100000). Sem esta regra, um id inexistente viraria registro órfão; com ela,
     * vira `Log::error` em `registrar()` e o save de negócio continua.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['usuario_id'], 'Users'), ['errorField' => 'usuario_id']);

        return $rules;
    }
}
