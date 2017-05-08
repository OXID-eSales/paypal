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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oxAccessRightException class.
 */
class PaymentGatewayTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetPayPalConfig_notSet_config()
    {
        $paymentGateway = new \OxidEsales\PayPalModule\Model\PaymentGateway();
        $config = $paymentGateway->getPayPalConfig();

        $this->assertTrue($config instanceof \OxidEsales\PayPalModule\Core\Config);
    }

    public function testGetPayPalService_notSet_service()
    {
        $paymentGateway = new \OxidEsales\PayPalModule\Model\PaymentGateway();
        $service = $paymentGateway->getPayPalCheckoutService();

        $this->assertTrue($service instanceof \OxidEsales\PayPalModule\Core\PayPalService);
    }

    public function testDoExpressCheckoutPayment_onSuccess_true()
    {
        // preparing price
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array("getBruttoPrice"));
        $price->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $basket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array("getPrice"));
        $basket->expects($this->once())->method("getPrice")->will($this->returnValue($price));

        // preparing session
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array("getBasket"));
        $session->expects($this->any())->method("getBasket")->will($this->returnValue($basket));

        // preparing config
        $payPalConfig = $this->getMock(\OxidEsales\PayPalModule\Core\Config::class, array("getTransactionMode"));
        $payPalConfig->expects($this->any())->method("getTransactionMode")->will($this->returnValue("Sale"));

        // preparing order
        $payPalOrder = $this->getMock(\OxidEsales\PayPalModule\Model\Order::class, array("finalizePayPalOrder"));
        $payPalOrder->expects($this->once())->method("finalizePayPalOrder")->with($this->equalTo('Result'));

        // preparing service
        $payPalService = $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class, array("doExpressCheckoutPayment"));
        $payPalService->expects($this->any())->method("doExpressCheckoutPayment")->will($this->returnValue('Result'));

        // preparing
        $paymentGateway = $this->getMock(\OxidEsales\PayPalModule\Model\PaymentGateway::class, array("getPayPalCheckoutService", "getPayPalConfig", "getPayPalOrder", "getSession", 'getPayPalUser'));
        $paymentGateway->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $paymentGateway->expects($this->any())->method("getPayPalOrder")->will($this->returnValue($payPalOrder));
        $paymentGateway->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $paymentGateway->expects($this->any())->method("getSession")->will($this->returnValue($session));
        $paymentGateway->expects($this->any())->method("getPayPalUser")->will($this->returnValue(new \OxidEsales\Eshop\Application\Model\User()));

        // testing
        $this->assertTrue($paymentGateway->doExpressCheckoutPayment());
    }

    public function testDoExpressCheckoutPayment_onResponseError_FalseAndException()
    {
        $exception = new \OxidEsales\Eshop\Core\Exception\StandardException();

        // preparing price
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array("getBruttoPrice"));
        $price->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $basket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array("getPrice"));
        $basket->expects($this->once())->method("getPrice")->will($this->returnValue($price));

        // preparing session
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array("getBasket"));
        $session->expects($this->any())->method("getBasket")->will($this->returnValue($basket));

        // preparing config
        $payPalConfig = $this->getMock(\OxidEsales\PayPalModule\Core\Config::class, array("getTransactionMode"));
        $payPalConfig->expects($this->any())->method("getTransactionMode")->will($this->returnValue("Sale"));

        // preparing order
        $payPalOrder = $this->getMock(\OxidEsales\PayPalModule\Model\Order::class, array("deletePayPalOrder"));
        $payPalOrder->expects($this->once())->method("deletePayPalOrder")->will($this->returnValue(true));

        // preparing service
        $payPalService = $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class, array("doExpressCheckoutPayment"));
        $payPalService->expects($this->any())->method("doExpressCheckoutPayment")->will($this->throwException($exception));

        // preparing
        $paymentGateway = $this->getMock(\OxidEsales\PayPalModule\Model\PaymentGateway::class, array("getPayPalCheckoutService", "getPayPalConfig", "getPayPalOrder", "getSession", 'getPayPalUser'));
        $paymentGateway->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $paymentGateway->expects($this->any())->method("getPayPalOrder")->will($this->returnValue($payPalOrder));
        $paymentGateway->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $paymentGateway->expects($this->any())->method("getSession")->will($this->returnValue($session));
        $paymentGateway->expects($this->any())->method("getPayPalUser")->will($this->returnValue(new \OxidEsales\Eshop\Application\Model\User()));

        // testing
        $this->assertFalse($paymentGateway->doExpressCheckoutPayment());
    }

    public function testGetPayPalOxOrder_NotSet()
    {
        $paymentGateway = new \OxidEsales\PayPalModule\Model\PaymentGateway();

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\Order::class, $paymentGateway->getPayPalOxOrder());
    }
}