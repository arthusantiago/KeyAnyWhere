<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Custom;

use App\Model\Custom\ValidatorKaw;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

/**
 * App\Model\Custom\ValidatorKaw Test Case
 *
 * @uses \App\Model\Custom\ValidatorKaw
 */
class ValidatorKawTest extends TestCase
{
    use LocatorAwareTrait;

    protected array $fixtures = [
        'app.Categorias',
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
}
