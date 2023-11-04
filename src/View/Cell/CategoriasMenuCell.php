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
    protected $_validCellOptions = [];

    /**
     * Initialization logic run at the end of object construction.
     *
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * Default display method.
     *
     * @return void
     */
    public function display()
    {
        $categorias = $this->fetchTable('Categorias');
        $query = $categorias
            ->find('all')
            ->order(['posicao']);

        $this->set(compact('query'));
    }
}
