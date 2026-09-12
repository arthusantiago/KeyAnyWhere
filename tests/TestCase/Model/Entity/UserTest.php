<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use PragmaRX\Google2FA\Google2FA;

/**
 * App\Model\Entity\User Test Case
 *
 * Cobre o bypass de 2FA em ambiente de desenvolvimento (ver App\Application::isTheExecutionEnvironment()).
 */
class UserTest extends TestCase
{
    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        // Restaura o valor usado pelo restante da suíte (config/.env local define DEBUG=true).
        Configure::write('debug', true);

        parent::tearDown();
    }

    /**
     * Em ambiente de desenvolvimento, valida2fa() deve aceitar qualquer código,
     * mesmo um claramente inválido — não deve chegar a chamar o Google2FA de verdade.
     *
     * @return void
     */
    public function testValida2faAceitaQualquerCodigoEmDesenvolvimento(): void
    {
        Configure::write('debug', true);

        $user = new User();
        $user->tfa_secret = (new Google2FA())->generateSecretKey();

        $this->assertTrue($user->valida2fa('000000'));
    }

    /**
     * Fora do ambiente de desenvolvimento, valida2fa() deve validar o código de verdade:
     * aceitar o código correto...
     *
     * @return void
     */
    public function testValida2faAceitaCodigoCorretoForaDeDesenvolvimento(): void
    {
        Configure::write('debug', false);

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user = new User();
        $user->tfa_secret = $secret;

        $this->assertTrue($user->valida2fa($google2fa->getCurrentOtp($secret)));
    }

    /**
     * ...e rejeitar um código incorreto.
     *
     * @return void
     */
    public function testValida2faRejeitaCodigoIncorretoForaDeDesenvolvimento(): void
    {
        Configure::write('debug', false);

        $user = new User();
        $user->tfa_secret = (new Google2FA())->generateSecretKey();

        $this->assertFalse($user->valida2fa('000000'));
    }
}
