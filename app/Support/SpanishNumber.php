<?php

namespace App\Support;

/**
 * Conversión de montos a letras en español (para comprobantes de pago).
 * Soporta hasta cientos de millones, con centavos en formato XX/100.
 */
class SpanishNumber
{
    private static array $unidades = [
        '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
        'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete',
        'dieciocho', 'diecinueve', 'veinte', 'veintiuno', 'veintidós', 'veintitrés',
        'veinticuatro', 'veinticinco', 'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve',
    ];

    private static array $decenas = [
        '', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa',
    ];

    private static array $centenas = [
        '', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos',
        'seiscientos', 'setecientos', 'ochocientos', 'novecientos',
    ];

    /** Monto en letras con moneda, p. ej. "Cinco mil dólares con 00/100 USD". */
    public static function money(float $amount, string $currencyWord = 'dólares', string $currencyCode = 'USD'): string
    {
        $entero   = (int) floor($amount);
        $centavos = (int) round(($amount - $entero) * 100);

        $palabras = $entero === 0 ? 'cero' : self::convert($entero);
        $palabras = ucfirst(trim($palabras));

        return sprintf('%s %s con %02d/100 %s', $palabras, $currencyWord, $centavos, $currencyCode);
    }

    private static function convert(int $n): string
    {
        if ($n < 30) {
            return self::$unidades[$n];
        }
        if ($n < 100) {
            $d = intdiv($n, 10);
            $u = $n % 10;
            return self::$decenas[$d] . ($u ? ' y ' . self::$unidades[$u] : '');
        }
        if ($n === 100) {
            return 'cien';
        }
        if ($n < 1000) {
            $c = intdiv($n, 100);
            $r = $n % 100;
            return self::$centenas[$c] . ($r ? ' ' . self::convert($r) : '');
        }
        if ($n < 1000000) {
            $miles = intdiv($n, 1000);
            $r = $n % 1000;
            $prefijo = $miles === 1 ? 'mil' : self::convert($miles) . ' mil';
            return trim($prefijo . ($r ? ' ' . self::convert($r) : ''));
        }
        $millones = intdiv($n, 1000000);
        $r = $n % 1000000;
        $prefijo = $millones === 1 ? 'un millón' : self::convert($millones) . ' millones';
        return trim($prefijo . ($r ? ' ' . self::convert($r) : ''));
    }
}
