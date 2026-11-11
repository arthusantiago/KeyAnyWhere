<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Custom\ValidatorKaw;
use App\Model\Entity\EventoSeguranca;
use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Eventos de segurança mostrados aos usuários. Só INSERT (App\Log\Engine\EventosSegurancaLog) e
 * SELECT: sem edição nem exclusão pela aplicação.
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @method \App\Model\Entity\EventoSeguranca newEmptyEntity()
 * @method \App\Model\Entity\EventoSeguranca newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EventoSeguranca|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class EventosSegurancaTable extends Table
{
    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('eventos_seguranca');
        $this->setEntityClass(EventoSeguranca::class);
        $this->setDisplayField('evento');
        $this->setPrimaryKey('id');

        // `created` preenchido no PHP (fuso do app), não por DEFAULT do banco (sessão em UTC).
        $this->addBehavior('Timestamp', ['events' => ['Model.beforeSave' => ['created' => 'new']]]);

        $this->belongsTo('Users', ['foreignKey' => 'usuario_id']);

        $this->_validatorClass = ValidatorKaw::class;
    }

    /**
     * Sem checkXSS de propósito: estes campos não vêm do formulário, e um XSS detectado aqui gravaria
     * outro evento de segurança.
     *
     * @param \App\Model\Custom\ValidatorKaw $validator Validator instance.
     * @return \App\Model\Custom\ValidatorKaw
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('evento')
            ->maxLength('evento', 50, ValidatorKaw::mensagemErroPadrao('maxLength', ['Evento', 50]))
            ->requirePresence('evento', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Evento']))
            ->notEmptyString('evento', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['Evento']));

        $validator
            ->scalar('nivel')
            ->maxLength('nivel', 10, ValidatorKaw::mensagemErroPadrao('maxLength', ['Nível', 10]))
            ->requirePresence('nivel', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['Nível']))
            ->notEmptyString('nivel', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['Nível']));

        $validator
            ->integer('usuario_id', ValidatorKaw::mensagemErroPadrao('integer', ['Usuário']))
            ->allowEmptyString('usuario_id');

        $validator
            ->scalar('ip')
            ->maxLength('ip', 45, ValidatorKaw::mensagemErroPadrao('maxLength', ['IP', 45]))
            ->allowEmptyString('ip');

        return $validator;
    }

    /**
     * Sem chave estrangeira no banco, como na auditoria: excluir um usuário não apaga os eventos dele.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(
            $rules->existsIn(
                ['usuario_id'],
                'Users',
                ValidatorKaw::mensagemErroPadrao('existsIn', ['usuario_id', 'Users']),
            ),
            ['errorField' => 'usuario_id'],
        );

        return $rules;
    }

    /**
     * Grava um evento. Nunca lança exceção: devolve false se não gravou.
     *
     * @param string $evento
     * @param string $nivel
     * @param int|null $usuarioId
     * @param string|null $ip
     * @return bool
     */
    public function registrar(string $evento, string $nivel, ?int $usuarioId, ?string $ip): bool
    {
        $registro = $this->newEntity([
            'evento' => $evento,
            'nivel' => $nivel,
            'usuario_id' => $usuarioId,
            'ip' => $ip,
        ]);

        return $this->save($registro, ['atomic' => false]) !== false;
    }

    /**
     * Os eventos que o usuário pode ver: o root vê todos; os demais, só os da própria conta.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\EventoSeguranca> $query
     * @param \App\Model\Entity\User $usuario
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\EventoSeguranca>
     */
    public function findVisiveisPara(SelectQuery $query, User $usuario): SelectQuery
    {
        if (!$usuario->root) {
            $query->where(['EventosSeguranca.usuario_id' => $usuario->id]);
        }

        return $query
            ->contain(['Users' => ['fields' => ['id', 'username']]])
            ->orderByDesc('EventosSeguranca.created')
            ->orderByDesc('EventosSeguranca.id');
    }
}
