<?php
declare(strict_types=1);

namespace App\Auditoria;

/**
 * DTO que representa um registro de auditoria antes de ser persistido.
 * Imutável de propósito — quem monta é o AuditoriaBehavior, quem persiste é
 * o callback injetado na config do behavior (ver AuditavelTrait).
 *
 * `usuarioId` null = ação sem usuário autenticado (ex.: criação do primeiro
 * usuário em UsersController::configInicial()).
 */
class RegistroAuditoria
{
    /**
     * @param string $contexto Tipo da entidade (ex.: 'entrada').
     * @param int $idEntidade Chave primária da entidade.
     * @param string $acao 'criacao', 'edicao', 'exclusao' ou uma ação de negócio.
     * @param int|null $usuarioId Usuário autenticado; null sem login.
     * @param array<string, array{antes: mixed, depois: mixed}> $alteracoes
     * @param string|null $ipOrigem IP de quem fez a ação.
     */
    public function __construct(
        private string $contexto,
        private int $idEntidade,
        private string $acao,
        private ?int $usuarioId,
        private array $alteracoes,
        private ?string $ipOrigem = null,
    ) {
    }

    /**
     * @return string
     */
    public function getContexto(): string
    {
        return $this->contexto;
    }

    /**
     * @return int
     */
    public function getIdEntidade(): int
    {
        return $this->idEntidade;
    }

    /**
     * @return string
     */
    public function getAcao(): string
    {
        return $this->acao;
    }

    /**
     * @return int|null
     */
    public function getUsuarioId(): ?int
    {
        return $this->usuarioId;
    }

    /**
     * @return array<string, array{antes: mixed, depois: mixed}>
     */
    public function getAlteracoes(): array
    {
        return $this->alteracoes;
    }

    /**
     * @return string|null
     */
    public function getIpOrigem(): ?string
    {
        return $this->ipOrigem;
    }

    /**
     * Formato pronto para `$table->newEntity($registro->toArray())`, casando com as
     * colunas da tabela `auditorias`.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contexto' => $this->contexto,
            'id_entidade' => $this->idEntidade,
            'acao' => $this->acao,
            'usuario_id' => $this->usuarioId,
            'alteracoes' => $this->alteracoes,
            'ip_origem' => $this->ipOrigem,
        ];
    }
}
