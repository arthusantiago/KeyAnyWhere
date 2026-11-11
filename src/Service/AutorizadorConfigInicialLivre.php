<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\ServerRequest;

/**
 * Implementação padrão: quem acessa primeiro faz a configuração inicial (ela só existe enquanto não há
 * usuário configurado; ver UsersTable::configInicialSistema()).
 */
class AutorizadorConfigInicialLivre implements AutorizadorConfigInicialInterface
{
    /**
     * @param \Cake\Http\ServerRequest $request
     * @return bool
     */
    public function autoriza(ServerRequest $request): bool
    {
        return true;
    }
}
