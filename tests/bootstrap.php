<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);
ini_set('display_errors', true);

chdir(dirname(__FILE__));

include_once "test_config.php";

define('oxPATH', getenv('oxPATH') ? getenv('oxPATH') : $sShopPath);

switch (getenv('OXID_VERSION') ? getenv('OXID_VERSION') : $sShopEdition) {
    case 'EE':
        define('OXID_VERSION_EE', true);
        define('OXID_VERSION_PE', false);
        define('OXID_VERSION_PE_PE', false);
        define('OXID_VERSION_PE_CE', false);
        define('OXID_VERSION_SUFIX', "_ee");
        break;
    case 'PE':
        define('OXID_VERSION_EE', false);
        define('OXID_VERSION_PE', true);
        define('OXID_VERSION_PE_PE', true);
        define('OXID_VERSION_PE_CE', false);
        define('OXID_VERSION_SUFIX', "_pe");
        break;
    case 'CE':
        define('OXID_VERSION_EE', false);
        define('OXID_VERSION_PE', true);
        define('OXID_VERSION_PE_PE', false);
        define('OXID_VERSION_PE_CE', true);
        define('OXID_VERSION_SUFIX', "_ce");
        break;

    default:
        die('bad version : ' . "'" . getenv('OXID_VERSION') . "'");
        break;
}

define ('oxCCTempDir', oxPATH . '/oxCCTempDir/');

if (!is_dir(oxCCTempDir)) {
    mkdir(oxCCTempDir, 0777, 1);
}