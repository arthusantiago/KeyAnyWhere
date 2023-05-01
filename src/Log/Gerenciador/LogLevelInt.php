<?php

namespace App\Log\Gerenciador;

use Psr\Log\LogLevel;
use Cake\Core\Exception\CakeException;

class LogLevelInt
{
    /*
    * Representação numerica de acordo com a RFC 5424
    * @see https://datatracker.ietf.org/doc/html/rfc5424#section-6.2.1
    */
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    const ARRAY_LOG_LEVEL = [
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
     * @access	public static
     * @param	string	$level
     * @return	int
     */
    public static function strLevelToNumeric(string $level): int
    {
        $codigoNumerico = array_search(strtolower($level), self::ARRAY_LOG_LEVEL) ?: null;

        if ($codigoNumerico === null) {
            throw new CakeException('O Severity Level informado não está dentro das definições da RFC 5424');
        }

        return $codigoNumerico;
    }
}
