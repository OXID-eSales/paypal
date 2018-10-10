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

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\ViewConfig;

/**
 * Testing \OxidEsales\PayPalModule\Core\ViewConfig class.
 */
class ViewConfigTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("delete from oxpayments where OXID = 'oxidpaypal' ");

        parent::tearDown();
    }

    /**
     * Test case for ViewConfig::isStandardCheckoutEnabled()
     */
    public function testIsStandardCheckoutEnabled()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['isStandardCheckoutEnabled']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->once())->method("isStandardCheckoutEnabled")->will($this->returnValue(true));

        $mockBuilder = $this->getMockBuilder(ViewConfig::class);
        $mockBuilder->setMethods(['getPayPalConfig']);
        $mockBuilder->setConstructorArgs([$payPalConfig, null, null]);
        $view = $mockBuilder->getMock();
        $view->expects($this->once())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $this->assertTrue($view->isStandardCheckoutEnabled());
    }

    /**
     * Test case for ViewConfig::isExpressCheckoutEnabledInDetails()
     */
    public function testIsExpressCheckoutEnabledCheckoutIsEnabledTrue()
    {
        $this->getConfig()->setConfigParam('blOEPayPalExpressCheckout', true);
        $view = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);

        $validator = $this->_createStub(\OxidEsales\PayPalModule\Model\PaymentValidator::class, array('isPaymentValid' => true));
        $view->setPaymentValidator($validator);

        $this->assertTrue($view->isExpressCheckoutEnabled());
    }

    /**
     * Test case for ViewConfig::isExpressCheckoutEnabledInDetails()
     */
    public function testIsExpressCheckoutEnabledWhenCheckoutIsDisabled()
    {
        $this->getConfig()->setConfigParam('blOEPayPalExpressCheckout', false);
        $view = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);

        $validator = $this->_createStub(\OxidEsales\PayPalModule\Model\PaymentValidator::class, array('isPaymentValid' => true));
        $view->setPaymentValidator($validator);

        $this->assertFalse($view->isExpressCheckoutEnabled());
    }

    /**
     * Test case for ViewConfig::isExpressCheckoutEnabledInDetails()
     */
    public function testIsExpressCheckoutEnabledWhenPaymentNotValid()
    {
        $this->getConfig()->setConfigParam('blOEPayPalExpressCheckout', true);
        $view = oxNew(\OxidEsales\PayPalModule\Core\ViewConfig::class);

        $validator = $this->_createStub(\OxidEsales\PayPalModule\Model\PaymentValidator::class, array('isPaymentValid' => false));
        $view->setPaymentValidator($validator);

        $this->assertFalse($view->isExpressCheckoutEnabled());
    }

    /**
     * Test case for ViewConfig::isExpressCheckoutEnabledInDetails()
     */
    public function testIsExpressCheckoutEnabledInDetailsWhenExpressCheckoutIsEnabled()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ViewConfig::class);
        $mockBuilder->setMethods(['isExpressCheckoutEnabled']);
        $view = $mockBuilder->getMock();
        $view->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(true));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', true);

        $this->assertTrue($view->isExpressCheckoutEnabledInDetails());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', false);
        $this->assertFalse($view->isExpressCheckoutEnabledInDetails());
    }

    /**
     * Test case for ViewConfig::isExpressCheckoutEnabledInDetails()
     */
    public function testIsExpressCheckoutEnabledInDetailsWhenExpressCheckoutIsDisabled()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ViewConfig::class);
        $mockBuilder->setMethods(['isExpressCheckoutEnabled']);
        $view = $mockBuilder->getMock();
        $view->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(false));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', true);

        $this->assertFalse($view->isExpressCheckoutEnabledInDetails());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', false);
        $this->assertFalse($view->isExpressCheckoutEnabledInDetails());
    }

    /**
     * Test case for ViewConfig::isExpressCheckoutEnabledInMiniBasket()
     */
    public function testIsExpressCheckoutEnabledInMiniBasketWhenExpressCheckoutIsEnabled()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ViewConfig::class);
        $mockBuilder->setMethods(['isExpressCheckoutEnabled']);
        $view = $mockBuilder->getMock();
        $view->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(true));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', true);

        $this->assertTrue($view->isExpressCheckoutEnabledInMiniBasket());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', false);
        $this->assertFalse($view->isExpressCheckoutEnabledInMiniBasket());
    }

    /**
     * Test case for ViewConfig::isExpressCheckoutEnabledInMiniBasket()
     */
    public function testIsExpressCheckoutEnabledInMiniBasketWhenExpressCheckoutIsDisabled()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ViewConfig::class);
        $mockBuilder->setMethods(['isExpressCheckoutEnabled']);
        $view = $mockBuilder->getMock();
        $view->expects($this->exactly(2))->method("isExpressCheckoutEnabled")->will($this->returnValue(false));

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', true);

        $this->assertFalse($view->isExpressCheckoutEnabledInMiniBasket());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', false);
        $this->assertFalse($view->isExpressCheckoutEnabledInMiniBasket());
    }

    /**
     * Test case for ViewConfig::getPayPalPaymentDescription()
     */
    public function testGetPayPalPaymentDescription()
    {
        $query = "INSERT INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXLONGDESC`) VALUES ('oxidpaypal', 1, 'PayPal', 'testLongDesc')";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

        $view = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
        $this->assertEquals('testLongDesc', $view->getPayPalPaymentDescription());
    }

    /**
     * Test case for ViewConfig::getPayPalPayment()
     */
    public function testGetPayPalPayment()
    {
        $query = "INSERT INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXLONGDESC`) VALUES ('oxidpaypal', 1, 'PayPal', 'testLongDesc')";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

        $view = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
        $payment = $view->getPayPalPayment();

        $this->assertTrue($payment instanceof Payment);
        $this->assertEquals("oxidpaypal", $payment->getId());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPal()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['sendOrderInfoToPayPal']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->once())->method("sendOrderInfoToPayPal")->will($this->returnValue(true));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ViewConfig::class);
        $mockBuilder->setMethods(['getPayPalConfig']);
        $mockBuilder->setConstructorArgs([$payPalConfig, null, null]);
        $view = $mockBuilder->getMock();
        $view->expects($this->once())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $this->assertTrue($view->sendOrderInfoToPayPal());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPalWhenFractionQuantityArticleIsInBasket()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSendToPayPal', true);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Article::class);
        $mockBuilder->setMethods(['getAmount']);
        $article = $mockBuilder->getMock();
        $article->expects($this->any())->method('getAmount')->will($this->returnValue(5.6));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getContents']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array($article)));

        $this->getSession()->setBasket($basket);

        $view = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
        $this->assertFalse($view->sendOrderInfoToPayPal());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPalWhenNoFractionQuantityArticleIsInBasket()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSendToPayPal', true);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Article::class);
        $mockBuilder->setMethods(['getAmount']);
        $article = $mockBuilder->getMock();
        $article->expects($this->any())->method('getAmount')->will($this->returnValue(5));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getContents']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array($article)));

        $this->getSession()->setBasket($basket);

        $view = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
        $this->assertTrue($view->sendOrderInfoToPayPal());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPalWhenBasketIsEmpty()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSendToPayPal', true);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getContents']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array()));

        $this->getSession()->setBasket($basket);

        $view = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
        $this->assertTrue($view->sendOrderInfoToPayPal());
    }

    /**
     * Checks if method returns correct current URL.
     */
    public function testGetCurrentUrl()
    {
        $cancelURL = 'http://oxid-esales.com/test';

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['getCurrentUrl']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->any())->method("getCurrentUrl")->will($this->returnValue($cancelURL));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ViewConfig::class);
        $mockBuilder->setMethods(['getPayPalConfig']);
        $viewPayPalConfig = $mockBuilder->getMock();
        $viewPayPalConfig->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));

        $this->assertEquals($cancelURL, $viewPayPalConfig->getCurrentUrl());
    }
}
