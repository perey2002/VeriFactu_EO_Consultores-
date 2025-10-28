<?php
/*
 * QR helper library for VeriFactu EO Consultores.
 */

dol_include_once('/core/lib/functions.lib.php');

if (!class_exists('QRcode')) {
    $paths = array(
        DOL_DOCUMENT_ROOT.'/includes/phpqrcode/qrlib.php',
        DOL_DOCUMENT_ROOT.'/core/lib/phpqrcode/qrlib.php'
    );
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

if (!class_exists('QRcode')) {
    /**
     * Fallback QR generator that stores textual content if library missing.
     */
    class QRcode
    {
        public static function png($text, $outfile, $level = 'M', $size = 4, $margin = 2)
        {
            file_put_contents($outfile, $text);
        }
    }
}

if (!defined('QR_ECLEVEL_M')) {
    define('QR_ECLEVEL_M', 'M');
}

/**
 * Build AEAT VeriFactu QR URL.
 *
 * @param array $data
 * @param string $hash
 * @return string
 */
function build_verifactu_qr_url($data, $hash)
{
    $params = array(
        'nif' => $data['nif'],
        'numfact' => $data['ref'],
        'fecha' => $data['date'],
        'importe' => $data['total'],
        'hash' => $hash,
        'modo' => $data['mode']
    );

    if (!empty($data['system_id'])) {
        $params['sistema'] = $data['system_id'];
    }

    return 'https://www2.agenciatributaria.gob.es/verifactu/qr?'.http_build_query($params);
}

/**
 * Generate QR PNG file.
 *
 * @param string $text
 * @param string $outfile
 * @return void
 */
function generate_qr_png($text, $outfile)
{
    $dir = dirname($outfile);
    if (!is_dir($dir)) {
        dol_mkdir($dir);
    }

    QRcode::png($text, $outfile, QR_ECLEVEL_M, 4, 2);
}
