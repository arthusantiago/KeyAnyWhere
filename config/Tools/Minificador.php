<?php

namespace Config\Tools;

use Composer\Script\Event;
use MatthiasMullie\Minify\CSS as MinifyCSS;
use MatthiasMullie\Minify\JS as MinifyJS;

class Minificador {
    private static $pathJsOrigem = '/webroot/js/';
    private static $pathJsMinified = '/webroot/js/minified/';

    private static $pathCssOrigem = '/webroot/css/';
    private static $pathCssMinified = '/webroot/css/minified/';

    static public function executar(Event $event)
    {
        $raizProjeto = dirname(__DIR__, 2);

        self::minificarCSS(
            $raizProjeto . self::$pathCssOrigem,
            $raizProjeto . self::$pathCssMinified,
        );

        self::minificarJS(
            $raizProjeto . self::$pathJsOrigem,
            $raizProjeto . self::$pathJsMinified
        );
    }

    /**
     * Executa a minificação dos arquivos .css .
     * Para cada arquivo, é criado um novo com o conteúdo minificado, com mesmo nome mas com extensão diferente.
     *
     * @access	static public
     * @param	string	$pathCssOrigem Pasta que contem os arquivos que serão minificados
     * @param	string	$pathCssMinified Pasta que armazena os arquivos que já foram minificados
     * @return	void
     */
    static public function minificarCSS(string $pathCssOrigem, string $pathCssMinified): void
    {
        $filesCss = self::somenteArquivos($pathCssOrigem, 'css');
        self::excluiArquivoComMesmoNome($filesCss, $pathCssMinified);

        foreach ($filesCss as $cssItem) {
            (new MinifyCSS())
                ->add($pathCssOrigem . $cssItem)
                ->minify($pathCssMinified . strstr($cssItem, '.', true) . '.min.css' );
        }
    }

    /**
     * Executa a minificação dos arquivos .js.
     * Para cada arquivo, é criado um novo com o conteúdo minificado, com mesmo nome mas com extensão diferente.
     *
     * @access	static public
     * @param	string	$pathJsOrigem Pasta que armazena os arquivos que serão minificados
     * @param	string	$pathJsMinified	Pasta que irá armazenar os arquivos minificados
     * @return	void
     */
    static public function minificarJS(string $pathJsOrigem, string $pathJsMinified): void
    {
        $arquivosJs = self::somenteArquivos($pathJsOrigem, 'js');
        self::excluiArquivoComMesmoNome($arquivosJs, $pathJsMinified);

        foreach ($arquivosJs as $jsItem) {
            (new MinifyJS())
                ->add($pathJsOrigem . $jsItem)
                ->minify($pathJsMinified . strstr($jsItem, '.', true) . '.min.js' );
        }
    }

    /**
     * Recebe uma pasta e devolve a listagem dos arquivos contidos nela.
     *
     * @access static public
     * @param string $caminhoPasta Caminho absoluta da pasta que será escaneada
     * @param string $somenteExtensao Opcional: Somente arquivos com essa extensão serão retornado
     * @return mixed
     */
    static public function somenteArquivos(string $caminhoPasta, string $somenteExtensao = ''): array
    {
        $arquivos = scandir($caminhoPasta);

        foreach ($arquivos as $index => $arquivo) {
            $dirFile = $caminhoPasta . $arquivo;

            if (!is_file($dirFile)) {
                $arquivos[$index] = null;
                continue;
            }

            if (
                !empty($somenteExtensao)
                && pathinfo($dirFile)['extension'] !== $somenteExtensao
            ) {
                $arquivos[$index] = null;
            }
        }

        return array_filter($arquivos);
    }


    /**
     * Procura em uma pasta por arquivos que tenham o mesmo nome dos arquivos de origem.
     * Se for encontrado o arquivo é excluído.
     *
     * @param array $arquivosOrigem Lista dos nomes dos arquivos que serão buscados
     * @param string $pastaQueSeraEscaneada	Caminho absoluto da pasta que será escaneada
     * @return void
     */
    static public function excluiArquivoComMesmoNome(array $arquivosOrigem, string $pastaQueSeraEscaneada)
    {
        $arquivosMinificados = self::somenteArquivos($pastaQueSeraEscaneada);

        foreach ($arquivosOrigem as $arquivoOrigem) {
            foreach ($arquivosMinificados as $arquivoMinificado) {
                if (strstr($arquivoOrigem, '.', true) == strstr($arquivoMinificado, '.', true)) {
                    self::excluiArquivo($pastaQueSeraEscaneada . $arquivoMinificado);
                }
            }
        }
    }

    /**
     * Exclui o arquivo passando por parâmetro.
     *
     * @access static public
     * @param string $caminhoArquivo
     * @return boolean Retorna TRUE quando o arquivo foi excluído e FALSE caso contrario.
     */
    static public function excluiArquivo(string $caminhoArquivo): bool
    {
        if (is_file($caminhoArquivo)) {
            return unlink($caminhoArquivo);
        }
        return false;
    }
}
