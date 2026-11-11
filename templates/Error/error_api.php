<?php
/**
 * Erro numa rota de API (`/api/...`, ver ErrorController::beforeRender()): o corpo é JSON com a
 * mensagem do erro, sem layout.
 *
 * @var \App\View\AppView $this
 * @var string $message
 */
echo json_encode(['mensagem' => $message]);
