<?php
declare(strict_types=1);

namespace App\Auditoria;

/**
 * Contrato da Table que persiste os registros de auditoria (`AuditoriasTable`).
 * É o que o AuditavelTrait chama no callback do behavior.
 *
 * Implementação pronta: RegistraAuditoriaTrait.
 */
interface RegistradorAuditoriaInterface
{
    /**
     * Persiste o registro. Nunca lança exceção — falha de auditoria não pode desfazer o
     * save de negócio ao qual está ligada.
     *
     * @param \App\Auditoria\RegistroAuditoria $registro Registro montado pelo behavior.
     * @param bool $mascararAlteracoes Troca antes/depois por '' mantendo só os nomes dos campos.
     * @return bool
     */
    public function registrar(RegistroAuditoria $registro, bool $mascararAlteracoes = false): bool;
}
