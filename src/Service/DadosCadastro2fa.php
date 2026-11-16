<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

/**
 * Dados para cadastrar o 2FA do usuário no aplicativo autenticador: o QrCode (SVG) e, para quem não
 * conseguir escaneá-lo, a chave e o codinome para o cadastro manual (`element/Users/dadosManuais2fa.php`).
 */
final class DadosCadastro2fa
{
    /**
     * Nome do emissor exibido no aplicativo de 2FA.
     */
    public const EMISSOR = 'KeyAnyWhere';

    /**
     * @param \App\Model\Entity\User $user Usuário dono do 2FA, com o `tfa_secret` gravado.
     * @param int $tamanhoQrCode Tamanho, em pixels, do QrCode.
     * @return array{svgQrCode: string, chave: string, codinome: string}
     */
    public static function doUsuario(User $user, int $tamanhoQrCode): array
    {
        $secret = $user->descripSecret2FA();

        $g2faUrl = (new Google2FA())->getQRCodeUrl(self::EMISSOR, $user->email, $secret);

        $render = new ImageRenderer(
            new RendererStyle($tamanhoQrCode),
            new SvgImageBackEnd(),
        );

        return [
            'svgQrCode' => (new Writer($render))->writeString($g2faUrl),
            // Agrupada de 4 em 4 caracteres para facilitar a digitação; os apps de 2FA ignoram os espaços.
            'chave' => trim(chunk_split($secret, 4, ' ')),
            'codinome' => self::EMISSOR . ' (' . $user->email . ')',
        ];
    }
}
