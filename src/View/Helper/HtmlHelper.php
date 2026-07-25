<?php

namespace App\View\Helper;

use Cake\View\Helper\HtmlHelper as Html;
use App\Application;

/**
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class HtmlHelper extends Html
{
    /**
     * Controla em qual ambiente a aplicação está executando.
     * O default é estar sendo executado em produção.
     * @var bool $ambienteProducao
     */
    private bool $ambienteProducao = true;

    /**
     * Pasta que armazena os arquivos minifcados
     * @var string $caminhoArquivoMinificado
     */
    private string $caminhoArquivoMinificado = ARQ_MINIFICADOS;

    /**
     * Configuração padrões
     * @var array $defaults
     */
    private array $defaults = [
        // Se deve ser verificado em qual ambiente a aplicação está executando.
        'checarAmbiente' => true,
    ];

    public function initialize(array $config): void
    {
        /**
         * Verifica em qual ambiente a aplicação está executando.
         */
        if (Application::isTheExecutionEnvironment(false) !== Application::PRODUCAO) {
            $this->ambienteProducao = false;
        }
    }

    /**
     * Seguir a documentação da classe Cake\View\Helper\HtmlHelper
     *
     * @param array<string>|string $path
     * @param array<string, mixed> $options
     * @return string|null
     */
    public function css(array|string $path, array $options = []): ?string
    {
        $options += $this->defaults;
        if ($this->ambienteProducao && $options['checarAmbiente']) {
            $path = $this->assetMinificado($path);
        }
        unset($options['checarAmbiente']);

        return parent::css($path, $options);
    }

    /**
     * Seguir a documentação da classe Cake\View\Helper\HtmlHelper
     *
     * @param array<string>|string $url
     * @param array<string, mixed> $options
     * @return string|null
     */
    public function script(array|string $url, array $options = []): ?string
    {
        $options += $this->defaults;
        if ($this->ambienteProducao && $options['checarAmbiente']) {
            $url = $this->assetMinificado($url);
        }
        unset($options['checarAmbiente']);

        return parent::script($url, $options);
    }

    /**
     * Altera o caminho do arquivo informado, agora carregando sua versão minificada.
     *
     * @access private
     * @param string $path
     * @return array|string
     */
    private function assetMinificado(array|string $path)
    {
        if (is_array($path)) {
            foreach ($path as $key => $nomeArquivo) {
                $nomeArquivo = explode('.', $nomeArquivo);
                $nomeArquivo = $this->caminhoArquivoMinificado . DS . $nomeArquivo[0] . '.min.' . $nomeArquivo[1];
                $path[$key] = $nomeArquivo;
            }
        } else {
            $nomeArquivo = explode('.', $path);
            $path = $this->caminhoArquivoMinificado . DS . $nomeArquivo[0] . '.min.' . $nomeArquivo[1];
        }

        return $path;
    }
}
