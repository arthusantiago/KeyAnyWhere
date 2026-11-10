<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * Isola os testes das configurações `Kaw.*` (pontos de extensão: menu, login, tela regional) que os
 * plugins da instalação gravam no bootstrap: cada teste começa com os valores padrão do core gravados
 * explicitamente e, no fim, a configuração da instalação volta.
 *
 * Os valores ficam gravados (e não apagados) porque cada requisição de teste refaz o bootstrap dos
 * plugins: um plugin não deve sobrescrever uma chave `Kaw.*` que já está definida.
 */
trait ConfiguracaoKawIsoladaTrait
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $configuracaoKawDaInstalacao = null;

    /**
     * @return void
     */
    #[Before]
    public function isolarConfiguracaoKaw(): void
    {
        $this->configuracaoKawDaInstalacao = (array)Configure::read('Kaw', []);
        Configure::write('Kaw', [
            'Menu' => ['configuracoes' => [], 'outros' => []],
            'Login' => ['elementos' => []],
            'Configuracoes' => ['regionalEditavel' => true],
        ]);
    }

    /**
     * @return void
     */
    #[After]
    public function restaurarConfiguracaoKaw(): void
    {
        Configure::delete('Kaw');
        if ($this->configuracaoKawDaInstalacao) {
            Configure::write('Kaw', $this->configuracaoKawDaInstalacao);
        }
    }
}
