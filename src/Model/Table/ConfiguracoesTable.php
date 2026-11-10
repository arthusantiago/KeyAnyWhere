<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Auditoria\AuditavelTrait;
use App\I18n\Regional;
use App\Model\Custom\ValidatorKaw;
use App\Model\Entity\Configuracao;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Configuracoes Model
 *
 * @method \App\Model\Entity\Configuracao newEmptyEntity()
 * @method \App\Model\Entity\Configuracao newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Configuracao> newEntities(array<int, array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Configuracao get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Configuracao findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Configuracao patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Configuracao> patchEntities(iterable<\App\Model\Entity\Configuracao> $entities, array<int, array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Configuracao|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Configuracao saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Configuracao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Configuracao>|false saveMany(iterable<\App\Model\Entity\Configuracao> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Configuracao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Configuracao> saveManyOrFail(iterable<\App\Model\Entity\Configuracao> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Configuracao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Configuracao>|false deleteMany(iterable<\App\Model\Entity\Configuracao> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Configuracao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Configuracao> deleteManyOrFail(iterable<\App\Model\Entity\Configuracao> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ConfiguracoesTable extends Table
{
    use AuditavelTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('configuracoes');
        $this->setEntityClass('Configuracao');
        $this->setDisplayField('chave');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Mascara antes/depois: este app guarda segredos (credenciais) e a trilha de
        // auditoria é imutável — guarda só o nome dos campos alterados.
        $this->adicionarAuditoria('configuracao', mascararAlteracoes: true);

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
            ->scalar('chave')
            ->requirePresence('chave', 'create', ValidatorKaw::mensagemErroPadrao('requirePresence', ['chave']))
            ->notEmptyString('chave', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['chave']));

        $validator
            ->scalar('valor')
            ->notEmptyString('valor', ValidatorKaw::mensagemErroPadrao('notEmptyString', ['valor']));

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
                ['chave'],
                ValidatorKaw::mensagemErroPadrao('isUnique', ['chave']),
            ),
            ['errorField' => 'chave'],
        );

        return $rules;
    }

    /**
     * Com base na chave da configuração o método retorna o valor configurado.
     *
     * @access public
     * @param string $chave Exemplo: "backOffice.credencial.url"
     * @return string
     */
    public function getValorConfig(string $chave): string
    {
        $config = $this
            ->find()
            ->select(['valor'])
            ->where(['chave' => $chave])
            ->first();

        return $config instanceof Configuracao ? $config->valor : '';
    }

    /**
     * Grava o valor de uma configuração, criando o registro na primeira vez.
     *
     * @access public
     * @param string $chave Exemplo: Configuracao::REGIONAL_IDIOMA
     * @param string $valor
     * @return \App\Model\Entity\Configuracao|false
     */
    public function salvarValorConfig(string $chave, string $valor): Configuracao|false
    {
        $config = $this->find()->where(['chave' => $chave])->first();
        $config = $config instanceof Configuracao
            ? $this->patchEntity($config, ['valor' => $valor])
            : $this->newEntity(['chave' => $chave, 'valor' => $valor]);

        return $this->save($config);
    }

    /**
     * Validação da tela "Configurações gerais": fuso horário e idioma da instalação.
     *
     * @param \App\Model\Custom\ValidatorKaw $validator Validator instance.
     * @return \App\Model\Custom\ValidatorKaw
     */
    public function validationRegional(Validator $validator): Validator
    {
        $validator
            ->requirePresence(
                'fusoHorario',
                true,
                ValidatorKaw::mensagemErroPadrao('requirePresence', ['Fuso horário']),
            )
            ->inList(
                'fusoHorario',
                Regional::fusosHorarios(),
                ValidatorKaw::mensagemErroPadrao('inList', ['Fuso horário']),
            );

        $validator
            ->requirePresence('idioma', true, ValidatorKaw::mensagemErroPadrao('requirePresence', ['Idioma']))
            ->inList(
                'idioma',
                array_keys(Regional::IDIOMAS),
                ValidatorKaw::mensagemErroPadrao('inList', ['Idioma']),
            );

        return $validator;
    }

    /**
     * Fuso horário e idioma gravados (vazio quando ainda não foram escolhidos). Lido no
     * config/bootstrap.php e aplicado por `App\I18n\Regional::aplicar()`.
     *
     * @access public
     * @return array{fusoHorario?: string, idioma?: string}
     */
    public function preferenciasRegionais(): array
    {
        $chaves = [
            Configuracao::REGIONAL_FUSO_HORARIO => 'fusoHorario',
            Configuracao::REGIONAL_IDIOMA => 'idioma',
        ];

        $preferencias = [];
        $configs = $this->find()
            ->select(['chave', 'valor'])
            ->where(['chave IN' => array_keys($chaves)])
            ->all();
        foreach ($configs as $config) {
            if ($config instanceof Configuracao && $config->valor !== '') {
                $preferencias[$chaves[$config->chave]] = $config->valor;
            }
        }

        return $preferencias;
    }
}
