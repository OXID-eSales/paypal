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

/**
 * This script unsets all domain cookies and cache
 */
require 'bootstrap.php';
/**
 * Delete all files and dirs recursively
 *
 * @param string $dir directory to delete
 *
 * @return null
 */
function rrmdir($dir)
{
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file)) {
            rrmdir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dir);
}

if ($sCompileDir = oxRegistry::get('oxConfigFile')->getVar('sCompileDir')) {
    foreach (glob($sCompileDir . "/*") as $file) {
        if (is_dir($file)) {
            rrmdir($file);
        } else {
            unlink($file);
        }
    }
}

if (OXID_VERSION_EE) :
    if (class_exists('oxReverseProxyBackEnd')) {
        // Clean cache
        if ($sCacheDir = oxRegistry::get('oxConfigFile')->getVar('sCacheDir')) {
            foreach (glob($sCacheDir . "/*") as $filename) {
                if (is_file($filename)) {
                    unlink($filename);
                }
                if (is_dir($filename)) {
                    rmdir($filename);
                }
            }
        }

        // Flush cache
        $oCache = oxNew('oxCacheBackend');
        $oCache->flush();

        // Invalidate reverse cache
        $oReverseProxy = oxNew('oxReverseProxyBackEnd');
        $oReverseProxy->setFlush();
        $oReverseProxy->execute();
    }
endif;

// Clean tmp
if (isset($_SERVER['HTTP_COOKIE'])) {
    $aCookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach ($aCookies as $sCookie) {
        $sRawCookie = explode('=', $sCookie);
        setcookie(trim($sRawCookie[0]), '', time() - 10000, '/');
    }
    // removing sid that created by clearing cache
    setcookie('sid', '', time() - 10000, '/');
}

if (!isset($_GET['no_redirect'])) {
    header("Location: " . dirname($_SERVER['REQUEST_URI']));
}