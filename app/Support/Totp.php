<?php

namespace App\Support;

/**
 * Implementación autónoma de TOTP (RFC 6238) / HOTP (RFC 4226).
 *
 * No depende de paquetes externos: usa hash_hmac con SHA1, compatible con
 * Google Authenticator, Authy, Microsoft Authenticator, 1Password, etc.
 * El secreto se maneja en Base32 (RFC 4648), el alfabeto que esperan las apps.
 */
final class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const DIGITS   = 6;
    private const PERIOD   = 30; // segundos

    /** Genera un secreto Base32 aleatorio (por defecto 160 bits). */
    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    /**
     * Verifica un código de 6 dígitos contra el secreto, tolerando un margen
     * de ±$window períodos (deriva de reloj). $window = 1 ⇒ ±30s.
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $counter = (int) floor(time() / self::PERIOD);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::codeAt($secret, $counter + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    /** Calcula el código HOTP para un contador dado. */
    public static function codeAt(string $secret, int $counter): string
    {
        $key    = self::base32Decode($secret);
        $binary = pack('N*', 0) . pack('N*', $counter); // contador de 64 bits, big-endian
        $hash   = hash_hmac('sha1', $binary, $key, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $value  = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string) $value, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Construye la URI otpauth:// que se codifica en el QR. */
    public static function otpauthUri(string $secret, string $accountName, string $issuer): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);
        $query = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => 'SHA1',
            'digits'    => self::DIGITS,
            'period'    => self::PERIOD,
        ]);
        return "otpauth://totp/{$label}?{$query}";
    }

    /* ---------------- Base32 (RFC 4648, sin padding al decodificar) -------- */

    private static function base32Encode(string $data): string
    {
        $bits = '';
        foreach (str_split($data) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }
        return $out;
    }

    private static function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        if ($secret === '') {
            return '';
        }
        $bits = '';
        foreach (str_split($secret) as $char) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }
        return $out;
    }
}
