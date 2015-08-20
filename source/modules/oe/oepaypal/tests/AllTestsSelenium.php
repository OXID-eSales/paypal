<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales theme switcher module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales theme switcher module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with OXID eSales theme switcher module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

require_once 'AllTestsRunner.php';

/**
 * PHPUnit_Framework_TestCase implementation for adding and testing all selenium tests from this dir
 */
class AllTestsSelenium extends AllTestsRunner
{

    /** @var array Default test suites */
    protected static $_aTestSuites = array('acceptance/oePayPal');

    /**
     * Returns test files filter
     *
     * @return string
     */
    protected static function _getTestFileFilter()
    {
        return '*Test.php';
    }

    /**
     * Forms class name from file name.
     *
     * @param string $sFilename
     *
     * @return string
     */
    protected static function _formClassNameFromFileName($sFilename)
    {
        $sFilename = str_replace('acceptance/', '', $sFilename);
        return str_replace(array("/", ".php"), array("_", ""), $sFilename);
    }
}
