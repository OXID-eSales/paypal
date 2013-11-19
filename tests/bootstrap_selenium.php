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
 * @copyright (C) OXID eSales AG 2003-2013
 */

error_reporting( (E_ALL ^ E_NOTICE) | E_STRICT );
ini_set('display_errors', true);

chdir( dirname(__FILE__));

include_once "test_config.php";

define ('OXID_PHP_UNIT', true);

define( 'shopURL', getenv('SELENIUM_TARGET')? getenv('SELENIUM_TARGET') : $sShopUrl );
define( 'oxPATH', getenv('oxPATH')? getenv('oxPATH') : $sShopPath );

define( 'hostUrl', getenv('SELENIUM_SERVER')? getenv('SELENIUM_SERVER') : $sSeleniumServerIp );
define('browserName', getenv('BROWSER_NAME')? getenv('BROWSER_NAME') : $sBrowserName );

switch ( getenv('OXID_VERSION')? getenv('OXID_VERSION') : $sShopEdition ) {
    case 'EE':
        define('OXID_VERSION_EE', true );
        define('OXID_VERSION_PE', false);
        define('OXID_VERSION_PE_PE', false );
        define('OXID_VERSION_PE_CE', false );
        break;
    case 'PE':
        define('OXID_VERSION_EE',    false);
        define('OXID_VERSION_PE',    true );
        define('OXID_VERSION_PE_PE', true );
        define('OXID_VERSION_PE_CE', false );
        break;
    case 'CE':
        define('OXID_VERSION_EE',    false);
        define('OXID_VERSION_PE',    true );
        define('OXID_VERSION_PE_PE', false );
        define('OXID_VERSION_PE_CE', true );
        break;

    default:
        die('bad version--- : '."'".getenv('OXID_VERSION')."'");
        break;
}

define('isSUBSHOP', false);

if (OXID_VERSION_EE) :
    $sShopId = 1;
endif;
if (OXID_VERSION_PE) :
    $sShopId = "oxbaseshop";
endif;
define ('oxSHOPID', $sShopId );

define ('oxCCTempDir', oxPATH.'/oxCCTempDir/');
if (!is_dir(oxCCTempDir)) {
    mkdir(oxCCTempDir, 0777, 1);
} else {
    array_map('unlink', glob(oxCCTempDir."/*"));
}

define('shopPrefix', '');

if (getenv('MODULE_PKG_DIR')) {
    define ('MODULE_PKG_DIR', getenv('MODULE_PKG_DIR'));
}

if (getenv('SHOP_REMOTE')) {
    define ('SHOP_REMOTE', getenv('SHOP_REMOTE'));
}

function getShopBasePath() {
    return oxPATH;
}

require_once 'unit/test_utils.php';

// Generic utility method file.
require_once getShopBasePath() . 'core/oxfunctions.php';

// As in new bootstrap to get db instance.
$oConfigFile = new OxConfigFile( getShopBasePath() . "config.inc.php" );

OxRegistry::set("OxConfigFile", $oConfigFile);
oxRegistry::set("oxConfig", new oxConfig());

// As in new bootstrap to get db instance.
$oDb = new oxDb();
$oDb->setConfig( $oConfigFile );
$oLegacyDb = $oDb->getDb();
OxRegistry::set( 'OxDb', $oLegacyDb );

oxConfig::getInstance();

// Utility class
require_once getShopBasePath() . 'core/oxutils.php';

// Database managing class.
require_once getShopBasePath() . 'core/adodblite/adodb.inc.php';

// Session managing class.
require_once getShopBasePath() . 'core/oxsession.php';

// config
require_once getShopBasePath() . 'core/oxconfig.php';

// dumping database for selenium tests
try {
    require_once 'acceptance/oepaypal/oxidAdditionalSeleniumFunctions.php';
    $oAdditionalFunctions = new oxidAdditionalSeleniumFunctions();
    $oAdditionalFunctions->dumpDB();
} catch (Exception $e) {
    $oAdditionalFunctions->stopTesting("Failed dumping db");
}