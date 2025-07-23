<?php
declare(strict_types=1);

namespace App\Log;

use Cake\Core\Exception\CakeException;
use Psr\Log\LogLevel;

class LogLevelInt
{
    /*
    * Representação numérica de acordo com a RFC 5424
    * @see https://datatracker.ietf.org/doc/html/rfc5424#section-6.2.1
    */
    public const EMERGENCY = 0;
    public const ALERT     = 1;
    public const CRITICAL  = 2;
    public const ERROR     = 3;
    public const WARNING   = 4;
    public const NOTICE    = 5;
    public const INFO      = 6;
    public const DEBUG     = 7;

    public const ARRAY_LOG_LEVEL = [
        self::EMERGENCY => LogLevel::EMERGENCY,
        self::ALERT     => LogLevel::ALERT,
        self::CRITICAL  => LogLevel::CRITICAL,
        self::ERROR     => LogLevel::ERROR,
        self::WARNING   => LogLevel::WARNING,
        self::NOTICE    => LogLevel::NOTICE,
        self::INFO      => LogLevel::INFO,
        self::DEBUG     => LogLevel::DEBUG,
    ];

    /**
     * Faz um mapeamento de chave => valor da string do Severity Level com o seu codigo numerico
     *
     * @access public static
     * @param string  $level
     * @return int
     */
    public static function toNumeric(string $level): int
    {
        $codigoNumerico = array_search(strtolower($level), self::ARRAY_LOG_LEVEL) ?: null;

        if ($codigoNumerico === null) {
            throw new CakeException('O Severity Level informado não está dentro das definições da RFC 5424');
        }

        return $codigoNumerico;
    }

    /**
     * Retorna a string do Nivel de severidade informado.
     *
     * @access public static
     * @param int $level
     * @return string
     */
    public static function toString(int $level): string
    {
        $string = self::ARRAY_LOG_LEVEL[$level] ?: null;

        if ($string === null) {
            throw new CakeException('O Severity Level informado não está dentro das definições da RFC 5424');
        }

        return $string;
    }
}
