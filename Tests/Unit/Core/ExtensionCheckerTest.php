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
 * @copyright (C) OXID eSales AG 2003-2017
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
        $oChecker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $oChecker->setShopId('testShopId');
        $this->assertEquals('testShopId', $oChecker->getShopId());
    }

    /**
     * Shop id getter test, if not defined use active shop id
     */
    public function testSetGetShopId_useActiveShopId()
    {
        $oChecker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $this->assertEquals($this->getConfig()->getShopId(), $oChecker->getShopId());
    }


    /**
     * Extension id setter and getter test
     */
    public function testSetGeExtensionId_withGivenExtensionId()
    {
        $oChecker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $oChecker->setExtensionId('testExtensionId');
        $this->assertEquals('testExtensionId', $oChecker->getExtensionId());
    }

    /**
     * Testing extension not given
     */
    public function testIsActive_extensionNotSet()
    {
        $oChecker = new \OxidEsales\PayPalModule\Core\ExtensionChecker();
        $this->assertFalse($oChecker->isActive());
    }

    /**
     * Data provider for testIsActive_extensionIsSet()
     *
     * @return array
     */
    public function getExtendedClassDataProvider()
    {
        $aExtendedClasses = array(
            \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class => 'oe/oepaypal/controllers/admin/oepaypalorder_list',
            \OxidEsales\Eshop\Application\Controller\OrderController::class => \OxidEsales\PayPalModule\Controller\OrderController::class
        );
        $aExtendedClassesWith = array(
            \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class => 'oe/testExtension/controllers/admin/oepaypalorder_list',
            \OxidEsales\Eshop\Application\Controller\OrderController::class => \OxidEsales\PayPalModule\Controller\OrderController::class
        );

        $aDisabledModules = array(
            0 => 'invoicepdf',
            1 => 'oepaypal'
        );

        $aDisabledModulesWith = array(
            0 => 'invoicepdf',
            1 => 'testExtension'
        );

        return array(
            array(false, array(), array()),
            array(false, array(), $aDisabledModules),
            array(false, array(), $aDisabledModulesWith),

            array(false, $aExtendedClasses, array()),
            array(false, $aExtendedClasses, $aDisabledModules),
            array(false, $aExtendedClasses, $aDisabledModulesWith),

            array(true, $aExtendedClassesWith, array()),
            array(true, $aExtendedClassesWith, $aDisabledModules),
            array(false, $aExtendedClassesWith, $aDisabledModulesWith),
        );
    }

    /**
     * Testing is given extension active in many scenarios
     *
     * @dataProvider getExtendedClassDataProvider
     */
    public function testIsActive_extensionIsSet($blIsActive, $aExtendedClasses, $aDisabledModules)
    {
        $oChecker = $this->getMock(\OxidEsales\PayPalModule\Core\ExtensionChecker::class, array("_getExtendedClasses", '_getDisabledModules'));
        $oChecker->expects($this->any())->method("_getExtendedClasses")->will($this->returnValue($aExtendedClasses));
        $oChecker->expects($this->any())->method("_getDisabledModules")->will($this->returnValue($aDisabledModules));

        $oChecker->setExtensionId('testExtension');

        $this->assertEquals($blIsActive, $oChecker->isActive());
    }
}
