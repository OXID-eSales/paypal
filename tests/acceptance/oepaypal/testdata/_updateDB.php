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

/**
 * Full reinstall
 */

ob_start();

class _config {
    function __construct(){
        if (file_exists('_version_define.php')) {
            include_once '_version_define.php';
        }
        include "config.inc.php";
        include "core/oxconfk.php";
    }
}
$_cfg      = new _config();

$_key      = $_cfg->sConfigKey;
$oDB       = mysql_connect( 'localhost', $_cfg->dbUser, $_cfg->dbPwd);

if ($_cfg->iUtfMode) {
    mysql_query("alter schema character set utf8 collate utf8_general_ci",$oDB);
    mysql_query("set names 'utf8'",$oDB);
    mysql_query("set character_set_database=utf8",$oDB);
    mysql_query("set character set utf8",$oDB);
    mysql_query("set character_set_connection = utf8",$oDB);
    mysql_query("set character_set_results = utf8",$oDB);
    mysql_query("set character_set_server = utf8",$oDB);
} else {
    mysql_query("alter schema character set latin1 collate latin1_general_ci",$oDB);
    mysql_query("set character set latin1",$oDB);
}

mysql_select_db( $_cfg->dbName , $oDB);

$sSqlDir = dirname(__FILE__)."/seleniumSql/";
$sSqlFileName = basename($_GET["filename"]);

if ( !$sSqlFileName ) {
    echo "Error: sql file name is empty.";
    exit;
}

if ( !file_exists($sSqlDir.$sSqlFileName) ) {
    echo "Error: File <b>{$sSqlFileName}</b> not found.";
    exit;
}

passthru ('mysql -u'.$_cfg->dbUser.' -p'.$_cfg->dbPwd.' '.$_cfg->dbName.' < '.$sSqlDir.$sSqlFileName, $sRes);

if ( $sRes == 0 ) {
    header("Location: ".$_cfg->sShopURL);
} else {
    echo "Error: SQL error in file <b>{$sSqlFileName}</b>.";
}

ob_end_flush();

