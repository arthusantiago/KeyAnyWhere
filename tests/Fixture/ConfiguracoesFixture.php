<?php
declare(strict_types=1);

namespace App\Test\Fixture;

/**
 * ConfiguracoesFixture
 */
class ConfiguracoesFixture extends AppFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'chave' => 'Lorem ipsum dolor sit amet',
                'valor' => 'Lorem ipsum dolor sit amet',
                'created' => 1756417165,
                'modified' => 1756417165,
            ],
        ];
        parent::init();
    }
}
