<?php

namespace Config\Tools;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;
/**
 * Trabalha os arquivos que precisam estar corretamente armazenados na pasta webroot/
 *
 * @global
 */
class PreparaAssets
{
    /* Boostrap Icons */
    private static $cssOrigemBootstrapIcons = 'vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css';
    private static $cssDestinoBootstrapIcons = 'webroot/css/minified/bootstrap-icons.min.css';

    /* Boostrap */
    private static $vendorBootstrap = 'vendor/twbs/bootstrap/dist/';
    private static $pathDestinoJS = 'webroot/js/minified/';
    private static $pathDestinoCSS = 'webroot/css/minified/';
    private static $fileDestinoBootstrapCSS = [
        'bootstrap.min.css',
        'bootstrap.min.css.map'
    ];
    private static $fileDestinoBootstrapJS = [
        'bootstrap.min.js',
        'bootstrap.min.js.map'
    ];

    /**
     * Recebe o evento de que um pacote foi atualizado
     *
     * @access	static public
     * @param	Composer\Installer\PackageEvent $event
     * @return	void
     */
    public static function postPackageUpdate(PackageEvent $event)
    {
        $nomePacote = $event->getOperation()->getTargetPackage()->getName();
        self::executar($nomePacote);
    }

    /**
     * Recebe o evento de que um pacote foi instalado
     *
     * @access	static public
     * @param	Composer\Installer\PackageEvent $event
     * @return	void
     */
    public static function postPackageInstall(PackageEvent $event)
    {
        $nomePacote = $event->getOperation()->getPackage()->getName();
        self::executar($nomePacote);
    }

    /**
     * Copia os arquivos CSS e JS do pacote instalado/atualizado para devida pasta dentro do webroot/
     *
     * @access static public
     * @param string $nomePacote
     * @return	void
     */
    static public function executar(string $nomePacote)
    {
        if ($nomePacote == 'twbs/bootstrap-icons') {
            self::excluiArquivo(self::$cssDestinoBootstrapIcons);
            self::copiarArquivo(self::$cssOrigemBootstrapIcons, self::$cssDestinoBootstrapIcons);
        }

        if ($nomePacote == 'twbs/bootstrap') {
            /* Excluir arquivos */
            self::excluirMultiplosArquivo(self::$pathDestinoCSS, self::$fileDestinoBootstrapCSS);
            self::excluirMultiplosArquivo(self::$pathDestinoJS, self::$fileDestinoBootstrapJS);

            /* Copiar arquivos */
            self::copiarMultiplosArquivo(
                self::$vendorBootstrap . 'css/',
                self::$pathDestinoCSS,
                self::$fileDestinoBootstrapCSS
            );

            self::copiarMultiplosArquivo(
                self::$vendorBootstrap . 'js/',
                self::$pathDestinoJS,
                self::$fileDestinoBootstrapJS
            );
        }
    }

    /**
     * Copia um único arquivo de um lugar para outro
     *
     * @param string $arquivoOrigem
     * @param string $arquivoDestino
     * @return boolean
     */
    static public function copiarArquivo(string $arquivoOrigem, string $arquivoDestino): bool
    {
        if (is_file($arquivoOrigem)) {
            return copy($arquivoOrigem, $arquivoDestino);
        }
        return false;
    }

    /**
     * Copia vários arquivos de um lugar para outro
     *
     * @param string $pathOrigem
     * @param string $pathDestino
     * @param array $arquivos Nomes dos arquivos
     * @return void
     */
    static public function copiarMultiplosArquivo(string $pathOrigem, string $pathDestino, array $arquivos): void
    {
        foreach ($arquivos as $arquivo) {
            $pathCompletoOrigem = $pathOrigem . $arquivo;
            $pathCompletoDestino = $pathDestino . $arquivo;
            self::copiarArquivo($pathCompletoOrigem, $pathCompletoDestino);
        }
    }

    /**
     * Exclui um arquivo
     *
     * @param string $arquivo Caminho completo para o arquivo
     * @return boolean Retorna TRUE se o arquivo foi excluído e FALSE caso contrario.
     */
    static public function excluiArquivo(string $arquivo): bool
    {
        if (is_file($arquivo)) {
            return unlink($arquivo);
        }
        return false;
    }

    /**
     * Exclui vários arquivos
     *
     * @param string $pathArquivos Pasta de onde os arquivos serão excluídos
     * @param array $arquivos
     */
    static public function excluirMultiplosArquivo(string $pathArquivos, array $arquivos): void
    {
        foreach ($arquivos as $arquivo) {
            self::excluiArquivo($pathArquivos . $arquivo);
        }
    }
}
