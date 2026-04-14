<?php

namespace App\Helpers;

/**
 * Generador de código de barras Code39 como SVG inline.
 * Compatible con DomPDF.
 */
class BarcodeHelper
{
    /**
     * Patrones Code39 (9 bits: posiciones pares=barra, impares=espacio; 1=ancho, 0=estrecho)
     */
    private static array $patterns = [
        '0' => '000110100', '1' => '100100001', '2' => '001100001', '3' => '101100000',
        '4' => '000110001', '5' => '100110000', '6' => '001110000', '7' => '000100101',
        '8' => '100100100', '9' => '001100100', 'A' => '100001001', 'B' => '001001001',
        'C' => '101001000', 'D' => '000011001', 'E' => '100011000', 'F' => '001011000',
        'G' => '000001101', 'H' => '100001100', 'I' => '001001100', 'J' => '000011100',
        'K' => '100000011', 'L' => '001000011', 'M' => '101000010', 'N' => '000010011',
        'O' => '100010010', 'P' => '001010010', 'Q' => '000000111', 'R' => '100000110',
        'S' => '001000110', 'T' => '000010110', 'U' => '110000001', 'V' => '011000001',
        'W' => '111000000', 'X' => '010010001', 'Y' => '110010000', 'Z' => '011010000',
        '-' => '010000101', '.' => '110000100', ' ' => '011000100', '$' => '010101000',
        '/' => '010100010', '+' => '010001010', '%' => '000101010', '*' => '010010100',
    ];

    /**
     * Genera un SVG con el código de barras Code39 para el texto dado.
     *
     * @param string $text   Texto a codificar (se convierte a mayúsculas)
     * @param int    $module Ancho del módulo estrecho en puntos SVG (por defecto 2)
     * @param int    $height Alto de las barras en puntos SVG (por defecto 60)
     * @return string  SVG completo como string
     */
    public static function generateSvg(string $text, int $module = 2, int $height = 60): string
    {
        $text   = strtoupper(preg_replace('/[^0-9A-Z\-\.\$\/\+\% ]/', '', $text));
        $chars  = array_merge(['*'], str_split($text), ['*']);

        $bars = [];
        $x    = 0;

        foreach ($chars as $i => $char) {
            // Separador entre caracteres (espacio estrecho)
            if ($i > 0) {
                $x += $module; // gap estrecho (no se dibuja)
            }

            if (!isset(self::$patterns[$char])) {
                continue;
            }

            $pattern = self::$patterns[$char];

            for ($j = 0; $j < 9; $j++) {
                $wide  = ($pattern[$j] === '1');
                $width = ($wide ? 3 : 1) * $module;

                // Posiciones pares (0,2,4,6,8) → barra negra
                if ($j % 2 === 0) {
                    $bars[] = compact('x', 'width');
                }

                $x += $width;
            }
        }

        $totalWidth = $x;

        $rects = '';
        foreach ($bars as $bar) {
            $rects .= sprintf(
                '<rect x="%d" y="0" width="%d" height="%d" fill="#000000"/>',
                $bar['x'],
                $bar['width'],
                $height
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">%s</svg>',
            $totalWidth,
            $height,
            $totalWidth,
            $height,
            $rects
        );
    }
}
