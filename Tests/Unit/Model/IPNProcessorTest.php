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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oePayPalIPNRequestVerifier class.
 */
class IPNProcessorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSetGetRequest()
    {
        $requestSet = new \OxidEsales\PayPalModule\Core\Request();

        $processor = new \OxidEsales\PayPalModule\Model\IPNProcessor();
        $processor->setRequest($requestSet);

        $this->assertEquals($requestSet, $processor->getRequest(), 'Getter should return what is set in setter.');
    }

    public function testSetGetLang()
    {
        $lang = oxNew(\OxidEsales\Eshop\Core\Language::class);
        $processor = new \OxidEsales\PayPalModule\Model\IPNProcessor();
        $processor->setLang($lang);

        $this->assertEquals($lang, $processor->getLang(), 'Getter should return what is set in setter.');
    }

    public function testSetGetPaymentBuilder()
    {
        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();

        $processor = new \OxidEsales\PayPalModule\Model\IPNProcessor();
        $processor->setPaymentBuilder($paymentBuilder);

        $this->assertEquals($paymentBuilder, $processor->getPaymentBuilder(), 'Getter should return what is set in setter.');
    }

    public function testGetPaymentBuilder()
    {
        $processor = new \OxidEsales\PayPalModule\Model\IPNProcessor();

        $this->assertTrue($processor->getPaymentBuilder() instanceof \OxidEsales\PayPalModule\Model\IPNPaymentBuilder, 'Getter should create payment builder if nothing is set.');
    }

    public function testSetGetOrderManager()
    {
        $orderManager = new \OxidEsales\PayPalModule\Model\OrderManager();

        $processor = new \OxidEsales\PayPalModule\Model\IPNProcessor();
        $processor->setOrderManager($orderManager);

        $this->assertEquals($orderManager, $processor->getOrderManager(), 'Getter should return what is set in setter.');
    }

    public function testGetOrderManager()
    {
        $processor = new \OxidEsales\PayPalModule\Model\IPNProcessor();

        $this->assertTrue($processor->getOrderManager() instanceof \OxidEsales\PayPalModule\Model\OrderManager, 'Getter should create order manager if nothing is set.');
    }

    public function testProcess()
    {
        $orderUpdated = true;
        $lang = oxNew(\OxidEsales\Eshop\Core\Language::class);
        $request = new \OxidEsales\PayPalModule\Core\Request();
        $payment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        // Call Payment Builder with defined lang and defined request. Will return mocked payment.
        $paymentBuilder = $this->preparePaymentBuilder($lang, $request, $payment);
        // Call Order Manager with payment from payment builder. Will return if order updated == PayPal call processed.
        $payPalOrderManager = $this->prepareOrderManager($payment, $orderUpdated);

        $payPalIPNProcessor = new \OxidEsales\PayPalModule\Model\IPNProcessor();
        $payPalIPNProcessor->setLang($lang);
        $payPalIPNProcessor->setRequest($request);
        $payPalIPNProcessor->setPaymentBuilder($paymentBuilder);
        $payPalIPNProcessor->setOrderManager($payPalOrderManager);

        $this->assertEquals($orderUpdated, $payPalIPNProcessor->process(), 'Order manager decide if order updated - processed successfully.');
    }

    protected function preparePaymentBuilder($lang, $request, $payment)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\IPNPaymentBuilder::class);
        $mockBuilder->setMethods(['setLang', 'setRequest', 'buildPayment']);
        $paymentBuilder = $mockBuilder->getMock();
        $paymentBuilder->expects($this->atLeastOnce())->method('setLang')->with($lang);
        $paymentBuilder->expects($this->atLeastOnce())->method('setRequest')->with($request);
        $paymentBuilder->expects($this->atLeastOnce())->method('buildPayment')->will($this->returnValue($payment));

        return $paymentBuilder;
    }

    protected function prepareOrderManager($payment, $orderUpdated)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\OrderManager::class);
        $mockBuilder->setMethods(['setOrderPayment', 'updateOrderStatus']);
        $orderManager = $mockBuilder->getMock();
        $orderManager->expects($this->atLeastOnce())->method('setOrderPayment')->with($payment);
        $orderManager->expects($this->atLeastOnce())->method('updateOrderStatus')->will($this->returnValue($orderUpdated));

        return $orderManager;
    }
}