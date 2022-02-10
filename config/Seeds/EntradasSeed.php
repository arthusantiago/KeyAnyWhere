<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Entradas seed.
 */
class EntradasSeed extends AbstractSeed
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
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 50; $i++) {
            $data[] = [
                'titulo'      => $faker->userName,
                'username'      => $faker->email,
                'password'      => sha1($faker->password),
                'link'          => $faker->domainName,
                'anotacoes'      => '',
                'categoria_id'      => rand(1, 12),
                'subcategoria_id' => rand(0, 1) == 1 ? rand(2, 7) : null,
                'user_id'         => rand(1, 5),
                'created'       => date('Y-m-d H:i:s'),
                'modified'       => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('entradas');
        $table->insert($data)->save();
    }
}
