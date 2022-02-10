<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Users seed.
 */
class UsersSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('pt_BR');
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $data[] = [
                'username'      => $faker->userName,
                'email'         => $faker->email,
                'password'      => sha1($faker->password),
                'created'       => date('Y-m-d H:i:s'),
                'modified'       => date('Y-m-d H:i:s'),
            ];
        }

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
