<?php
declare(strict_types=1);

namespace App\Test\TestCase\Criptografia;

use App\Criptografia\Criptografia;
use Cake\TestSuite\TestCase;

/**
 * App\Criptografia\Criptografia Test Case
 *
 * @uses \App\Criptografia\Criptografia
 */
class CriptografiaTest extends TestCase
{
    /**
     * Test criptografar - Basic encryption with simple text
     *
     * @return void
     */
    public function testCriptografarSimpleText(): void
    {
        $plainText = 'Hello, World!';
        $encrypted = Criptografia::criptografar($plainText);

        // Encrypted text should not be empty
        $this->assertNotEmpty($encrypted);

        // Encrypted text should be a string
        $this->assertIsString($encrypted);

        // Encrypted text should be different from plain text
        $this->assertNotEquals($plainText, $encrypted);

        // Encrypted text should be hex encoded (contains only hex characters)
        $this->assertTrue(ctype_xdigit($encrypted), 'Encrypted text should be hex encoded');
    }

    /**
     * Test criptografar - Encryption with empty string
     *
     * @return void
     */
    public function testCriptografarEmptyString(): void
    {
        $plainText = '';
        $encrypted = Criptografia::criptografar($plainText);

        // Empty string should still be encrypted
        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);
        $this->assertTrue(ctype_xdigit($encrypted));
    }

    /**
     * Test criptografar - Encryption with special characters
     *
     * @return void
     */
    public function testCriptografarSpecialCharacters(): void
    {
        $plainText = '!@#$%^&*()_+-=[]{}|;:,.<>?/~`';
        $encrypted = Criptografia::criptografar($plainText);

        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);
        $this->assertNotEquals($plainText, $encrypted);
        $this->assertTrue(ctype_xdigit($encrypted));
    }

    /**
     * Test criptografar - Encryption with unicode characters
     *
     * @return void
     */
    public function testCriptografarUnicodeCharacters(): void
    {
        $plainText = 'Héllo Wørld! 中文 テスト';
        $encrypted = Criptografia::criptografar($plainText);

        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);
        $this->assertNotEquals($plainText, $encrypted);
        $this->assertTrue(ctype_xdigit($encrypted));
    }

    /**
     * Test criptografar - Encryption with large text
     *
     * @return void
     */
    public function testCriptografarLargeText(): void
    {
        $plainText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 100);
        $encrypted = Criptografia::criptografar($plainText);

        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);
        $this->assertNotEquals($plainText, $encrypted);
        $this->assertTrue(ctype_xdigit($encrypted));
    }

    /**
     * Test criptografar - Same plaintext produces different ciphertext (due to random nonce)
     *
     * @return void
     */
    public function testCriptografarRandomNonce(): void
    {
        $plainText = 'Same text';
        $encrypted1 = Criptografia::criptografar($plainText);
        $encrypted2 = Criptografia::criptografar($plainText);

        // Different encryptions should produce different ciphertext (due to random nonce)
        $this->assertNotEquals($encrypted1, $encrypted2);
    }

    /**
     * Test descriptografar - Basic decryption
     *
     * @return void
     */
    public function testDescriptografarSimpleText(): void
    {
        $plainText = 'Hello, World!';
        $encrypted = Criptografia::criptografar($plainText);
        $decrypted = Criptografia::descriptografar($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    /**
     * Test descriptografar - Decryption with empty string
     *
     * @return void
     */
    public function testDescriptografarEmptyString(): void
    {
        $plainText = '';
        $encrypted = Criptografia::criptografar($plainText);
        $decrypted = Criptografia::descriptografar($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    /**
     * Test descriptografar - Decryption with special characters
     *
     * @return void
     */
    public function testDescriptografarSpecialCharacters(): void
    {
        $plainText = '!@#$%^&*()_+-=[]{}|;:,.<>?/~`';
        $encrypted = Criptografia::criptografar($plainText);
        $decrypted = Criptografia::descriptografar($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    /**
     * Test descriptografar - Decryption with unicode characters
     *
     * @return void
     */
    public function testDescriptografarUnicodeCharacters(): void
    {
        $plainText = 'Héllo Wørld! 中文 テスト';
        $encrypted = Criptografia::criptografar($plainText);
        $decrypted = Criptografia::descriptografar($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    /**
     * Test descriptografar - Decryption with large text
     *
     * @return void
     */
    public function testDescriptografarLargeText(): void
    {
        $plainText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 100);
        $encrypted = Criptografia::criptografar($plainText);
        $decrypted = Criptografia::descriptografar($encrypted);

        $this->assertEquals($plainText, $decrypted);
    }

    /**
     * Test descriptografar - Invalid hex string returns error message
     *
     * @return void
     */
    public function testDescriptografarInvalidHexString(): void
    {
        $invalidHex = 'not_a_valid_hex_string!@#$';
        $decrypted = Criptografia::descriptografar($invalidHex);

        $this->assertIsString($decrypted);
        $this->assertStringContainsString('Erro ao obter as informações', $decrypted);
    }

    /**
     * Test descriptografar - Tampered ciphertext returns error message
     *
     * @return void
     */
    public function testDescriptografarTamperedCiphertext(): void
    {
        $plainText = 'Original text';
        $encrypted = Criptografia::criptografar($plainText);

        // Tamper with the encrypted text by changing some hex characters
        $tamperedHex = substr($encrypted, 0, -10) . '0000000000';
        $decrypted = Criptografia::descriptografar($tamperedHex);

        $this->assertIsString($decrypted);
        $this->assertStringContainsString('Não foi possivel descriptografar', $decrypted);
    }

    /**
     * Test descriptografar - Empty encrypted string returns error message
     *
     * @return void
     */
    public function testDescriptografarEmptyEncryptedString(): void
    {
        $decrypted = Criptografia::descriptografar('');

        $this->assertIsString($decrypted);
        // Should contain error related to decryption
        $this->assertTrue(
            str_contains($decrypted, 'Erro') || str_contains($decrypted, 'Não foi possivel'),
            'Should return error message',
        );
    }

    /**
     * Test descriptografar - Too short encrypted string returns error message
     *
     * @return void
     */
    public function testDescriptografarTooShortEncryptedString(): void
    {
        $encrypted = Criptografia::criptografar('text');
        // Remove too many characters to make it shorter than nonce length
        $tooShort = substr($encrypted, 0, 10);

        $decrypted = Criptografia::descriptografar($tooShort);

        $this->assertIsString($decrypted);
        // Should contain error message about decryption
        $this->assertTrue(
            str_contains($decrypted, 'Erro') || str_contains($decrypted, 'Não foi possivel'),
            'Should return error message',
        );
    }

    /**
     * Test criptografar and descriptografar roundtrip with various texts
     *
     * @return void
     */
    public function testEncryptDecryptRoundtrip(): void
    {
        $testTexts = [
            'Simple text',
            'Text with numbers 12345',
            'Text with special chars !@#$%',
            'Very long text ' . str_repeat('x', 1000),
            'Múltiple líneas\nde\ntexto',
            '{"json": "data", "nested": {"value": 123}}',
        ];

        foreach ($testTexts as $text) {
            $encrypted = Criptografia::criptografar($text);
            $decrypted = Criptografia::descriptografar($encrypted);

            $this->assertEquals(
                $text,
                $decrypted,
                'Roundtrip failed for text: ' . substr($text, 0, 50),
            );
        }
    }

    /**
     * Test criptografar - Output length consistency
     *
     * @return void
     */
    public function testCriptografarOutputLength(): void
    {
        $plainText1 = 'short';
        $plainText2 = 'a much longer text here';

        $encrypted1 = Criptografia::criptografar($plainText1);
        $encrypted2 = Criptografia::criptografar($plainText2);

        // Both should be encrypted properly
        $this->assertNotEmpty($encrypted1);
        $this->assertNotEmpty($encrypted2);

        // Both should contain the nonce (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES = 24)
        // Plus the ciphertext
        // Hex encoded: nonce is 48 hex chars (24 bytes * 2)
        // Ciphertext varies
        $this->assertTrue(strlen($encrypted1) > 48, 'Should contain nonce + ciphertext');
        $this->assertTrue(strlen($encrypted2) > 48, 'Should contain nonce + ciphertext');
    }
}
