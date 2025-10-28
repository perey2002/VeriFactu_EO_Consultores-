<?php
require '../main.inc.php';

global $db, $conf, $user;

if (empty($conf->verifactu_eoconsultores->enabled)) {
    accessforbidden();
}

if (empty($user->rights->verifactu_eoconsultores->export)) {
    accessforbidden();
}

$from = GETPOST('from', 'int');
$to = GETPOST('to', 'int');

$sql = 'SELECT r.rowid, r.type, r.ref, r.tms, r.emisor_nif, r.importe_total, r.mode, r.system_id, h.hash_value, h.previous_hash, h.signature'
    .' FROM '.MAIN_DB_PREFIX.'verifactu_register r'
    .' LEFT JOIN '.MAIN_DB_PREFIX.'verifactu_hash h ON h.fk_register = r.rowid'
    .' WHERE r.entity = '.((int) $conf->entity);

if (!empty($from)) {
    $sql .= ' AND r.tms >= '.((int) $from);
}
if (!empty($to)) {
    $sql .= ' AND r.tms <= '.((int) $to);
}

$sql .= ' ORDER BY r.tms ASC';

$resql = $db->query($sql);
$items = array();
if ($resql) {
    while ($obj = $db->fetch_object($resql)) {
        $items[] = array(
            'rowid' => (int) $obj->rowid,
            'type' => $obj->type,
            'ref' => $obj->ref,
            'timestamp' => (int) $obj->tms,
            'emisor_nif' => $obj->emisor_nif,
            'importe_total' => (float) $obj->importe_total,
            'mode' => $obj->mode,
            'system_id' => $obj->system_id,
            'hash' => $obj->hash_value,
            'previous_hash' => $obj->previous_hash,
            'signature' => $obj->signature
        );
    }
}

$result = array(
    'entity' => (int) $conf->entity,
    'module_version' => '0.4.0',
    'mode' => !empty($conf->global->VERIFACTU_EO_MODE) ? $conf->global->VERIFACTU_EO_MODE : 'VERI*FACTU',
    'count' => count($items),
    'items' => $items
);

header('Content-Type: application/json');
print json_encode($result, JSON_PRETTY_PRINT);
exit;
