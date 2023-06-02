<?php
declare(strict_types=1);

namespace App\Criptografia;

use Throwable;

/**
 * Responsável por todo o processo de criptografia de descriptografia
 *
 */
class Criptografia
{
    public static function criptografar(string $textoPuro): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = sodium_crypto_secretbox(
            $textoPuro,
            $nonce,
            sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
        );

        sodium_memzero($textoPuro);

        return sodium_bin2hex($nonce . $encrypted);
    }

    public static function descriptografar(string $strCriptografada): string
    {
        try {
            $decoded = sodium_hex2bin($strCriptografada);
            sodium_memzero($strCriptografada);

            $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null);
            sodium_memzero($decoded);
        } catch (\Throwable $ex) {
            return 'Erro ao obter as informações para descriptografar: ' . $ex->getMessage();
        }

        try {
            $return = sodium_crypto_secretbox_open(
                $ciphertext,
                $nonce,
                sodium_hex2bin(env('KEY_CRIPTOGRAFIC'))
            );

            sodium_memzero($ciphertext);
            sodium_memzero($nonce);

            if ($return === false) {
                $return = 'Não foi possivel descriptografar.';
            }
        } catch (Throwable $ex) {
            $return = 'Erro ao descriptografar: ' . $ex->getMessage();
        }

        return $return;
    }
}