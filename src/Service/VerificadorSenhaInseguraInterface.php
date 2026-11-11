<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Diz se uma senha está em vazamentos conhecidos (aviso ao cadastrar uma entrada).
 *
 * Registrada no container de dependências (Application::services()); uma instalação pode trocar a
 * implementação no services() de um plugin (ex.: consultar um serviço externo em vez da tabela local).
 */
interface VerificadorSenhaInseguraInterface
{
    /**
     * @param string $senha
     * @return bool
     */
    public function estaComprometida(string $senha): bool;
}
