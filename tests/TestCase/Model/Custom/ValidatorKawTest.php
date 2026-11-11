<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Custom;

use App\Log\Seguranca;
use App\Model\Custom\ValidatorKaw;
use App\Test\TestCase\Log\CapturaLogSegurancaTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * App\Model\Custom\ValidatorKaw Test Case
 *
 * @uses \App\Model\Custom\ValidatorKaw
 */
class ValidatorKawTest extends TestCase
{
    use CapturaLogSegurancaTrait;
    use LocatorAwareTrait;

    protected array $fixtures = [
        'app.Categorias',
        'app.Auditorias',
    ];

    /**
     * Test checkXSS method returns ValidatorKaw instance
     *
     * @return void
     */
    public function testCheckXssReturnsValidatorInstance(): void
    {
        $validator = new ValidatorKaw();
        $result = $validator->checkXSS('test_field');

        $this->assertInstanceOf(ValidatorKaw::class, $result);
    }

    /**
     * Test checkXSS method with custom message
     *
     * @return void
     */
    public function testCheckXssWithCustomMessage(): void
    {
        $validator = new ValidatorKaw();
        $customMessage = 'Custom XSS detection message';
        $result = $validator->checkXSS('test_field', $customMessage);

        $this->assertInstanceOf(ValidatorKaw::class, $result);
    }

    /**
     * Test checkXSS method with when parameter
     *
     * @return void
     */
    public function testCheckXssWithWhenParameter(): void
    {
        $validator = new ValidatorKaw();
        $result = $validator->checkXSS('test_field', 'message', true);

        $this->assertInstanceOf(ValidatorKaw::class, $result);
    }

    /**
     * Test checkXSS fluent interface chaining
     *
     * @return void
     */
    public function testCheckXssFluentInterfaceChaining(): void
    {
        $validator = new ValidatorKaw();
        $result = $validator
            ->checkXSS('field1')
            ->checkXSS('field2')
            ->checkXSS('field3');

        $this->assertInstanceOf(ValidatorKaw::class, $result);
    }

    /**
     * Test ValidatorKaw extends Cake Validator
     *
     * @return void
     */
    public function testValidatorKawExtendsValidator(): void
    {
        $validator = new ValidatorKaw();
        $this->assertInstanceOf(Validator::class, $validator);
    }

    /**
     * Test checkXSS actually rejects a malicious payload.
     *
     * checkXSS's rule callback relies on the 'table' provider CakePHP injects
     * automatically when validating through a Table's newEntity()/patchEntity(),
     * so it needs to be exercised through a real table (Categorias uses
     * checkXSS() on 'nome') rather than a bare Validator::validate() call.
     *
     * @return void
     */
    public function testCheckXssRejectsMaliciousPayload(): void
    {
        $categorias = $this->fetchTable('Categorias');
        $categoria = $categorias->newEntity(['nome' => '<script>alert(1)</script>']);

        $errors = $categoria->getErrors();

        $this->assertArrayHasKey('nome', $errors);
        $this->assertArrayHasKey('checkXSS', $errors['nome']);
    }

    /**
     * Test checkXSS allows clean data through
     *
     * @return void
     */
    public function testCheckXssAllowsCleanData(): void
    {
        $categorias = $this->fetchTable('Categorias');
        $categoria = $categorias->newEntity(['nome' => 'Um nome perfeitamente normal']);

        $errors = $categoria->getErrors();

        $this->assertArrayNotHasKey('nome', $errors);
    }

    /**
     * XSS detectado registra `xss_detectado` (alert) com tabela, campo e ação no contexto; o conteúdo
     * malicioso não vai para o log.
     *
     * @return void
     */
    public function testCheckXssRegistraEventoDeSeguranca(): void
    {
        $this->fetchTable('Categorias')->newEntity(['nome' => '<script>alert(1)</script>']);

        $linha = $this->logSeguranca(Seguranca::XSS_DETECTADO);
        $this->assertSame('alert', $linha['nivel']);
        $this->assertSame('Categorias', $linha['contexto']['tabela']);
        $this->assertSame('nome', $linha['contexto']['campo']);
        $this->assertSame('criacao', $linha['contexto']['acao']);
        $this->assertStringNotContainsString('<script>', json_encode($linha, JSON_THROW_ON_ERROR));
    }

    /**
     * Test mensagemErroPadrao - checkXSS
     *
     * @return void
     */
    public function testMensagemErroPadraoCheckXss(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('checkXSS', ['email']);
        $this->assertStringContainsString('maliciosa', $message);
        $this->assertStringContainsString('XSS', $message);
    }

    /**
     * Test mensagemErroPadrao - requirePresence
     *
     * @return void
     */
    public function testMensagemErroPadraoRequirePresence(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('requirePresence', ['email']);
        $this->assertStringContainsString('email', $message);
        $this->assertStringContainsString('informado', $message);
    }

    /**
     * Test mensagemErroPadrao - notEmptyString
     *
     * @return void
     */
    public function testMensagemErroPadraoNotEmptyString(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('notEmptyString', ['name']);
        $this->assertStringContainsString('name', $message);
        $this->assertStringContainsString('vazio', $message);
    }

    /**
     * Test mensagemErroPadrao - maxLength
     *
     * @return void
     */
    public function testMensagemErroPadraoMaxLength(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('maxLength', ['username', 255]);
        $this->assertStringContainsString('username', $message);
        $this->assertStringContainsString('255', $message);
        $this->assertStringContainsString('máximo', $message);
    }

    /**
     * Test mensagemErroPadrao - minLength
     *
     * @return void
     */
    public function testMensagemErroPadraoMinLength(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('minLength', ['password', 8]);
        $this->assertStringContainsString('password', $message);
        $this->assertStringContainsString('8', $message);
        $this->assertStringContainsString('mínimo', $message);
    }

    /**
     * Test mensagemErroPadrao - integer
     *
     * @return void
     */
    public function testMensagemErroPadraoInteger(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('integer', ['age']);
        $this->assertStringContainsString('age', $message);
        $this->assertStringContainsString('inteiro', $message);
    }

    /**
     * Test mensagemErroPadrao - boolean
     *
     * @return void
     */
    public function testMensagemErroPadraoBoolean(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('boolean', ['active']);
        $this->assertStringContainsString('active', $message);
        $this->assertStringContainsString('booleano', $message);
    }

    /**
     * Test mensagemErroPadrao - email
     *
     * @return void
     */
    public function testMensagemErroPadraoEmail(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('email', ['email']);
        $this->assertStringContainsString('email', $message);
        $this->assertStringContainsString('válido', $message);
    }

    /**
     * Test mensagemErroPadrao - date
     *
     * @return void
     */
    public function testMensagemErroPadraoDate(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('date', ['birth_date']);
        $this->assertStringContainsString('birth_date', $message);
        $this->assertStringContainsString('data', $message);
    }

    /**
     * Test mensagemErroPadrao - datetime
     *
     * @return void
     */
    public function testMensagemErroPadraoDatetime(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('datetime', ['created_at']);
        $this->assertStringContainsString('created_at', $message);
        $this->assertStringContainsString('data', $message);
        $this->assertStringContainsString('hora', $message);
    }

    /**
     * Test mensagemErroPadrao - time
     *
     * @return void
     */
    public function testMensagemErroPadraoTime(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('time', ['event_time']);
        $this->assertStringContainsString('event_time', $message);
        $this->assertStringContainsString('horário', $message);
    }

    /**
     * Test mensagemErroPadrao - decimal
     *
     * @return void
     */
    public function testMensagemErroPadraoDecimal(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('decimal', ['price']);
        $this->assertStringContainsString('price', $message);
        $this->assertStringContainsString('decimal', $message);
    }

    /**
     * Test mensagemErroPadrao - numeric
     *
     * @return void
     */
    public function testMensagemErroPadraoNumeric(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('numeric', ['quantity']);
        $this->assertStringContainsString('quantity', $message);
        $this->assertStringContainsString('numérico', $message);
    }

    /**
     * Test mensagemErroPadrao - url
     *
     * @return void
     */
    public function testMensagemErroPadraoUrl(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('url', ['website']);
        $this->assertStringContainsString('website', $message);
        $this->assertStringContainsString('URL', $message);
    }

    /**
     * Test mensagemErroPadrao - urlWithProtocol
     *
     * @return void
     */
    public function testMensagemErroPadraoUrlWithProtocol(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('urlWithProtocol', ['api_url']);
        $this->assertStringContainsString('api_url', $message);
        $this->assertStringContainsString('protocolo', $message);
    }

    /**
     * Test mensagemErroPadrao - inList
     *
     * @return void
     */
    public function testMensagemErroPadraoInList(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('inList', ['status']);
        $this->assertStringContainsString('status', $message);
        $this->assertStringContainsString('permitido', $message);
    }

    /**
     * Test mensagemErroPadrao - uuid
     *
     * @return void
     */
    public function testMensagemErroPadraoUuid(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('uuid', ['id']);
        $this->assertStringContainsString('id', $message);
        $this->assertStringContainsString('UUID', $message);
    }

    /**
     * Test mensagemErroPadrao - ip
     *
     * @return void
     */
    public function testMensagemErroPadraoIp(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('ip', []);
        $this->assertStringContainsString('IP', $message);
        $this->assertStringContainsString('inválido', $message);
    }

    /**
     * Test mensagemErroPadrao - existsIn (RulesChecker)
     *
     * @return void
     */
    public function testMensagemErroPadraoExistsIn(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('existsIn', ['user_id', 'Users']);
        $this->assertStringContainsString('user_id', $message);
        $this->assertStringContainsString('Users', $message);
        $this->assertStringContainsString('encontrado', $message);
    }

    /**
     * Test mensagemErroPadrao - isUnique (RulesChecker)
     *
     * @return void
     */
    public function testMensagemErroPadraoIsUnique(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('isUnique', ['email']);
        $this->assertStringContainsString('email', $message);
        $this->assertStringContainsString('cadastro', $message);
    }

    /**
     * Test mensagemErroPadrao - Invalid method returns empty string
     *
     * @return void
     */
    public function testMensagemErroPadraoInvalidMethodReturnsEmpty(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('invalidMethod', ['field']);
        $this->assertEquals('', $message);
    }

    /**
     * Test mensagemErroPadrao - Unknown method returns empty string
     *
     * @return void
     */
    public function testMensagemErroPadraoUnknownMethodReturnsEmpty(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('someRandomMethod', ['value']);
        $this->assertEquals('', $message);
    }

    /**
     * Provide various validator methods for testing
     *
     * @return list<array{string}>
     */
    public static function validatorMethodsProvider(): array
    {
        return [
            ['checkXSS'],
            ['requirePresence'],
            ['notEmptyString'],
            ['maxLength'],
            ['minLength'],
            ['integer'],
            ['boolean'],
            ['email'],
            ['date'],
            ['datetime'],
            ['time'],
            ['decimal'],
            ['numeric'],
            ['url'],
            ['urlWithProtocol'],
            ['inList'],
            ['uuid'],
            ['ip'],
            ['existsIn'],
            ['isUnique'],
        ];
    }

    /**
     * Test mensagemErroPadrao - All valid methods return non-empty messages
     *
     * @param string $method
     * @return void
     */
    #[DataProvider('validatorMethodsProvider')]
    public function testAllValidatorMethodsReturnMessages(string $method): void
    {
        $message = ValidatorKaw::mensagemErroPadrao($method, ['field', 'value']);
        $this->assertNotEmpty($message, "Method $method should return a message");
    }

    /**
     * Test mensagemErroPadrao - Multiple replacement values
     *
     * @return void
     */
    public function testMensagemErroPadraoMultipleReplacementValues(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('maxLength', ['username', '100']);
        // Os dois valores substituem os marcadores {0} e {1}.
        $this->assertStringContainsString('username', $message);
        $this->assertStringContainsString('100', $message);
    }

    /**
     * Test mensagemErroPadrao - Empty values array
     *
     * @return void
     */
    public function testMensagemErroPadraoEmptyValuesArray(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('email', []);
        $this->assertNotEmpty($message);
    }

    /**
     * Test mensagemErroPadrao - Case sensitivity
     *
     * @return void
     */
    public function testMensagemErroPadraoIsoCaseSensitive(): void
    {
        $message1 = ValidatorKaw::mensagemErroPadrao('email', []);
        $message2 = ValidatorKaw::mensagemErroPadrao('EMAIL', []);

        // email should work, EMAIL should not
        $this->assertNotEmpty($message1);
        $this->assertEquals('', $message2);
    }

    /**
     * Test mensagemErroPadrao - With special values that need translation
     *
     * @return void
     */
    public function testMensagemErroPadraoWithSpecialValues(): void
    {
        $message = ValidatorKaw::mensagemErroPadrao('existsIn', ['user_id', 'Users']);
        $this->assertNotEmpty($message);
    }
}
