<?php
/*
 * Copyright (C) 2024 EO Consultores
 *
 * This file is part of VeriFactu EO Consultores, a module for Dolibarr ERP/CRM.
 *
 * VeriFactu EO Consultores is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * VeriFactu EO Consultores is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       modVerifactuEO.class.php
 *  \ingroup    verifactu_eoconsultores
 *  \brief      Module descriptor for VeriFactu EO Consultores.
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Module descriptor.
 */
class modVerifactuEO extends DolibarrModules
{
    /**
     * Constructor.
     *
     * @param  DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $conf;

        $this->db = $db;

        $this->numero = 107500; // Unique ID not used by Dolibarr core modules
        $this->rights_class = 'verifactu_eoconsultores';

        $this->family = 'EO Consultores';
        $this->module_position = 501; // Display order in module list

        $this->name = preg_replace('/^mod/', '', get_class($this));
        $this->description = 'VeriFactu EO Consultores - Cumplimiento RD 1007/2023';
        $this->editor_name = 'EO Consultores';
        $this->editor_url = 'https://www.eoconsultores.es';

        $this->version = '0.4.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->special = 0;
        $this->picto = 'generic';

        $this->module_parts = array(
            'triggers' => 1,
            'hooks' => array('pdfgeneration'),
            'models' => 1
        );

        $this->dirs = array('/verifactu_eoconsultores');
        $this->config_page_url = array('setup.php@verifactu_eoconsultores');
        $this->langfiles = array('verifactu_eoconsultores@verifactu_eoconsultores');

        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(7, 4); // PHP 7.4 minimum
        $this->need_dolibarr_version = array(17, 0);

        $this->const = array();
        $this->tabs = array();

        $this->dictionaries = array();
        $this->boxes = array();

        $this->rights = array();
        $this->rights_class = 'verifactu_eoconsultores';

        $r = 0;
        $this->rights[$r][0] = 1075001;
        $this->rights[$r][1] = 'Administrar la configuración de VeriFactu EO Consultores';
        $this->rights[$r][3] = 1;
        $this->rights[$r][4] = 'setup';
        $r++;

        $this->rights[$r][0] = 1075002;
        $this->rights[$r][1] = 'Exportar registros VeriFactu';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'export';
        $r++;

        $this->menu = array();
        $this->menu[] = array(
            'fk_menu' => 'fk_mainmenu=accountancy,billing',
            'type' => 'left',
            'titre' => 'VeriFactu EO Consultores',
            'mainmenu' => 'billing',
            'leftmenu' => 'verifactu_eoconsultores',
            'url' => '/verifactu_eoconsultores/admin/setup.php',
            'langs' => 'verifactu_eoconsultores@verifactu_eoconsultores',
            'position' => 100,
            'enabled' => '$conf->verifactu_eoconsultores->enabled',
            'perms' => '$user->rights->verifactu_eoconsultores->setup',
            'target' => '',
            'user' => 2
        );

        if (!empty($conf->global->MAIN_MODULE_VERIFACTUEOCONSULTORES)) {
            $this->const[] = array('VERIFACTU_EO_VERSION', 'chaine', $this->version, 'Versión del módulo VeriFactu EO Consultores', 1, 'current');
        }
    }

    /**
     * Init module (create tables, constants).
     *
     * @param string $options
     * @return int
     */
    public function init($options = '')
    {
        $sql = array();

        $result = $this->_load_tables('/verifactu_eoconsultores/sql/');
        if ($result < 0) {
            return $result;
        }

        return $this->_init($sql, $options);
    }

    /**
     * Remove module
     *
     * @param string $options
     * @return int
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }
}
