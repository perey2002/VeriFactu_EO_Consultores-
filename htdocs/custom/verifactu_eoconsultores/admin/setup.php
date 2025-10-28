<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/verifactu_eoconsultores/lib/qr.php');

global $conf, $langs, $user, $db;

$langs->loadLangs(array('admin', 'verifactu_eoconsultores@verifactu_eoconsultores'));

if (!$user->admin && empty($user->rights->verifactu_eoconsultores->setup)) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$message = '';
$error = 0;

$backtopage = $_SERVER['PHP_SELF'];

if ($action === 'save') {
    $nif = trim(GETPOST('nif_emisor', 'alphanohtml'));
    $mode = GETPOST('modalidad', 'alphanohtml');
    $systemId = trim(GETPOST('system_id', 'alphanohtml'));
    $pemPath = trim(GETPOST('pem_path', 'restricthtml'));
    $pemPassword = GETPOST('pem_password', 'restricthtml');
    $qrEnabled = GETPOST('qr_enabled', 'int');
    if ($qrEnabled != 1) {
        $qrEnabled = 0;
    }

    dolibarr_set_const($db, 'VERIFACTU_EO_NIF', $nif, 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'VERIFACTU_EO_MODE', $mode, 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'VERIFACTU_EO_SYSTEM_ID', $systemId, 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'VERIFACTU_EO_PEM_PATH', $pemPath, 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'VERIFACTU_EO_PEM_PASSWORD', $pemPassword, 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'VERIFACTU_EO_QR_ENABLED', $qrEnabled, 'chaine', 0, '', $conf->entity);

    $message = $langs->trans('SetupSaved');
    logSetupChange($user, array(
        'nif' => $nif,
        'mode' => $mode,
        'system_id' => $systemId,
        'pem_path' => $pemPath,
        'qr_enabled' => $qrEnabled
    ));
}

if ($action === 'testqr') {
    $data = array(
        'nif' => isset($conf->global->VERIFACTU_EO_NIF) ? $conf->global->VERIFACTU_EO_NIF : '',
        'ref' => 'TEST-QR',
        'date' => dol_print_date(dol_now(), 'dayrfc'),
        'total' => '0.00',
        'mode' => isset($conf->global->VERIFACTU_EO_MODE) ? $conf->global->VERIFACTU_EO_MODE : 'VERI*FACTU'
    );
    $hash = hash('sha256', microtime(true));
    $url = build_verifactu_qr_url($data, $hash);
    $file = DOL_DATA_ROOT.'/test_qr.png';
    generate_qr_png($url, $file);
    setEventMessages($langs->trans('QrGenerated', $file), null, 'mesgs');
}

if (!empty($message)) {
    setEventMessages($message, null, 'mesgs');
}

$mode = !empty($conf->global->VERIFACTU_EO_MODE) ? $conf->global->VERIFACTU_EO_MODE : 'VERI*FACTU';
$qrEnabled = !empty($conf->global->VERIFACTU_EO_QR_ENABLED) ? (int) $conf->global->VERIFACTU_EO_QR_ENABLED : 0;
$nifValue = isset($conf->global->VERIFACTU_EO_NIF) ? $conf->global->VERIFACTU_EO_NIF : '';
$systemIdValue = isset($conf->global->VERIFACTU_EO_SYSTEM_ID) ? $conf->global->VERIFACTU_EO_SYSTEM_ID : '';
$pemPathValue = isset($conf->global->VERIFACTU_EO_PEM_PATH) ? $conf->global->VERIFACTU_EO_PEM_PATH : '';
$pemPasswordValue = isset($conf->global->VERIFACTU_EO_PEM_PASSWORD) ? $conf->global->VERIFACTU_EO_PEM_PASSWORD : '';

llxHeader('', 'VeriFactu EO Consultores');

print load_fiche_titre('VeriFactu EO Consultores');

print '<form method="post" action="'.$backtopage.'" role="form" aria-label="Configuración VeriFactu EO Consultores">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="save">';

print '<div class="fichecenter">';
print '<table class="noborder" role="presentation">';

print '<tr><td><label for="nif_emisor" title="NIF del emisor" aria-label="NIF del emisor">NIF Emisor</label></td>';
print '<td><input type="text" class="flat" id="nif_emisor" name="nif_emisor" placeholder="ES12345678A" title="NIF del emisor" aria-label="NIF del emisor" value="'.dol_escape_htmltag($nifValue).'" required></td></tr>';

print '<tr><td><label for="modalidad" title="Modalidad del sistema" aria-label="Modalidad">Modalidad</label></td>';
print '<td><select id="modalidad" name="modalidad" title="Selecciona la modalidad" aria-label="Selecciona la modalidad">';
$opts = array('VERI*FACTU', 'NO VERI*FACTU');
foreach ($opts as $opt) {
    $selected = ($mode === $opt) ? ' selected' : '';
    print '<option value="'.$opt.'"'.$selected.'>'.$opt.'</option>';
}
print '</select></td></tr>';

print '<tr><td><label for="system_id" title="Identificador del sistema" aria-label="Identificador del sistema">Sistema ID</label></td>';
print '<td><input type="text" class="flat" id="system_id" name="system_id" placeholder="EO-VERIFACTU-001" title="Identificador del sistema" aria-label="Identificador del sistema" value="'.dol_escape_htmltag($systemIdValue).'"></td></tr>';

print '<tr><td><label for="pem_path" title="Ruta del archivo PEM" aria-label="Ruta PEM">Ruta Clave PEM</label></td>';
print '<td><input type="text" class="flat" id="pem_path" name="pem_path" placeholder="/var/keys/private.pem" title="Ruta del archivo PEM" aria-label="Ruta del archivo PEM" value="'.dol_escape_htmltag($pemPathValue).'"></td></tr>';

print '<tr><td><label for="pem_password" title="Contraseña PEM" aria-label="Contraseña PEM">Password PEM</label></td>';
print '<td><input type="password" class="flat" id="pem_password" name="pem_password" placeholder="••••" title="Contraseña PEM" aria-label="Contraseña PEM" value="'.dol_escape_htmltag($pemPasswordValue).'"></td></tr>';

print '<tr><td><label for="qr_enabled" title="Activar QR en PDF" aria-label="Activar QR">Activar QR</label></td>';
print '<td><select id="qr_enabled" name="qr_enabled" title="Activar código QR" aria-label="Activar código QR">';
print '<option value="1"'.($qrEnabled ? ' selected="selected"' : '').'>Sí</option>';
print '<option value="0"'.(!$qrEnabled ? ' selected="selected"' : '').'>No</option>';
print '</select></td></tr>';

print '</table>';
print '</div>';

print '<div class="center">';
print '<button type="submit" class="button" aria-label="Guardar configuración" role="button">💾 Guardar configuración</button>';
print '</div>';
print '</form>';

print '<form method="post" action="'.$backtopage.'" role="form" aria-label="Generar QR de prueba">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="testqr">';
print '<div class="center">';
print '<button type="submit" class="button" aria-label="Generar QR de prueba EO Consultores" role="button">🧪 Generar QR de prueba (EO Consultores)</button>';
print '</div>';
print '</form>';

print '<p class="center">Versión del módulo: 0.4.0 — EO Consultores</p>';

llxFooter();
$db->close();

/**
 * Log setup changes to file.
 *
 * @param User $user
 * @param array $data
 * @return void
 */
function logSetupChange($user, $data)
{
    global $conf, $db;

    $file = DOL_DATA_ROOT.'/verifactu_eoconsultores_setup.log';

    $entry = dol_print_date(dol_now(), 'dayhourlog').'|'.$user->login.'|'.json_encode($data)."\n";
    dol_mkdir(dirname($file));
    file_put_contents($file, $entry, FILE_APPEND);

    $sql = 'INSERT INTO '.MAIN_DB_PREFIX."verifactu_log (entity, user_login, action, details) VALUES (".
        ((int) $conf->entity).", '".$db->escape($user->login)."', 'CONFIG_SAVE', '".$db->escape(json_encode($data))."')";
    $db->query($sql);
}
?>
