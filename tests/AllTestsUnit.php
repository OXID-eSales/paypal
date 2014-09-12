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

require_once 'PHPUnit/Framework/TestSuite.php';

echo "=========\nrunning php version " . phpversion() . "\n\n============\n";

/**
 * PHPUnit_Framework_TestCase implementation for adding and testing all unit tests from unit dir
 */
class AllTestsUnit extends PHPUnit_Framework_TestCase
{
    static function suite()
    {
        $oSuite = new PHPUnit_Framework_TestSuite('PHPUnit');
        $sFilter = getenv("PREG_FILTER");

        $aTestGroups = array(
            'unit/oepaypal' => array('', 'components', 'core', 'models', 'models/actions', 'models/responses', 'models/paypalrequest', 'controllers', 'controllers/admin'),
            'integration/oepaypal' => array('', 'checkoutrequest')
        );
        if (getenv('TEST_DIRS')) {
            $sTestDir['unit/oepaypal'] = explode('%', getenv('TEST_DIRS'));
        }
        foreach ($aTestGroups as $sGroupDir => $aTestDirs) {
            foreach ($aTestDirs as $sTestDir) {
                if ($sTestDir == '_root_') {
                    $sTestDir = '';
                }
                $sTestDir = rtrim($sGroupDir . '/' . $sTestDir, '/');
                echo $sTestDir . "\n";

                if (!is_dir($sTestDir)) {
                    continue;
                }

                //adding UNIT Tests
                echo "Adding tests from $sTestDir/*Test.php\n";
                foreach (glob("$sTestDir/*Test.php") as $sFilename) {
                    if (!$sFilter || preg_match("&$sFilter&i", $sFilename)) {
                        include_once $sFilename;

                        $sClassName = str_replace(array("/", ".php"), array("_", ""), $sFilename);

                        if (class_exists($sClassName)) {
                            $oSuite->addTestSuite($sClassName);
                        } else {
                            echo "\n\nWarning: class not found: $sClassName in $sFilename\n\n\n ";
                        }
                    } else {
                        echo "skiping $sFilename\n";
                    }
                }
            }
        }

        return $oSuite;
    }
}
