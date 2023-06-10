<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Query;
use Cake\Validation\Validator;

/**
 * Logs Model
 *
 * @method \App\Model\Entity\Log newEmptyEntity()
 * @method \App\Model\Entity\Log newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Log[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Log get($primaryKey, $options = [])
 * @method \App\Model\Entity\Log findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Log patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Log[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Log|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Log saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Log[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Log[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Log[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Log[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LogsTable extends Table
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

        $this->setTable('logs');
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
            ->scalar('evento')
            ->maxLength('evento', 7, 'O identificador do evento pode ter no máximo 7 caracteres')
            ->allowEmptyString('evento');

        $validator
            ->integer('nivel_severidade')
            ->requirePresence('nivel_severidade', 'create', 'O nivel de severidade precisa ser informado')
            ->notEmptyString('nivel_severidade', 'O Nivel de Severidade precisa ser preencido');

        $validator
            ->scalar('recurso')
            ->maxLength('recurso', 100, 'O recurso pode ter no máximo 100 caracteres')
            ->allowEmptyString('recurso');

        $validator
            ->ip('ip_origem', 'O IP informado é inválido')
            ->maxLength('ip_origem', 39, 'O IP de Origem pode ter no máximo 39 caracteres')
            ->allowEmptyString('ip_origem');

        $validator
            ->scalar('usuario')
            ->maxLength('usuario', 200, 'O usuário pode ter no máximo 200 caracteres')
            ->allowEmptyString('usuario');

        $validator
            ->scalar('mensagem')
            ->maxLength('mensagem', 256, 'A mensagem pode ter no máximo 256 caracteres')
            ->requirePresence('mensagem', 'create', 'A Mensagem precisa ser preencida')
            ->notEmptyString('mensagem', 'A Mensagem precisa ser preencida');

        return $validator;
    }

    public function findCountAtividadesSuspeitas(Query $query, array $options)
    {
        $query
            ->select([
                'nivel_severidade',
                'quantidade' => $query->func()->count('nivel_severidade')
            ])
            ->where([
                'analisado' => false,
                'nivel_severidade NOT IN (6, 7)'
            ])
            ->group(['nivel_severidade']);

        return $query;
    }
}
