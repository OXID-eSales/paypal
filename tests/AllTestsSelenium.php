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

require_once 'PHPUnit/Framework/TestSuite.php';

define ('oxTESTSUITEDIR', 'acceptance/oepaypal');

/**
 * PHPUnit_Framework_TestCase implementation for adding and testing all selenium tests from this dir
 */
class AllTestsSelenium extends PHPUnit_Framework_TestCase
{
    /**
     * Test suite
     *
     * @return object
     */
    static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

        //adding ACCEPTANCE Tests
        if (!($sFilter = getenv('TEST_FILE_FILTER'))) {
            $sFilter = '*';
        }
        $aTestFiles = glob(oxTESTSUITEDIR . "/{$sFilter}Test.php");

        foreach ($aTestFiles as $sFilename) {
            include_once $sFilename;
            $sClassName = str_replace("/", "_", oxTESTSUITEDIR) . '_' . str_replace("/", "_", str_replace(array(".php", oxTESTSUITEDIR . '/'), "", $sFilename));
            $suite->addTestSuite($sClassName);
        }

        return $suite;
    }
}