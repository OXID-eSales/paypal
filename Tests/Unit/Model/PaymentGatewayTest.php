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

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\PaymentGateway;

/**
 * Testing oxAccessRightException class.
 */
class PaymentGatewayTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetPayPalConfig_notSet_config()
    {
        $paymentGateway = oxNew(PaymentGateway::class);
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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getBruttoPrice']);
        $price = $mockBuilder->getMock();
        $price->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $mockBuilder = $this->getMockBuilder(Basket::class);
        $mockBuilder->setMethods(['getPrice']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method("getPrice")->will($this->returnValue($price));

        // preparing session
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class);
        $mockBuilder->setMethods(['getBasket']);
        $session = $mockBuilder->getMock();
        $session->expects($this->any())->method("getBasket")->will($this->returnValue($basket));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        // preparing config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['getTransactionMode']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->any())->method("getTransactionMode")->will($this->returnValue("Sale"));

        // preparing order
        $mockBuilder = $this->getMockBuilder(Order::class);
        $mockBuilder->setMethods(['finalizePayPalOrder']);
        $payPalOrder = $mockBuilder->getMock();
        $payPalOrder->expects($this->once())->method("finalizePayPalOrder")->with($this->equalTo('Result'));

        // preparing service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['doExpressCheckoutPayment']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->any())->method("doExpressCheckoutPayment")->will($this->returnValue('Result'));

        // preparing
        $mockBuilder = $this->getMockBuilder(PaymentGateway::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getPayPalConfig', 'getPayPalOrder', 'getPayPalUser']);
        $paymentGateway = $mockBuilder->getMock();
        $paymentGateway->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $paymentGateway->expects($this->any())->method("getPayPalOrder")->will($this->returnValue($payPalOrder));
        $paymentGateway->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $paymentGateway->expects($this->any())->method("getPayPalUser")->will($this->returnValue(oxNew(\OxidEsales\Eshop\Application\Model\User::class)));

        // testing
        $this->assertTrue($paymentGateway->doExpressCheckoutPayment());
    }

    public function testDoExpressCheckoutPayment_onResponseError_FalseAndException()
    {
        $exception = new \OxidEsales\Eshop\Core\Exception\StandardException();

        // preparing price
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getBruttoPrice']);
        $price = $mockBuilder->getMock();
        $price->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getPrice']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method("getPrice")->will($this->returnValue($price));

        // preparing session
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class);
        $mockBuilder->setMethods(['getBasket']);
        $session = $mockBuilder->getMock();
        $session->expects($this->any())->method("getBasket")->will($this->returnValue($basket));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        // preparing config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['getTransactionMode']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->any())->method("getTransactionMode")->will($this->returnValue("Sale"));

        // preparing order
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Order::class);
        $mockBuilder->setMethods(['deletePayPalOrder']);
        $payPalOrder = $mockBuilder->getMock();
        $payPalOrder->expects($this->once())->method("deletePayPalOrder")->will($this->returnValue(true));

        // preparing service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['doExpressCheckoutPayment']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->any())->method("doExpressCheckoutPayment")->will($this->throwException($exception));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\PaymentGateway::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getPayPalConfig', 'getPayPalOrder', 'getPayPalUser']);
        $paymentGateway = $mockBuilder->getMock();
        $paymentGateway->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $paymentGateway->expects($this->any())->method("getPayPalOrder")->will($this->returnValue($payPalOrder));
        $paymentGateway->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $paymentGateway->expects($this->any())->method("getPayPalUser")->will($this->returnValue(oxNew(\OxidEsales\Eshop\Application\Model\User::class)));

        // testing
        $this->assertFalse($paymentGateway->doExpressCheckoutPayment());
    }

    public function testGetPayPalOxOrder_NotSet()
    {
        $paymentGateway = new \OxidEsales\PayPalModule\Model\PaymentGateway();

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\Order::class, $paymentGateway->getPayPalOxOrder());
    }
}