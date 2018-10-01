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
namespace OxidEsales\PayPalModule\Tests\Unit\Controller;

class StandardDispatcherTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();
        // fix for state ID compatability between editions
        $sqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) " .
                     "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sqlState);
    }

    public function testSetGetPayPalCheckoutService()
    {
        $payPalCheckoutService = new \OxidEsales\PayPalModule\Core\PayPalService();

        $payPalStandardDispatcher = new \OxidEsales\PayPalModule\Controller\StandardDispatcher();
        $payPalStandardDispatcher->setPayPalCheckoutService($payPalCheckoutService);

        $this->assertEquals($payPalCheckoutService, $payPalStandardDispatcher->getPayPalCheckoutService(), 'Getter should return what is set in setter.');
    }

    public function testGetPayPalCheckoutService()
    {
        $payPalStandardDispatcher = new \OxidEsales\PayPalModule\Controller\StandardDispatcher();

        $this->assertTrue(
            $payPalStandardDispatcher->getPayPalCheckoutService() instanceof \OxidEsales\PayPalModule\Core\PayPalService,
                'Getter should create correct type of object.'
        );
    }

    /**
     * Test case for oepaypalstandarddispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetails()
    {
        // preparing session, inputs etc.
        $this->getSession()->setVariable("oepaypal-token", "111");
        $detailsData = ["PAYERID" => "111"];
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($detailsData);

        // preparing config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['finalizeOrderOnPayPalSide']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->once())->method('finalizeOrderOnPayPalSide')->will($this->returnValue(true));

        // preparing service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['getExpressCheckoutDetails']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->returnValue($details));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\StandardDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getPayPalConfig']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));

        // testing
        $this->assertEquals("order?fnc=execute", $dispatcher->getExpressCheckoutDetails());
        $this->assertEquals("111", $this->getSession()->getVariable("oepaypal-payerId"));
    }

    /**
     * Test case for oepaypalstandarddispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetailsError()
    {
        $excp = oxNew(\OxidEsales\Eshop\Core\Exception\StandardException::class);

        // preparing config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['finalizeOrderOnPayPalSide']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->never())->method('finalizeOrderOnPayPalSide');

        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['getExpressCheckoutDetails']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->throwException($excp));

        // preparing utils view
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsView::class);
        $mockBuilder->setMethods(['addErrorToDisplay']);
        $utilsView = $mockBuilder->getMock();
        $utilsView->expects($this->once())->method("addErrorToDisplay")->with($this->equalTo($excp));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\StandardDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getPayPalConfig', 'getUtilsView']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("getUtilsView")->will($this->returnValue($utilsView));
        $dispatcher->expects($this->never())->method("getPayPalConfig");

        // testing
        $this->assertEquals("payment", $dispatcher->getExpressCheckoutDetails());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\Dispatcher::setExpressCheckout()
     * Main functionality
     */
    public function testSetExpressCheckout_onSuccess()
    {
        $result = new \OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout();
        $result->setData(array('TOKEN' => 'token'));

        $url = "https://www.sandbox.paypal.com/cgi-bin/webscr&cmd=_express-checkout&token=token";

        //utils
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class);
        $mockBuilder->setMethods(['redirect']);
        $utils = $mockBuilder->getMock();
        $utils->expects($this->once())->method("redirect")->with($this->equalTo($url), $this->equalTo(false));

        //config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['getPayPalCommunicationUrl']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->once())->method("getPayPalCommunicationUrl")->with($this->equalTo($result->getToken()))->will($this->returnValue($url));

        // preparing service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['setExpressCheckout', 'setParameter']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("setExpressCheckout")->will($this->returnValue($result));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\StandardDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getUtils', 'getPayPalConfig']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $dispatcher->expects($this->once())->method("getUtils")->will($this->returnValue($utils));

        // testing
        $dispatcher->setExpressCheckout();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\Dispatcher::setExpressCheckout()
     * On error
     */
    public function testSetExpressCheckout_onError()
    {
        $excp = oxNew(\OxidEsales\Eshop\Core\Exception\StandardException::class);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['getPaypalUrl']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->never())->method("getPaypalUrl");

        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['setExpressCheckout']);
        $payPalService= $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("setExpressCheckout")->will($this->throwException($excp));

        // preparing utils view
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsView::class);
        $mockBuilder->setMethods(['addErrorToDisplay']);
        $utilsView = $mockBuilder->getMock();
        $utilsView->expects($this->once())->method("addErrorToDisplay")->with($this->equalTo($excp));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\StandardDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getUtilsView']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("getUtilsView")->will($this->returnValue($utilsView));

        // testing
        $this->assertEquals("basket", $dispatcher->setExpressCheckout());
    }
}
