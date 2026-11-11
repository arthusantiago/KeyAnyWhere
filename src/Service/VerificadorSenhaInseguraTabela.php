<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\InsecurePasswordsTable;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Implementação padrão: procura a senha (em minúsculas) na tabela `insecure_passwords`, populada pelos
 * seeds `PasswordsInsecurePt*Seed`.
 */
class VerificadorSenhaInseguraTabela implements VerificadorSenhaInseguraInterface
{
    use LocatorAwareTrait;

    /**
     * @param string $senha
     * @return bool
     */
    public function estaComprometida(string $senha): bool
    {
        return $this->fetchTable(InsecurePasswordsTable::class)
            ->exists(['password' => strtolower($senha)]);
    }
}
