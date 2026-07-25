<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

/**
 * CategoriasMenu cell
 */
class CategoriasMenuCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array
     */
    protected array $_validCellOptions = [];

    /**
     * Initialization logic run at the end of object construction.
     *
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * Recupera as categorias utilizadas no menu da versão de desktop
     *
     * @return void
     */
    public function desktop(): void
    {
        $query = $this->queryCategorias();
        $this->set(compact('query'));
    }

    /**
     * Recupera as categorias utilizadas no menu da versão responsiva do site
     *
     * @return void
     */
    public function responsivo(): void
    {
        $query = $this->queryCategorias();
        $this->set(compact('query'));
    }

    private function queryCategorias()
    {
        return $this
            ->fetchTable('Categorias')
            ->find('all')
            ->orderBy(['posicao']);
    }
}
