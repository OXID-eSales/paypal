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

require_once realpath(".") . '/unit/OxidTestCase.php';
require_once realpath(".") . '/unit/test_config.inc.php';

if (!class_exists('oePayPalOxViewConfig_parent')) {
    class oePayPalOxViewConfig_parent extends oxViewConfig
    {
    }
}

if (!class_exists('oePayPalPayment_parent')) {
    class oePayPalPayment_parent extends Payment
    {
    }
}

/**
 * Testing oePayPalOxViewConfig class.
 */
class Unit_oePayPal_Core_oePayPalOxViewConfigTest extends OxidTestCase
{
    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        oxDb::getDB()->execute("delete from oxpayments where OXID = 'oxidpaypal' ");

        parent::tearDown();
    }

    /**
     * Test case for oePayPalOxViewConfig::isStandardCheckoutEnabled()
     *
     * @return null
     */
    public function testIsStandardCheckoutEnabled()
    {
        //
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("isStandardCheckoutEnabled"));
        $oPayPalConfig->expects($this->once())->method("isStandardCheckoutEnabled")->will($this->returnValue(true));

        $oView = $this->getMock("oePayPalOxViewConfig", array("_getPayPalConfig"), array($oPayPalConfig, null, null));
        $oView->expects($this->once())->method("_getPayPalConfig")->will($this->returnValue($oPayPalConfig));
        $this->assertTrue($oView->isStandardCheckoutEnabled());
    }

    /**
     * Test case for oePayPalOxViewConfig::isExpressCheckoutEnabledInDetails()
     *
     * @return null
     */
    public function testIsExpressCheckoutEnabled_CheckoutIsEnabled_True()
    {
        $this->getConfig()->setConfigParam('blOEPayPalExpressCheckout', true);
        $oView = new oePayPalOxViewConfig();

        $oValidator = $this->_createStub('oePayPalPaymentValidator', array('isPaymentValid' => true));
        $oView->setPaymentValidator($oValidator);

        $this->assertTrue($oView->isExpressCheckoutEnabled());
    }

    /**
     * Test case for oePayPalOxViewConfig::isExpressCheckoutEnabledInDetails()
     *
     * @return null
     */
    public function testIsExpressCheckoutEnabled_CheckoutIsDisabled_False()
    {
        $this->getConfig()->setConfigParam('blOEPayPalExpressCheckout', false);
        $oView = new oePayPalOxViewConfig();

        $oValidator = $this->_createStub('oePayPalPaymentValidator', array('isPaymentValid' => true));
        $oView->setPaymentValidator($oValidator);

        $this->assertFalse($oView->isExpressCheckoutEnabled());
    }

    /**
     * Test case for oePayPalOxViewConfig::isExpressCheckoutEnabledInDetails()
     *
     * @return null
     */
    public function testIsExpressCheckoutEnabled_PaymentNotValid_False()
    {
        $this->getConfig()->setConfigParam('blOEPayPalExpressCheckout', true);
        $oView = new oePayPalOxViewConfig();

        $oValidator = $this->_createStub('oePayPalPaymentValidator', array('isPaymentValid' => false));
        $oView->setPaymentValidator($oValidator);

        $this->assertFalse($oView->isExpressCheckoutEnabled());
    }

    /**
     * Test case for oePayPalOxViewConfig::isExpressCheckoutEnabledInDetails()
     *
     * @return null
     */
    public function testIsExpressCheckoutEnabledInDetails_WhenExpressCheckoutIsEnabled()
    {
        $oView = $this->getMock("oePayPalOxViewConfig", array("isExpressCheckoutEnabled"));
        $oView->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(true));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', true);

        $this->assertTrue($oView->isExpressCheckoutEnabledInDetails());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', false);
        $this->assertFalse($oView->isExpressCheckoutEnabledInDetails());
    }

    /**
     * Test case for oePayPalOxViewConfig::isExpressCheckoutEnabledInDetails()
     *
     * @return null
     */
    public function testIsExpressCheckoutEnabledInDetails_WhenExpressCheckoutIsDisabled()
    {
        $oView = $this->getMock("oePayPalOxViewConfig", array("isExpressCheckoutEnabled"));
        $oView->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(false));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', true);

        $this->assertFalse($oView->isExpressCheckoutEnabledInDetails());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', false);
        $this->assertFalse($oView->isExpressCheckoutEnabledInDetails());
    }

    /**
     * Test case for oePayPalOxViewConfig::isExpressCheckoutEnabledInMiniBasket()
     *
     * @return null
     */
    public function testIsExpressCheckoutEnabledInMiniBasket_WhenExpressCheckoutIsEnabled()
    {
        $oView = $this->getMock("oePayPalOxViewConfig", array("isExpressCheckoutEnabled"));
        $oView->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(true));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', true);

        $this->assertTrue($oView->isExpressCheckoutEnabledInMiniBasket());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', false);
        $this->assertFalse($oView->isExpressCheckoutEnabledInMiniBasket());
    }

    /**
     * Test case for oePayPalOxViewConfig::isExpressCheckoutEnabledInMiniBasket()
     *
     * @return null
     */
    public function testIsExpressCheckoutEnabledInMiniBasket_WhenExpressCheckoutIsDisabled()
    {
        $oView = $this->getMock("oePayPalOxViewConfig", array("isExpressCheckoutEnabled"));
        $oView->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(false));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', true);

        $this->assertFalse($oView->isExpressCheckoutEnabledInMiniBasket());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', false);
        $this->assertFalse($oView->isExpressCheckoutEnabledInMiniBasket());
    }

    /**
     * Test case for oePayPalOxViewConfig::getPayPalPaymentDescription()
     *
     * @return null
     */
    public function testGetPayPalPaymentDescription()
    {
        $sSql = "INSERT INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXLONGDESC`) VALUES ('oxidpaypal', 1, 'PayPal', 'testLongDesc')";
        oxDb::getDb()->execute($sSql);

        $oView = new oePayPalOxViewConfig();
        $this->assertEquals('testLongDesc', $oView->getPayPalPaymentDescription());
    }

    /**
     * Test case for oePayPalOxViewConfig::getPayPalPayment()
     *
     * @return null
     */
    public function testGetPayPalPayment()
    {
        $sSql = "INSERT INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXLONGDESC`) VALUES ('oxidpaypal', 1, 'PayPal', 'testLongDesc')";
        oxDb::getDb()->execute($sSql);

        $oView = new oePayPalOxViewConfig();
        $oPayment = $oView->getPayPalPayment();

        $this->assertTrue($oPayment instanceof oxPayment);
        $this->assertEquals("oxidpaypal", $oPayment->getId());
    }

    /**
     * Test case for oePayPalOxViewConfig::sendOrderInfoToPayPal()
     *
     * @return null
     */
    public function testSendOrderInfoToPayPal()
    {
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("sendOrderInfoToPayPal"));
        $oPayPalConfig->expects($this->once())->method("sendOrderInfoToPayPal")->will($this->returnValue(true));

        $oView = $this->getMock("oePayPalOxViewConfig", array("_getPayPalConfig"), array($oPayPalConfig, null, null));
        $oView->expects($this->once())->method("_getPayPalConfig")->will($this->returnValue($oPayPalConfig));
        $this->assertTrue($oView->sendOrderInfoToPayPal());
    }

    /**
     * Checks if method returns correct current URL.
     */
    public function testGetCurrentUrl()
    {
        $sCancelURL = 'http://oxid-esales.com/test';
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("getCurrentUrl"));
        $oPayPalConfig->expects($this->any())->method("getCurrentUrl")->will($this->returnValue($sCancelURL));

        $oViewPayPalConfig = $this->getMock("oePayPalOxViewConfig", array("_getPayPalConfig"));
        $oViewPayPalConfig->expects($this->any())->method("_getPayPalConfig")->will($this->returnValue($oPayPalConfig));

        $this->assertEquals($sCancelURL, $oViewPayPalConfig->getCurrentUrl());
    }

}