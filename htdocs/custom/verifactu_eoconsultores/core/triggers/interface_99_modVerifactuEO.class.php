<?php
/*
 * Trigger for VeriFactu EO Consultores.
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

/**
 * Trigger class capturing invoice lifecycle events.
 */
class InterfaceVerifactuEOTrigger extends DolibarrTriggers
{
    /**
     * @var string
     */
    public $family = 'EO Consultores';

    /**
     * @var string
     */
    public $description = 'Trigger para integrar el registro VeriFactu con las facturas.';

    /**
     * @var string
     */
    public $picto = 'bill';

    /**
     * Constructor.
     *
     * @param DoliDB $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->name = preg_replace('/^interface_/', '', get_class($this));
    }

    /**
     * Execute trigger.
     *
     * @param string $action Event action
     * @param CommonObject $object Object
     * @param User $user User
     * @param Translate $langs Langs
     * @param Conf $conf Conf
     *
     * @return int
     */
    public function runTrigger($action, $object, $user, $langs, $conf)
    {
        if (empty($conf->verifactu_eoconsultores->enabled)) {
            return 0;
        }

        if ($object->element !== 'facture') {
            return 0;
        }

        dol_include_once('/verifactu_eoconsultores/class/verifactu_eo.class.php');
        $verifactu = new VerifactuEO($this->db);

        if ($action === 'BILL_VALIDATE') {
            $verifactu->registerInvoice($object, $user);
        }

        if ($action === 'BILL_CANCEL') {
            $verifactu->cancelInvoice($object, $user);
        }

        return 0;
    }
}
