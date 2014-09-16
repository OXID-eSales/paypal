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
class _config
{
    function __construct()
    {
        if (file_exists('_version_define.php')) {
            include "_version_define.php";
        }
        include "config.inc.php";
        include "core/oxconfk.php";
    }
}

$_cfg = new _config();
echo "clearing tmp files...";

# recursively remove a directory
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

if ($_cfg->sCompileDir) {
    foreach (glob($_cfg->sCompileDir . '/*') as $file) {
        if (is_dir($file)) {
            rrmdir($file);
        } else {
            unlink($file);
        }
    }
}

echo " done.";