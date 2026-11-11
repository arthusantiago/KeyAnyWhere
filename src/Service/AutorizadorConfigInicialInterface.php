<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\ServerRequest;

/**
 * Decide se a requisição pode abrir a configuração inicial (criação do primeiro usuário e do 2FA),
 * que é acessível sem login enquanto o sistema não está configurado.
 *
 * Registrada no container de dependências (Application::services()); uma instalação pode trocar a
 * implementação no services() de um plugin (ex.: exigir um token de primeiro acesso).
 */
interface AutorizadorConfigInicialInterface
{
    /**
     * @param \Cake\Http\ServerRequest $request
     * @return bool
     */
    public function autoriza(ServerRequest $request): bool;
}
