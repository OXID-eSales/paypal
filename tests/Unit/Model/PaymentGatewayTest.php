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
        $oPaymentGateway = new \OxidEsales\PayPalModule\Model\PaymentGateway();
        $oConfig = $oPaymentGateway->getPayPalConfig();

        $this->assertTrue($oConfig instanceof \OxidEsales\PayPalModule\Core\Config);
    }

    public function testGetPayPalService_notSet_service()
    {
        $oPaymentGateway = new \OxidEsales\PayPalModule\Model\PaymentGateway();
        $oService = $oPaymentGateway->getPayPalCheckoutService();

        $this->assertTrue($oService instanceof \OxidEsales\PayPalModule\Core\PayPalService);
    }

    public function testDoExpressCheckoutPayment_onSuccess_true()
    {
        // preparing price
        $oPrice = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array("getBruttoPrice"));
        $oPrice->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array("getPrice"));
        $oBasket->expects($this->once())->method("getPrice")->will($this->returnValue($oPrice));

        // preparing session
        $oSession = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array("getBasket"));
        $oSession->expects($this->any())->method("getBasket")->will($this->returnValue($oBasket));

        // preparing config
        $oPayPalConfig = $this->getMock(\OxidEsales\PayPalModule\Core\Config::class, array("getTransactionMode"));
        $oPayPalConfig->expects($this->any())->method("getTransactionMode")->will($this->returnValue("Sale"));

        // preparing order
        $oPayPalOrder = $this->getMock(\OxidEsales\PayPalModule\Model\Order::class, array("finalizePayPalOrder"));
        $oPayPalOrder->expects($this->once())->method("finalizePayPalOrder")->with($this->equalTo('Result'));

        // preparing service
        $oPayPalService = $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class, array("doExpressCheckoutPayment"));
        $oPayPalService->expects($this->any())->method("doExpressCheckoutPayment")->will($this->returnValue('Result'));

        // preparing
        $oPaymentGateway = $this->getMock(\OxidEsales\PayPalModule\Model\PaymentGateway::class, array("getPayPalCheckoutService", "getPayPalConfig", "_getPayPalOrder", "getSession", '_getPayPalUser'));
        $oPaymentGateway->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oPaymentGateway->expects($this->any())->method("_getPayPalOrder")->will($this->returnValue($oPayPalOrder));
        $oPaymentGateway->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($oPayPalConfig));
        $oPaymentGateway->expects($this->any())->method("getSession")->will($this->returnValue($oSession));
        $oPaymentGateway->expects($this->any())->method("_getPayPalUser")->will($this->returnValue(new \OxidEsales\Eshop\Application\Model\User()));

        // testing
        $this->assertTrue($oPaymentGateway->doExpressCheckoutPayment());
    }

    public function testDoExpressCheckoutPayment_onResponseError_FalseAndException()
    {
        $oException = new \OxidEsales\Eshop\Core\Exception\StandardException();

        // preparing price
        $oPrice = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array("getBruttoPrice"));
        $oPrice->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array("getPrice"));
        $oBasket->expects($this->once())->method("getPrice")->will($this->returnValue($oPrice));

        // preparing session
        $oSession = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array("getBasket"));
        $oSession->expects($this->any())->method("getBasket")->will($this->returnValue($oBasket));

        // preparing config
        $oPayPalConfig = $this->getMock(\OxidEsales\PayPalModule\Core\Config::class, array("getTransactionMode"));
        $oPayPalConfig->expects($this->any())->method("getTransactionMode")->will($this->returnValue("Sale"));

        // preparing order
        $oPayPalOrder = $this->getMock(\OxidEsales\PayPalModule\Model\Order::class, array("deletePayPalOrder"));
        $oPayPalOrder->expects($this->once())->method("deletePayPalOrder")->will($this->returnValue(true));

        // preparing service
        $oPayPalService = $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class, array("doExpressCheckoutPayment"));
        $oPayPalService->expects($this->any())->method("doExpressCheckoutPayment")->will($this->throwException($oException));

        // preparing
        $oPaymentGateway = $this->getMock(\OxidEsales\PayPalModule\Model\PaymentGateway::class, array("getPayPalCheckoutService", "getPayPalConfig", "_getPayPalOrder", "getSession", '_getPayPalUser'));
        $oPaymentGateway->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oPaymentGateway->expects($this->any())->method("_getPayPalOrder")->will($this->returnValue($oPayPalOrder));
        $oPaymentGateway->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($oPayPalConfig));
        $oPaymentGateway->expects($this->any())->method("getSession")->will($this->returnValue($oSession));
        $oPaymentGateway->expects($this->any())->method("_getPayPalUser")->will($this->returnValue(new \OxidEsales\Eshop\Application\Model\User()));

        // testing
        $this->assertFalse($oPaymentGateway->doExpressCheckoutPayment());
    }

    public function testGetPayPalOxOrder_NotSet()
    {
        $oPaymentGateway = new \OxidEsales\PayPalModule\Model\PaymentGateway();

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\Order::class, $oPaymentGateway->getPayPalOxOrder());
    }
}