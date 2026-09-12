<?php
declare(strict_types=1);

namespace App\Test\Fixture;

/**
 * InsecurePasswordsFixture
 */
class InsecurePasswordsFixture extends AppFixture
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
                'password' => '123456',
                'created' => 1688597054,
                'modified' => 1688597054,
            ],
        ];
        parent::init();
    }
}
