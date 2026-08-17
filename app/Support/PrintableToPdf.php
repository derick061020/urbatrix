<?php

namespace App\Support;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Convierte los documentos imprimibles (plan de pagos, promesa de compraventa,
 * KYC) de HTML a PDF real, para que el botón "Descargar" entregue un archivo en
 * vez de abrir una pestaña con el diálogo de impresión.
 *
 * El HTML guardado NO se toca: los ajustes de compatibilidad se inyectan al
 * vuelo, sólo en la ruta de descarga. La vista en pantalla y el window.print()
 * del navegador siguen exactamente igual.
 */
class PrintableToPdf
{
    /**
     * CSS que se agrega al final del <head> justo antes de renderizar.
     *
     * dompdf no implementa flexbox ni grid, así que los pocos bloques que los
     * usan para ponerse en fila se re-declaran como tabla, que sí soporta bien.
     * Sin esto las columnas se apilan una debajo de otra.
     */
    private const COMPAT_CSS = <<<'CSS'
/* ── compatibilidad dompdf (inyectado sólo al generar el PDF) ── */
.no-print, .toolbar, .print-bar { display: none !important; }

/* plan de pagos */
.unit-row  { display: table !important; width: 100%; }
.unit-cell { display: table-cell !important; vertical-align: top; }
.stages    { display: table !important; width: 100%; border-spacing: 5px 0; }
.stages > div { display: table-cell !important; width: 33.33%; vertical-align: top; }

/* promesa de compraventa */
.cover-parties   { display: table !important; width: 100%; }
.cover-parties > div { display: table-cell !important; vertical-align: top; }
.cover-prop-grid { display: table !important; width: 100%; }
.cover-prop-grid > div { display: table-cell !important; width: 25%; vertical-align: top; }
.cover-meta      { display: table !important; width: 100%; border-spacing: 8px 0; }
.cover-meta > div { display: table-cell !important; width: 33.33%; vertical-align: top; }
.cover-price     { display: block !important; }

/* comunes a ambos */
.sig-grid        { display: table !important; width: 100%; border-spacing: 16px 0; }
.sig-grid > div  { display: table-cell !important; width: 50%; vertical-align: top; }
.hdr, .ph, .footer, .hdr-logo, .ph-left, .art-hdr { display: block !important; }
CSS;

    /**
     * Devuelve los bytes del PDF a partir del HTML del documento.
     */
    public static function render(string $html, string $paper = 'letter'): string
    {
        $options = new Options();
        // Las plantillas traen su CSS embebido; no hay assets remotos que buscar.
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        // Con media "print" se aplican los @media print de las plantillas, que es
        // lo que oculta la barra "Descargar PDF" de la vista en pantalla.
        $options->set('defaultMediaType', 'print');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(self::withCompatCss($html), 'UTF-8');
        $dompdf->setPaper($paper, 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Mete COMPAT_CSS al final del <head>. Si el documento no tuviera <head>
     * (no debería pasar), lo antepone: igual gana por orden de cascada.
     */
    private static function withCompatCss(string $html): string
    {
        $style = '<style>'.self::COMPAT_CSS.'</style>';

        $pos = stripos($html, '</head>');
        if ($pos !== false) {
            return substr($html, 0, $pos).$style.substr($html, $pos);
        }

        return $style.$html;
    }

    /**
     * Nombre de archivo .pdf a partir del nombre guardado del documento.
     */
    public static function filename(?string $original, string $fallback): string
    {
        $base = pathinfo($original ?: $fallback, PATHINFO_FILENAME);

        return ($base !== '' ? $base : 'documento').'.pdf';
    }
}
