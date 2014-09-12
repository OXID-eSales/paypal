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

print("start" . "\r\n");

require_once dirname(__FILE__) . "/bootstrap.php";

error_reporting(E_ALL);
ini_set('display_errors', '1');

$oxConfig = oxRegistry::getConfig();

$sClassName = $oxConfig->getParameter("cl");
$sFunctionName = $oxConfig->getParameter('fnc');
$sOxid = $oxConfig->getParameter("oxid");
$sClassParams = $oxConfig->getParameter("classparams");

// Class and function name is must have for every action: create object, save object, delete object.
if (!$sClassName || !$sFunctionName) {
    echo "No \$sClassName or no \$sFunctionName";
    return;
}

switch (strtolower($sClassName)) {
    case "oxconfig":
        callFunctionOnConfig($sClassName, $sFunctionName, $sClassParams);
        break;

    default:
        callFunctionOnObject($sClassName, $sFunctionName, $sOxid, $sClassParams);
        break;
}

/**
 * Calls oxconfig method with passed parameters.
 * For now it is prepared for 'saveShopConfVar' method only.
 *
 * @param string $sClassName Name of class
 * @param string $sFunctionName Name of method
 * @param string $sClassParams
 *
 * @return null
 */
function callFunctionOnConfig($sClassName, $sFunctionName, $sClassParams = null)
{
    $oConfig = oxRegistry::getConfig();
    if ($sClassParams) {
        foreach ($sClassParams as $sParamKey => $aParams) {
            if ($aParams) {
                $sType = null;
                $sValue = null;
                $sModule = null;
                foreach ($aParams as $sSubParamKey => $sSubParamValue) {
                    switch ($sSubParamKey) {
                        case "type":
                            $sType = $sSubParamValue;
                            break;
                        case "value":
                            $sValue = $sSubParamValue;
                            break;
                        case "module":
                            $sModule = $sSubParamValue;
                            break;
                    }
                }
                if (isset($sType) && isset($sValue)) {
                    if ($sType == "arr") {
                        $sValue = unserialize(htmlspecialchars_decode($sValue));
                    }
                    call_user_func(array($oConfig, $sFunctionName), $sType, $sParamKey, $sValue, null, $sModule);
                    //flush cache if needed
                    $oCache = oxRegistry::get('oxReverseProxyBackend');
                    if ($oCache->isActive()) {
                        $oCache->execute();
                    }
                }
            }
        }
    }
}

/**
 * Calls object method with passed parameters.
 *
 * @param string $sClassName Name of class
 * @param string $sFunctionName Name of method
 * @param string $sOxid Oxid value
 * @param string $sClassParams
 *
 * @return null
 */
function callFunctionOnObject($sClassName, $sFunctionName, $sOxid = null, $sClassParams = null)
{
    $oObject = oxNew($sClassName);
    if (!empty($sOxid)) {
        $oObject->load($sOxid);
    }

    $sTableName = getTableNameFromClassName($sClassName);
    if ($sClassParams) {
        foreach ($sClassParams as $sParamKey => $sParamValue) {
            $sDBFieldName = $sTableName . '__' . $sParamKey;
            $oObject->$sDBFieldName = new oxField($sParamValue);
        }
    }
    call_user_func(array($oObject, $sFunctionName));
    //flush cache if needed
    $oCache = oxRegistry::get('oxReverseProxyBackend');
    if ($oCache->isActive()) {
        $oCache->execute();
    }
}

/**
 * Return table name from class name.
 * @example $sClassName = oxArticle; return oxarticles;
 * @example $sClassName = oxRole; return oxroles;
 *
 * @param string $sClassName Name of class.
 *
 * @return string
 */

function getTableNameFromClassName($sClassName)
{
    $aClassNameWithoutS = array("oxarticle", "oxrole");
    $aClassNameWithoutIes = array("oxcategory");

    $sTableName = strtolower($sClassName);
    if (in_array(strtolower($sClassName), $aClassNameWithoutS)) {
        $sTableName = strtolower($sClassName) . "s";
    } elseif (in_array(strtolower($sClassName), $aClassNameWithoutIes)) {
        $sTableName = substr(strtolower($sClassName), 0, -1) . "ies";
    }
    return $sTableName;
}

print("end" . "\r\n");