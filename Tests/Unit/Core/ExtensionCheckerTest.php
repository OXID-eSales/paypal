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
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

/**
 * Testing \OxidEsales\PayPalModule\Core\ExtensionChecker class.
 */
class ExtensionCheckerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Shop id setter and getter test
     */
    public function testSetGetShopId_withGivenShopId()
    {
        $checker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $checker->setShopId('testShopId');
        $this->assertEquals('testShopId', $checker->getShopId());
    }

    /**
     * Shop id getter test, if not defined use active shop id
     */
    public function testSetGetShopId_useActiveShopId()
    {
        $checker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $this->assertEquals($this->getConfig()->getShopId(), $checker->getShopId());
    }


    /**
     * Extension id setter and getter test
     */
    public function testSetGeExtensionId_withGivenExtensionId()
    {
        $checker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $checker->setExtensionId('testExtensionId');
        $this->assertEquals('testExtensionId', $checker->getExtensionId());
    }

    /**
     * Testing extension not given
     */
    public function testIsActive_extensionNotSet()
    {
        $checker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $this->assertFalse($checker->isActive());
    }

    /**
     * Data provider for testIsActive_extensionIsSet()
     *
     * @return array
     */
    public function getExtendedClassDataProvider()
    {
        $extendedClasses = array(
            \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class => 'oe/oepaypal/controllers/admin/oepaypalorder_list',
            \OxidEsales\Eshop\Application\Controller\OrderController::class => \OxidEsales\PayPalModule\Controller\OrderController::class
        );
        $extendedClassesWith = array(
            \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class => 'oe/testExtension/controllers/admin/oepaypalorder_list',
            \OxidEsales\Eshop\Application\Controller\OrderController::class => \OxidEsales\PayPalModule\Controller\OrderController::class
        );

        $disabledModules = array(
            0 => 'invoicepdf',
            1 => 'oepaypal'
        );

        $disabledModulesWith = array(
            0 => 'invoicepdf',
            1 => 'testExtension'
        );

        return array(
            array(false, array(), array()),
            array(false, array(), $disabledModules),
            array(false, array(), $disabledModulesWith),

            array(false, $extendedClasses, array()),
            array(false, $extendedClasses, $disabledModules),
            array(false, $extendedClasses, $disabledModulesWith),

            array(true, $extendedClassesWith, array()),
            array(true, $extendedClassesWith, $disabledModules),
            array(false, $extendedClassesWith, $disabledModulesWith),
        );
    }

    /**
     * Testing is given extension active in many scenarios
     *
     * @dataProvider getExtendedClassDataProvider
     */
    public function testIsActive_extensionIsSet($isActive, $extendedClasses, $disabledModules)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ExtensionChecker::class);
        $mockBuilder->setMethods(['getExtendedClasses', 'getDisabledModules']);
        $checker = $mockBuilder->getMock();
        $checker->expects($this->any())->method("getExtendedClasses")->will($this->returnValue($extendedClasses));
        $checker->expects($this->any())->method("getDisabledModules")->will($this->returnValue($disabledModules));

        $checker->setExtensionId('testExtension');

        $this->assertEquals($isActive, $checker->isActive());
    }
}
