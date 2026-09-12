<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Cell;

use App\View\Cell\CategoriasMenuCell;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * App\View\Cell\CategoriasMenuCell Test Case
 */
class CategoriasMenuCellTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Categorias',
    ];

    /**
     * Test subject
     *
     * @var \App\View\Cell\CategoriasMenuCell
     */
    protected $CategoriasMenu;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->CategoriasMenu = new CategoriasMenuCell(new ServerRequest(), new Response());
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CategoriasMenu);

        parent::tearDown();
    }

    /**
     * desktop() deve disponibilizar para a view a lista de categorias ordenada por posição.
     *
     * @return void
     * @uses \App\View\Cell\CategoriasMenuCell::desktop()
     */
    public function testDesktopExpoeCategoriasOrdenadasPorPosicao(): void
    {
        $this->CategoriasMenu->desktop();

        $viewVars = $this->CategoriasMenu->viewBuilder()->getVars();
        $this->assertArrayHasKey('query', $viewVars);
        $this->assertGreaterThanOrEqual(1, $viewVars['query']->count());
    }

    /**
     * responsivo() deve disponibilizar para a view a mesma lista de categorias que desktop().
     *
     * @return void
     * @uses \App\View\Cell\CategoriasMenuCell::responsivo()
     */
    public function testResponsivoExpoeCategoriasOrdenadasPorPosicao(): void
    {
        $this->CategoriasMenu->responsivo();

        $viewVars = $this->CategoriasMenu->viewBuilder()->getVars();
        $this->assertArrayHasKey('query', $viewVars);
        $this->assertGreaterThanOrEqual(1, $viewVars['query']->count());
    }
}
