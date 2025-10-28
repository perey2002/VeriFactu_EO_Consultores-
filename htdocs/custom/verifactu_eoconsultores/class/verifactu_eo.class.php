<?php
/*
 * Main class implementing VeriFactu EO Consultores features.
 */

dol_include_once('/core/lib/files.lib.php');
dol_include_once('/core/lib/date.lib.php');
dol_include_once('/core/lib/price.lib.php');
dol_include_once('/verifactu_eoconsultores/lib/qr.php');

/**
 * Class VerifactuEO.
 */
class VerifactuEO
{
    /**
     * @var DoliDB
     */
    private $db;

    /**
     * Constructor.
     *
     * @param DoliDB $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Register invoice validation.
     *
     * @param Facture $invoice
     * @param User $user
     * @return void
     */
    public function registerInvoice($invoice, $user)
    {
        global $conf;

        $this->db->begin();

        try {
            $config = $this->loadConfig();

            $data = array(
                'type' => 'ALTA',
                'fk_facture' => $invoice->id,
                'ref' => $invoice->ref,
                'date' => dol_now(),
                'emisor_nif' => $config['nif'],
                'importe_total' => price2num($invoice->total_ttc, 'MT'),
                'mode' => $config['mode'],
                'system_id' => $config['system_id'],
                'entity' => $invoice->entity,
                'user_id' => $user->id
            );

            $hashRecord = $this->createHashRecord($data);
            $this->saveRegister($data, $hashRecord);

            if (!empty($config['qr_enabled'])) {
                $this->generateAndStoreQr($invoice, $hashRecord['hash']);
            }

            $this->logAction('BILL_VALIDATE', $invoice->ref, $hashRecord['hash'], $user->login);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            dol_syslog(__METHOD__.' Error: '.$e->getMessage(), LOG_ERR);
        }
    }

    /**
     * Register invoice cancellation.
     *
     * @param Facture $invoice
     * @param User $user
     * @return void
     */
    public function cancelInvoice($invoice, $user)
    {
        $this->db->begin();

        try {
            $config = $this->loadConfig();

            $data = array(
                'type' => 'ANULACION',
                'fk_facture' => $invoice->id,
                'ref' => $invoice->ref,
                'date' => dol_now(),
                'emisor_nif' => $config['nif'],
                'importe_total' => price2num($invoice->total_ttc, 'MT'),
                'mode' => $config['mode'],
                'system_id' => $config['system_id'],
                'entity' => $invoice->entity,
                'user_id' => $user->id
            );

            $hashRecord = $this->createHashRecord($data);
            $this->saveRegister($data, $hashRecord);

            $this->logAction('BILL_CANCEL', $invoice->ref, $hashRecord['hash'], $user->login);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            dol_syslog(__METHOD__.' Error: '.$e->getMessage(), LOG_ERR);
        }
    }

    /**
     * Create hash record using SHA-256 chained to previous invoice hash.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function createHashRecord($data)
    {
        $previous = $this->getLastHash();
        $payload = json_encode($data);

        $concat = ($previous ? $previous['hash'] : '').'|'.$payload;
        $hash = hash('sha256', $concat);

        $signature = $this->signPayload($hash);

        return array(
            'previous_hash' => $previous ? $previous['hash'] : null,
            'hash' => $hash,
            'signature' => $signature,
            'payload' => $payload
        );
    }

    /**
     * Persist register and hash rows.
     *
     * @param array $data
     * @param array $hashRecord
     * @return void
     * @throws Exception
     */
    private function saveRegister($data, $hashRecord)
    {
        global $conf;

        $sql = 'INSERT INTO '.MAIN_DB_PREFIX."verifactu_register (type, fk_facture, ref, tms, emisor_nif, importe_total, mode, system_id, entity, fk_user)"
            ." VALUES ('".$this->db->escape($data['type'])."', ".$data['fk_facture'].", '".$this->db->escape($data['ref'])."', "
            .$data['date'].", '".$this->db->escape($data['emisor_nif'])."', '".$this->db->escape($data['importe_total'])."', '"
            .$this->db->escape($data['mode'])."', '".$this->db->escape($data['system_id'])."', ".$data['entity'].', '.$data['user_id'].')';

        $res = $this->db->query($sql);
        if (!$res) {
            throw new Exception($this->db->lasterror());
        }

        $registerId = $this->db->last_insert_id(MAIN_DB_PREFIX.'verifactu_register');

        $sqlHash = 'INSERT INTO '.MAIN_DB_PREFIX."verifactu_hash (fk_register, previous_hash, hash_value, signature)"
            .' VALUES ('.$registerId.', '.($hashRecord['previous_hash'] ? "'".$this->db->escape($hashRecord['previous_hash'])."'" : 'NULL')
            .", '".$this->db->escape($hashRecord['hash'])."', "
            .($hashRecord['signature'] ? "'".$this->db->escape($hashRecord['signature'])."'" : 'NULL').')';

        $resHash = $this->db->query($sqlHash);
        if (!$resHash) {
            throw new Exception($this->db->lasterror());
        }
    }

    /**
     * Generate and store QR PNG for invoice.
     *
     * @param Facture $invoice
     * @param string $hash
     * @return void
     */
    private function generateAndStoreQr($invoice, $hash)
    {
        global $conf;

        $dir = $conf->facture->dir_output.'/'.$invoice->ref;
        dol_mkdir($dir);

        $config = $this->loadConfig();
        $qrData = array(
            'nif' => $config['nif'],
            'ref' => $invoice->ref,
            'date' => dol_print_date($invoice->date_validation ?: dol_now(), 'dayrfc'),
            'total' => price2num($invoice->total_ttc, 'MT'),
            'hash' => $hash,
            'mode' => $config['mode'],
            'system_id' => $config['system_id']
        );

        $qrUrl = build_verifactu_qr_url($qrData, $hash);
        $file = $dir.'/verifactu_qr.png';
        generate_qr_png($qrUrl, $file);
    }

    /**
     * Load latest hash stored.
     *
     * @return array|null
     */
    private function getLastHash()
    {
        global $conf;

        $sql = 'SELECT h.hash_value as hash FROM '.MAIN_DB_PREFIX.'verifactu_hash h'
            .' INNER JOIN '.MAIN_DB_PREFIX.'verifactu_register r ON r.rowid = h.fk_register'
            .' WHERE r.entity = '.((int) $conf->entity)
            .' ORDER BY h.rowid DESC LIMIT 1';
        $res = $this->db->query($sql);
        if ($res && $this->db->num_rows($res)) {
            return $this->db->fetch_array($res);
        }

        return null;
    }

    /**
     * Sign payload when mode is NO VERI*FACTU.
     *
     * @param string $hash
     * @return string|null
     */
    private function signPayload($hash)
    {
        $config = $this->loadConfig();

        if ($config['mode'] !== 'NO VERI*FACTU' || empty($config['pem_path'])) {
            return null;
        }

        if (!is_readable($config['pem_path'])) {
            dol_syslog(__METHOD__.' PEM file not readable: '.$config['pem_path'], LOG_WARNING);
            return null;
        }

        $privateKey = openssl_pkey_get_private(file_get_contents($config['pem_path']), $config['pem_password']);
        if (!$privateKey) {
            dol_syslog(__METHOD__.' Unable to load private key', LOG_WARNING);
            return null;
        }

        $signature = '';
        $ok = openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        if (!$ok) {
            dol_syslog(__METHOD__.' Unable to sign hash', LOG_WARNING);
            return null;
        }

        return base64_encode($signature);
    }

    /**
     * Load configuration from Dolibarr constants.
     *
     * @return array
     */
    private function loadConfig()
    {
        global $conf;

        return array(
            'nif' => !empty($conf->global->VERIFACTU_EO_NIF) ? $conf->global->VERIFACTU_EO_NIF : '',
            'mode' => !empty($conf->global->VERIFACTU_EO_MODE) ? $conf->global->VERIFACTU_EO_MODE : 'VERI*FACTU',
            'system_id' => !empty($conf->global->VERIFACTU_EO_SYSTEM_ID) ? $conf->global->VERIFACTU_EO_SYSTEM_ID : '',
            'pem_path' => !empty($conf->global->VERIFACTU_EO_PEM_PATH) ? $conf->global->VERIFACTU_EO_PEM_PATH : '',
            'pem_password' => !empty($conf->global->VERIFACTU_EO_PEM_PASSWORD) ? $conf->global->VERIFACTU_EO_PEM_PASSWORD : '',
            'qr_enabled' => !empty($conf->global->VERIFACTU_EO_QR_ENABLED) ? (bool) $conf->global->VERIFACTU_EO_QR_ENABLED : false
        );
    }

    /**
     * Log invoice event to VeriFactu log table.
     *
     * @param string $action
     * @param string $ref
     * @param string $hash
     * @param string $login
     * @return void
     */
    private function logAction($action, $ref, $hash, $login = 'system')
    {
        global $conf;

        $sql = 'INSERT INTO '.MAIN_DB_PREFIX."verifactu_log (entity, user_login, action, details) VALUES (".
            ((int) $conf->entity).", '".$this->db->escape($login)."', '".$this->db->escape($action)."', '".$this->db->escape(json_encode(array('ref' => $ref, 'hash' => $hash)))."')";
        $this->db->query($sql);
    }
}
