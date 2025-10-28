<?php
/*
 * PDF hook to inject VeriFactu QR.
 */

dol_include_once('/verifactu_eoconsultores/lib/qr.php');

class ActionsVerifactu_eoconsultores
{
    /**
     * Hook executed during invoice PDF build.
     *
     * @param array $parameters
     * @param Facture $object
     * @param string $action
     * @param HookManager $hookmanager
     * @return int
     */
    public function hookBILL_PDF_BUILD($parameters, &$object, &$action, $hookmanager)
    {
        if ($object->element === 'facture' && !empty($parameters['pdf'])) {
            $this->injectQrIntoPdf($parameters['pdf'], $object);
        }

        return 0;
    }

    /**
     * Insert QR into PDF document at footer near totals.
     *
     * @param TCPDF $pdf
     * @param Facture $invoice
     * @return void
     */
    private function injectQrIntoPdf($pdf, $invoice)
    {
        global $conf;

        $qrFile = $conf->facture->dir_output.'/'.$invoice->ref.'/verifactu_qr.png';
        if (!file_exists($qrFile)) {
            return;
        }

        $x = 15;
        $y = $pdf->getPageHeight() - 50;
        $pdf->Image($qrFile, $x, $y, 30, 30);

        $pdf->SetXY($x, $y + 32);
        $pdf->SetFont('', '', 8);
        $pdf->MultiCell(70, 5, 'QR VeriFactu EO Consultores', 0, 'L');
    }
}
