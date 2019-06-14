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
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Controller;

/**
 * Testing OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher class.
 */
class ExpressCheckoutDispatcherTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class);
        $mockBuilder->setMethods(['showMessageAndExit']);
        $utilsMock = $mockBuilder->getMock();
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Utils::class, $utilsMock);

        // fix for state ID compatability between editions
        $sqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) " .
                    "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sqlState);

        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Routing\ControllerClassNameResolver::class, null);
    }

    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        $_POST = [];
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Routing\ControllerClassNameResolver::class, null);

        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("DELETE FROM oxaddress WHERE OXID = '_testUserAddressId' ");

        $this->resetTestDataDeliveryCostRule();

        parent::tearDown();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::processCallBack()
     */
    public function testProcessCallBack()
    {
        // preparing service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['callbackResponse']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("callbackResponse");

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'setParamsForCallbackResponse']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("setParamsForCallbackResponse")->with($this->equalTo($payPalService));

        // testing
        $dispatcher->processCallBack();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetails()
    {
        $detailsData = [
            "SHIPPINGOPTIONNAME"   => "222",
            "PAYERID"              => "111",
            "PAYMENTREQUEST_0_AMT" => "129.00"
        ];

        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($detailsData);

        // preparing user
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['getId']);
        $user = $mockBuilder->getMock();
        $user->expects($this->any())->method("getId")->will($this->returnValue("321"));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getBruttoPrice']);
        $price = $mockBuilder->getMock();
        $price->expects($this->any())->method("getBruttoPrice")->will($this->returnValue(129.00));

        // preparing basket
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['setPayment', 'setShipping', 'calculateBasket', 'getAdditionalServicesVatPercent', 'getPrice']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method("setPayment")->with($this->equalTo("oxidpaypal"));
        $basket->expects($this->once())->method("setShipping")->with($this->equalTo("123"));
        $basket->expects($this->once())->method("calculateBasket")->with($this->equalTo(true));
        $basket->expects($this->any())->method("getAdditionalServicesVatPercent")->will($this->returnValue(0));
        $basket->expects($this->any())->method("getPrice")->will($this->returnValue($price));

        // preparing config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['finalizeOrderOnPayPalSide']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->once())->method("finalizeOrderOnPayPalSide")->will($this->returnValue(true));

        // preparing service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['getExpressCheckoutDetails']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->returnValue($details));

        // preparing session basket
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class);
        $mockBuilder->setMethods(['getBasket']);
        $session = $mockBuilder->getMock();
        $session->expects($this->once())->method("getBasket")->will($this->returnValue($basket));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        // preparing payment list
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\PaymentList::class);
        $mockBuilder->setMethods(['getPaymentList']);
        $paymentList = $mockBuilder->getMock();
        $paymentList->expects($this->once())->method("getPaymentList")->will($this->returnValue(array('oxidpaypal' => '')));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Application\Model\PaymentList::class, $paymentList);

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(
            ['getPayPalCheckoutService',
             'initializeUserData',
             'getPayPalConfig',
             'isPaymentValidForUserCountry',
             'extractShippingId']
        );
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("initializeUserData")->with($this->equalTo($details))->will($this->returnValue($user));
        $dispatcher->expects($this->once())->method("extractShippingId")->with($this->equalTo("222"), $this->equalTo($user))->will($this->returnValue("123"));
        $dispatcher->expects($this->once())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $dispatcher->expects($this->once())->method("isPaymentValidForUserCountry")->with($this->equalTo($user))->will($this->returnValue(true));

        // testing
        $this->assertEquals("order?fnc=execute", $dispatcher->getExpressCheckoutDetails());
        $this->assertEquals("111", $this->getSession()->getVariable("oepaypal-payerId"));
        $this->assertEquals("321", $this->getSession()->getVariable("oepaypal-userId"));
        $this->assertEquals("129.00", $this->getSession()->getVariable("oepaypal-basketAmount"));

        // testing current active payment
        $this->assertEquals("oxidpaypal", $this->getSession()->getVariable("paymentid"));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetails_onError_returnToBasket()
    {
        $excp = oxNew(\OxidEsales\Eshop\Core\Exception\StandardException::class);

        // preparing config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['finalizeOrderOnPayPalSide']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->never())->method("finalizeOrderOnPayPalSide");

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

        // preparing logger
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $payPalLogger = $mockBuilder->getMock();
        $payPalLogger->expects($this->once())->method("log");

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getPayPalConfig', 'getUtilsView', 'getLogger']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("getUtilsView")->will($this->returnValue($utilsView));
        $dispatcher->expects($this->once())->method("getLogger")->will($this->returnValue($payPalLogger));
        $dispatcher->expects($this->never())->method("getPayPalConfig");

        // testing
        $this->assertEquals("basket", $dispatcher->getExpressCheckoutDetails());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::getExpressCheckoutDetails()
     * Testing addition validation by country
     */
    public function testGetExpressCheckoutDetails_CountryValidationError()
    {
        $details["SHIPPINGOPTIONNAME"] = "222";
        $details["PAYERID"] = "111";

        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($details);

        // preparing config
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['finalizeOrderOnPayPalSide']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->never())->method("finalizeOrderOnPayPalSide");

        // preparing user
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        // preparing service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['getExpressCheckoutDetails']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->returnValue($details));

        // preparing utils view
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsView::class);
        $mockBuilder->setMethods(['addErrorToDisplay']);
        $utilsView = $mockBuilder->getMock();
        $utilsView->expects($this->once())->method("addErrorToDisplay")->with($this->equalTo("MESSAGE_PAYMENT_SELECT_ANOTHER_PAYMENT"));

        // preparing logger
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $payPalLogger = $mockBuilder->getMock();
        $payPalLogger->expects($this->once())->method("log");

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(
            ['getPayPalCheckoutService',
             'initializeUserData',
             'getPayPalConfig',
             'isPaymentValidForUserCountry',
             'getUtilsView',
             'getLogger']
        );
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("initializeUserData")->with($this->equalTo($details))->will($this->returnValue($user));
        $dispatcher->expects($this->once())->method("getUtilsView")->will($this->returnValue($utilsView));
        $dispatcher->expects($this->once())->method("getLogger")->will($this->returnValue($payPalLogger));
        $dispatcher->expects($this->once())->method("isPaymentValidForUserCountry")->with($this->equalTo($user))->will($this->returnValue(false));
        $dispatcher->expects($this->never())->method("getPayPalConfig");

        // testing
        $this->assertEquals("payment", $dispatcher->getExpressCheckoutDetails());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::setExpressCheckout()
     */
    public function testSetExpressCheckout_onSuccess()
    {
        $result = new \OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout();
        $result->setData(array('TOKEN' => 'token'));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['getPayPalCommunicationUrl']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->once())->method("getPayPalCommunicationUrl")->with($this->equalTo($result->getToken()))->will($this->returnValue('url+123'));

        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['setExpressCheckout', 'getRedirectUrl']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("setExpressCheckout")->will($this->returnValue($result));

        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class);
        $mockBuilder->setMethods(['redirect']);
        $utils = $mockBuilder->getMock();
        $utils->expects($this->once())->method("redirect")->with($this->equalTo("url+123"), $this->equalTo(false));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getUtils', 'getPayPalConfig']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($payPalConfig));
        $dispatcher->expects($this->once())->method("getUtils")->will($this->returnValue($utils));

        // testing
        $dispatcher->setExpressCheckout();
        $this->assertEquals("token", $this->getSession()->getVariable("oepaypal-token"));
    }

    /**
     * @return array
     */
    public function providerTestSetExpressCheckout_Error()
    {
        $data = [];

        $data['basket'] = ['post'     => ['oePayPalRequestedControllerKey' => 'basket'],
                           'resolved' => \OxidEsales\Eshop\Application\Controller\BasketController::class,
                           'expected' => 'basket'];

        $data['user'] = ['post'     => ['oePayPalRequestedControllerKey' => 'user'],
                         'resolved' => \OxidEsales\Eshop\Application\Controller\UserController::class,
                         'expected' => 'user'];

        return $data;
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::setExpressCheckout()
     *
     * @dataProvider providerTestSetExpressCheckout_Error
     *
     * @param array  $post
     * @param string $resolvedClass
     * @param string $expected
     */
    public function testSetExpressCheckout_Error($post, $resolvedClass, $expected)
    {
        $excp = oxNew(\OxidEsales\Eshop\Core\Exception\StandardException::class);

        $_POST = $post;
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Routing\ControllerClassNameResolver::class);
        $mockBuilder->setMethods(['getClassNameById']);
        $classNameResolverMock = $mockBuilder->getMock();
        $classNameResolverMock->expects($this->any())->method('getClassNameById')->will($this->returnValue($resolvedClass));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Routing\ControllerClassNameResolver::class, $classNameResolverMock);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(['getPayPalCommunicationUrl']);
        $payPalConfig = $mockBuilder->getMock();
        $payPalConfig->expects($this->never())->method("getPayPalCommunicationUrl");

        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['setExpressCheckout']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("setExpressCheckout")->will($this->throwException($excp));

        // preparing utils view
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsView::class);
        $mockBuilder->setMethods(['addErrorToDisplay']);
        $utilsView = $mockBuilder->getMock();
        $utilsView->expects($this->once())->method("addErrorToDisplay")->will($this->returnValue(null));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getUtilsView']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->once())->method("getUtilsView")->will($this->returnValue($utilsView));

        // testing
        $this->assertEquals($expected, $dispatcher->setExpressCheckout());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::processCallBack() - no user country id
     */
    public function testProcessCallBack_cancelPayment_noUserCountryId()
    {
        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['callbackResponse']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $payPalLogger = $mockBuilder->getMock();
        $payPalLogger->expects($this->at(0))->method("log");
        $payPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: NO SHIPPING COUNTRY ID"));

        // creating user without set country id
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->load("oxdefaultadmin");
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("");

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getLogger', 'getCallBackUser', 'setPayPalIsNotAvailable']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($payPalLogger));

        $dispatcher->expects($this->once())->method("getCallBackUser")->will($this->returnValue($user));
        $dispatcher->expects($this->once())->method("setPayPalIsNotAvailable");

        $dispatcher->processCallBack();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::processCallBack() - no delivery set
     */
    public function testProcessCallBack_cancelPayment_noDeliverySet()
    {
        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['callbackResponse']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $payPalLogger = $mockBuilder->getMock();
        $payPalLogger->expects($this->at(0))->method("log");
        $payPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: NO DELIVERY LIST SET"));

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->load("oxdefaultadmin");

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'getLogger', 'getCallBackUser', 'getDeliverySetList', 'setPayPalIsNotAvailable']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($payPalLogger));

        $dispatcher->expects($this->once())->method("getCallBackUser")->will($this->returnValue($user));
        $dispatcher->expects($this->once())->method("getDeliverySetList")->will($this->returnValue(null));
        $dispatcher->expects($this->once())->method("setPayPalIsNotAvailable");

        $dispatcher->processCallBack();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::processCallBack() - PayPal is not available in user country
     */
    public function testProcessCallBack_cancelPayment_noPayPalInUserCountry()
    {

        // preparing PayPal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['callbackResponse']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $payPalLogger = $mockBuilder->getMock();
        $payPalLogger->expects($this->at(0))->method("log");
        $payPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: NOT VALID COUNTRY ID"));

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->load("oxdefaultadmin");

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(
            ['getPayPalCheckoutService',
             'getLogger',
             'getCallBackUser',
             'getDeliverySetList',
             'isPaymentValidForUserCountry',
             'setPayPalIsNotAvailable']
        );
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($payPalLogger));

        $dispatcher->expects($this->once())->method("getCallBackUser")->will($this->returnValue($user));
        $dispatcher->expects($this->once())->method("getDeliverySetList")->will($this->returnValue(array(1)));
        $dispatcher->expects($this->once())->method("isPaymentValidForUserCountry")->will($this->returnValue(false));
        $dispatcher->expects($this->once())->method("setPayPalIsNotAvailable");

        $dispatcher->processCallBack();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::processCallBack() - PayPal is not in delivery list
     */
    public function testProcessCallBack_cancelPayment_noPayPalInDeliveryListSet()
    {

        // preparing paypal service
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['callbackResponse']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $payPalLogger = $mockBuilder->getMock();
        $payPalLogger->expects($this->at(0))->method("log");
        $payPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: DELIVERY SET LIST HAS NO PAYPAL"));

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->load("oxdefaultadmin");

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(
            ['getPayPalCheckoutService',
             'getLogger',
             'getCallBackUser',
             'getDeliverySetList',
             'isPaymentValidForUserCountry',
             'setDeliverySetListForCallbackResponse',
             'setPayPalIsNotAvailable']
        );
        $dispatcher= $mockBuilder->getMock();
        $dispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($payPalService));
        $dispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($payPalLogger));
        $dispatcher->expects($this->once())->method("getCallBackUser")->will($this->returnValue($user));
        $dispatcher->expects($this->once())->method("getDeliverySetList")->will($this->returnValue(array(1)));
        $dispatcher->expects($this->once())->method("isPaymentValidForUserCountry")->will($this->returnValue(true));
        $dispatcher->expects($this->once())->method("setDeliverySetListForCallbackResponse")->will($this->returnValue(0));
        $dispatcher->expects($this->once())->method("setPayPalIsNotAvailable");

        $dispatcher->processCallBack();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_initializeUserData() - new user from PayPal
     */
    public function testInitializeUserData_newPayPalUser()
    {
        $userDetails["EMAIL"] = "testUserEmail";
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($userDetails);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['isRealPayPalUser', 'createPayPalUser']);
        $user = $mockBuilder->getMock();
        $user->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testUserEmail"))->will($this->returnValue(false));
        $user->expects($this->once())->method("createPayPalUser")->with($this->equalTo($details));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $user);

        // preparing
        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();

        // testing
        $dispatcher->initializeUserData($details);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_initializeUserData() - user with same email exists in shop
     * but has different address. User are not logged in.
     */
    public function testInitializeUserData_userAlreadyExistsWithDifferentAddress()
    {
        $userDetails["EMAIL"] = "testUserEmail";
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($userDetails);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['isRealPayPalUser', 'isSamePayPalUser']);
        $user = $mockBuilder->getMock();
        $user->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testUserEmail"))->will($this->returnValue(true));
        $user->expects($this->once())->method("isSamePayPalUser")->with($this->equalTo($details))->will($this->returnValue(false));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $user);

        // setting expected exception
        $this->expectException(\OxidEsales\Eshop\Core\Exception\StandardException::class);

        // preparing
        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();

        // testing
        $dispatcher->initializeUserData($details);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_initializeUserData() - Logged in shop user, paypal returns user email
     * that  exists in shop but has different address. New user address should be created.
     */
    public function testInitializeUserData_loggedUser_addingNewAddress()
    {
        $userDetails["EMAIL"] = "testUserEmail";
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($userDetails);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['isRealPayPalUser', 'isSamePayPalUser', 'isSameAddressPayPalUser', 'isSameAddressUserPayPalUser']);
        $user = $mockBuilder->getMock();
        $user->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testLoggedUserEmail"))->will($this->returnValue("testLoggedUserId"));
        $user->expects($this->any())->method("isSameAddressPayPalUser")->with($this->equalTo($details))->will($this->returnValue(false));
        $user->expects($this->any())->method("isSameAddressUserPayPalUser")->with($this->equalTo($details))->will($this->returnValue(false));
        $user->expects($this->never())->method("isSamePayPalUser");
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field("testLoggedUserEmail");

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $user);

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['createUserAddress', 'getUser']);
        $dispatcher= $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("createUserAddress")->with($this->equalTo($details), $this->equalTo("testLoggedUserId"));
        $dispatcher->expects($this->once())->method("getUser")->will($this->returnValue($user));

        // testing
        $dispatcher->initializeUserData($details);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_initializeUserData() - Logged in shop user, paypal returns user email
     * that  exists in shop and has same address. No new user address should be created.
     */
    public function testInitializeUserData_loggedUser_sameAddress()
    {
        $userDetails["EMAIL"] = "testUserEmail";
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($userDetails);

        $this->getSession()->setVariable("deladrid", "testDelId");
        $this->assertEquals("testDelId", $this->getSession()->getVariable("deladrid"));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['isRealPayPalUser', 'isSamePayPalUser', 'isSameAddressPayPalUser', 'isSameAddressUserPayPalUser']);
        $user = $mockBuilder->getMock();
        $user->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testLoggedUserEmail"))->will($this->returnValue("testLoggedUserId"));
        $user->expects($this->once())->method("isSameAddressPayPalUser")->with($this->equalTo($details))->will($this->returnValue(true));
        $user->expects($this->once())->method("isSameAddressUserPayPalUser")->with($this->equalTo($details))->will($this->returnValue(true));
        $user->expects($this->never())->method("isSamePayPalUser");
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field("testLoggedUserEmail");

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $user);

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['createUserAddress', 'getUser']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->never())->method("createUserAddress");
        $dispatcher->expects($this->once())->method("getUser")->will($this->returnValue($user));

        // testing
        $dispatcher->initializeUserData($details);

        // delivery address id storred in session should be deleted
        $this->assertNull($this->getSession()->getVariable("deladrid"));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_setDeliverySetListForCallbackResponse()
     * No delivery sets - no params to PayPal should be setted
     */
    public function testSetDeliverySetListForCallbackResponse_noDeliverySet()
    {

        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", false);

        // preparing config
        $payPalService = new \OxidEsales\PayPalModule\Core\PayPalService();

        // preparing delyvery set
        $deliverySetList = array();

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->load("oxdefaultadmin");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getPriceForPayment']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));

        // preparing
        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $this->assertEquals(0, $dispatcher->setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket));

        $payPalParams = $payPalService->getCaller()->getParameters();

        $this->assertNull($payPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertNull($payPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertNull($payPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_setDeliverySetListForCallbackResponse()
     * PayPal not assigned to delivery set
     */
    public function testSetDeliverySetListForCallbackResponse_PayPalNotAssignedToDeliverySet()
    {
        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", false);

        // preparing config
        $payPalService = new \OxidEsales\PayPalModule\Core\PayPalService();

        // preparing delivery set
        $deliverySetList = array("oxidstandart" => "DeliverySet Name");

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getPriceForPayment']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['isPayPalInDeliverySet']);
        $dispatcher= $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("isPayPalInDeliverySet")->with($this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($user))->will($this->returnValue(false));

        $this->assertEquals(0, $dispatcher->setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket));

        $payPalParams = $payPalService->getCaller()->getParameters();

        $this->assertNull($payPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertNull($payPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertNull($payPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_setDeliverySetListForCallbackResponse()
     * No fitted deliveries found in selected delivery set
     */
    public function testSetDeliverySetListForCallbackResponse_noFittedDeliveriesInDeliverySet()
    {
        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", false);

        // preparing config
        $payPalService = new \OxidEsales\PayPalModule\Core\PayPalService();

        // preparing delivery set
        $deliverySetList = array("oxidstandart" => "DeliverySet Name");

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getPriceForPayment']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\DeliveryList::class);
        $mockBuilder->setMethods(['hasDeliveries']);
        $deliveryList = $mockBuilder->getMock();
        $deliveryList->expects($this->once())->method("hasDeliveries")->with($this->equalTo($basket), $this->equalTo($user), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart"))->will($this->returnValue(false));
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $deliveryList);

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['isPayPalInDeliverySet']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher
            ->expects($this->once())
            ->method("isPayPalInDeliverySet")
            ->with(
                $this->equalTo("oxidstandart"),
                $this->equalTo(5),
                $this->equalTo($user)
            )
            ->will($this->returnValue(true));

        $this->assertEquals(0, $dispatcher->setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket));

        $payPalParams = $payPalService->getCaller()->getParameters();

        $this->assertNull($payPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertNull($payPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertNull($payPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
    }

    /**
     * Data provider for testSetExpressCheckoutSetParameters()
     *
     * @return array
     */
    public function setDeliverySetListForCallbackResponseTest_deliveriesFitsInDeliverySet_dataProvider()
    {
        return array(array(true), array(false));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_setDeliverySetListForCallbackResponse()
     * Deliveries found in selected delivery set
     *
     * @param bool $isNetMode if net mode true
     *
     * @dataProvider setDeliverySetListForCallbackResponseTest_deliveriesFitsInDeliverySet_dataProvider
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInDeliverySet($isNetMode)
    {
        // preparing config
        $payPalService = new \OxidEsales\PayPalModule\Core\PayPalService();

        // preparing delivery set
        $deliverySetList = array("oxidstandart" => "DeliverySet Name");

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(
            ['getPriceForPayment',
             'getAdditionalServicesVatPercent',
             'isCalculationModeNetto',
             'getPayPalBasketVatValue']
        );
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));
        $basket->expects($this->once())->method('getAdditionalServicesVatPercent')->will($this->returnValue(0));
        $basket->expects($this->any())->method('isCalculationModeNetto')->will($this->returnValue($isNetMode));
        $basket->expects($this->any())->method('getPayPalBasketVatValue')->will($this->returnValue(13.12));

        // preparing delivery
        $price = oxNew(\OxidEsales\Eshop\Core\Price::class);
        $price->setPrice(27);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Delivery::class);
        $mockBuilder->setMethods(['getDeliveryPrice']);
        $delivery = $mockBuilder->getMock();
        $delivery->expects($this->once())->method('getDeliveryPrice')->with($this->equalTo(0))->will($this->returnValue($price));
        $deliveryList = array($delivery);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\DeliveryList::class);
        $mockBuilder->setMethods(['hasDeliveries', 'getDeliveryList']);
        $deliveryListProvider = $mockBuilder->getMock();
        $deliveryListProvider->expects($this->once())->method("hasDeliveries")->with($this->equalTo($basket), $this->equalTo($user), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart"))->will($this->returnValue(true));
        $deliveryListProvider->expects($this->once())->method("getDeliveryList")->with($this->equalTo($basket), $this->equalTo($user), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart"))->will($this->returnValue($deliveryList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $deliveryListProvider);

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['isPayPalInDeliverySet']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("isPayPalInDeliverySet")->with($this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($user))->will($this->returnValue(true));

        $this->assertEquals(1, $dispatcher->setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket));

        $payPalParams = $payPalService->getCaller()->getParameters();

        if ($isNetMode) {
            $this->assertEquals(13.12, $payPalParams["L_TAXAMT0"]);
        } else {
            $this->assertEquals(0, $payPalParams["L_TAXAMT0"]);
        }

        $this->assertEquals("DeliverySet Name", $payPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $payPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertEquals(27, $payPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
        $this->assertEquals('true', $payPalParams["L_SHIPPINGOPTIONISDEFAULT0"]);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_setDeliverySetListForCallbackResponse()
     * Deliveries found in selected delivery set - more than one delivery set fits.
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInMultipleDeliverySet()
    {
        // preparing config
        $payPalService = new \OxidEsales\PayPalModule\Core\PayPalService();

        // preparing delivery set
        $deliverySetList = array("oxidstandart" => "DeliverySet Name", "oxidstandart2" => "DeliverySet Name 2");

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        // setting basket delivery set
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(
            ['getPriceForPayment',
             'getShippingId',
             'getAdditionalServicesVatPercent',
             'getPayPalBasketVatValue']
        );
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));
        $basket->expects($this->once())->method('getShippingId')->will($this->returnValue("oxidstandart2"));
        $basket->expects($this->once())->method('getAdditionalServicesVatPercent')->will($this->returnValue(0));
        $basket->expects($this->any())->method('getPayPalBasketVatValue');

        // preparing delivery
        $price = new \OxidEsales\Eshop\Core\Price();
        $price->setPrice(27);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Delivery::class);
        $mockBuilder->setMethods(['getDeliveryPrice']);
        $delivery = $mockBuilder->getMock();
        $delivery->expects($this->exactly(2))->method('getDeliveryPrice')->with($this->equalTo(0))->will($this->returnValue($price));
        $deliveryList = array($delivery);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\DeliveryList::class);
        $mockBuilder->setMethods(['hasDeliveries', 'getDeliveryList']);
        $deliveryListProvider = $mockBuilder->getMock();
        $deliveryListProvider->expects($this->exactly(2))->method("hasDeliveries")->will($this->returnValue(true));
        $deliveryListProvider->expects($this->exactly(2))->method("getDeliveryList")->will($this->returnValue($deliveryList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $deliveryListProvider);

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['isPayPalInDeliverySet']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->exactly(2))->method("isPayPalInDeliverySet")->will($this->returnValue(true));

        $this->assertEquals(2, $dispatcher->setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket));

        $payPalParams = $payPalService->getCaller()->getParameters();

        $this->assertEquals("DeliverySet Name", $payPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $payPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertEquals(27, $payPalParams["L_SHIPPINGOPTIONAMOUNT0"]);

        $this->assertEquals("DeliverySet Name 2", $payPalParams["L_SHIPPINGOPTIONNAME1"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $payPalParams["L_SHIPPINGOPTIONLABEL1"]);
        $this->assertEquals(27, $payPalParams["L_SHIPPINGOPTIONAMOUNT1"]);

        // second shipping should be active
        $this->assertEquals('true', $payPalParams["L_SHIPPINGOPTIONISDEFAULT1"]);
    }


    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_setDeliverySetListForCallbackResponse()
     * Applying delivery VAT
     */
    public function testSetDeliverySetListForCallbackResponse_applyingDeliveryVat()
    {
        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", true);

        // preparing config
        $payPalService = new \OxidEsales\PayPalModule\Core\PayPalService();

        // preparing delivery set
        $deliverySetList = array("oxidstandart" => "DeliverySet Name");

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getPriceForPayment', 'getAdditionalServicesVatPercent', 'getPayPalBasketVatValue']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));
        $basket->expects($this->once())->method('getAdditionalServicesVatPercent')->will($this->returnValue(19));
        $basket->expects($this->any())->method('getPayPalBasketVatValue');

        // preparing delivery
        $price = new \OxidEsales\Eshop\Core\Price();
        $price->setPrice(27);

        // delivery VAT should be passed to "getDeliveryPrice" method
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Delivery::class);
        $mockBuilder->setMethods(['getDeliveryPrice']);
        $delivery = $mockBuilder->getMock();
        $delivery->expects($this->once())->method('getDeliveryPrice')->with($this->equalTo(19))->will($this->returnValue($price));
        $deliveryList = array($delivery);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\DeliveryList::class);
        $mockBuilder->setMethods(['hasDeliveries', 'getDeliveryList']);
        $deliveryListProvider = $mockBuilder->getMock();
        $deliveryListProvider->expects($this->once())->method("hasDeliveries")->will($this->returnValue(true));
        $deliveryListProvider->expects($this->once())->method("getDeliveryList")->will($this->returnValue($deliveryList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $deliveryListProvider);

        // preparing
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['isPayPalInDeliverySet']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("isPayPalInDeliverySet")->will($this->returnValue(true));

        $this->assertEquals(1, $dispatcher->setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket));

        $payPalParams = $payPalService->getCaller()->getParameters();

        $this->assertEquals("DeliverySet Name", $payPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $payPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertEquals(27, $payPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
        $this->assertEquals('true', $payPalParams["L_SHIPPINGOPTIONISDEFAULT0"]);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_getCallBackUser()
     */
    public function testGetCallBackUser()
    {
        $payPalData["SHIPTOSTREET"] = "testStreetName str. 12a";
        $payPalData["SHIPTOCITY"] = "testCity";
        $payPalData["SHIPTOSTATE"] = "SS";
        $payPalData["SHIPTOCOUNTRY"] = "US";
        $payPalData["SHIPTOZIP"] = "testZip";

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, oxNew(\OxidEsales\Eshop\Application\Model\User::class));
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, oxNew(\OxidEsales\Eshop\Application\Model\Address::class));

        // preparing
        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();

        $payPalUser = $dispatcher->getCallBackUser($payPalData);

        $this->assertTrue(is_string($payPalUser->getId()));
        $this->assertEquals('testStreetName str.', $payPalUser->oxuser__oxstreet->value);
        $this->assertEquals('12a', $payPalUser->oxuser__oxstreetnr->value);
        $this->assertEquals('testCity', $payPalUser->oxuser__oxcity->value);
        $this->assertEquals('testZip', $payPalUser->oxuser__oxzip->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $payPalUser->oxuser__oxcountryid->value);
        $this->assertEquals('333', $payPalUser->oxuser__oxstateid->value);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_getPayPalUser()
     * No user id setted to session
     */
    public function testGetPayPalUser_noUserIdInSession()
    {
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, oxNew(\OxidEsales\Eshop\Application\Model\User::class));

        // setting user id to session
        $this->setSessionParam("oepaypal-userId", null);

        $testUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $testUser->setId("testUserId");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getUser']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->once())->method("getUser")->will($this->returnValue($testUser));

        $payPalUser = $dispatcher->getPayPalUser();
        $this->assertEquals("testUserId", $payPalUser->getId());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_getPayPalUser()
     */
    public function testGetPayPalUser()
    {
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, oxNew(\OxidEsales\Eshop\Application\Model\User::class));

        // setting user id to session
        $this->setSessionParam("oepaypal-userId", "oxdefaultadmin");

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $payPalUser = $dispatcher->getPayPalUser();
        $this->assertEquals("oxdefaultadmin", $payPalUser->getId());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_extractShippingId()
     */
    public function testExtractShippingId()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Language::class);
        $mockBuilder->setMethods(['translateString']);
        $lang = $mockBuilder->getMock();
        $lang->expects($this->once())->method("translateString")->with($this->equalTo("OEPAYPAL_PRICE"))->will($this->returnValue("Price:"));
        $this->addModuleObject(\OxidEsales\Eshop\Core\Language::class, $lang);

        $payPalConfig = $this->_createStub('ePayPalConfig', array('getCharset' => 'UTF-8'));

        $deliverySetList = array("oxidstandart" => "Delivery Set Name");
        $this->setSessionParam("oepaypal-oxDelSetList", $deliverySetList);

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $dispatcher->setPayPalConfig($payPalConfig);
        $id = $dispatcher->extractShippingId("Delivery Set Name Price:", null);
        $this->assertEquals("oxidstandart", $id);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_isPaymentValidForUserCountry()
     */
    public function testIsPaymentValidForUserCountry()
    {
        $paymentCountries = array("testCountryId");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Payment::class);
        $mockBuilder->setMethods(['load', 'getCountries']);
        $payment = $mockBuilder->getMock();
        $payment->expects($this->atLeastOnce())->method("load")->with($this->equalTo("oxidpaypal"));
        $payment->expects($this->atLeastOnce())->method("getCountries")->will($this->returnValue($paymentCountries));;

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Payment::class, $payment);

        $user1 = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user1->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $user2 = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user2->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId_2");

        $user3 = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $this->assertTrue($dispatcher->isPaymentValidForUserCountry($user1));
        $this->assertFalse($dispatcher->isPaymentValidForUserCountry($user2));
        $this->assertFalse($dispatcher->isPaymentValidForUserCountry($user3));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_isPaymentValidForUserCountry()
     * No country assigned to PayPal payment
     */
    public function testIsPaymentValidForUserCountry_noAssignedCountries()
    {
        $paymentCountries = array();

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Payment::class);
        $mockBuilder->setMethods(['load', 'getCountries']);
        $payment = $mockBuilder->getMock();
        $payment->expects($this->atLeastOnce())->method("load")->with($this->equalTo("oxidpaypal"));
        $payment->expects($this->atLeastOnce())->method("getCountries")->will($this->returnValue($paymentCountries));;

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Payment::class, $payment);

        $user1 = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $this->assertTrue($dispatcher->isPaymentValidForUserCountry($user1));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_isPayPalInDeliverySet()
     */
    public function testIsPayPalInDeliverySet()
    {
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $paymentList = array("oxidpaypal" => "1", "oxidstandart" => "2");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\PaymentList::class);
        $mockBuilder->setMethods(['getPaymentList']);
        $paymentListProvider = $mockBuilder->getMock();
        $paymentListProvider->expects($this->once())->method("getPaymentList")->with($this->equalTo("testDelSetId"), $this->equalTo(5), $this->equalTo($user))->will($this->returnValue($paymentList));;

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\PaymentList::class, $paymentListProvider);

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $this->assertTrue($dispatcher->isPayPalInDeliverySet("testDelSetId", 5, $user));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_isPayPalInDeliverySet()
     * PayPal payment not assigned to delivery set
     */
    public function testIsPayPalInDeliverySet_notInList()
    {
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\PaymentList::class);
        $mockBuilder->setMethods(['getPaymentList']);
        $paymentList = $mockBuilder->getMock();
        $paymentList->expects($this->once())->method("getPaymentList")->with($this->equalTo("testDelSetId"), $this->equalTo(5), $this->equalTo($user))->will($this->returnValue($paymentList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\PaymentList::class, $paymentList);

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $this->assertFalse($dispatcher->isPayPalInDeliverySet("testDelSetId", 5, $user));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_setPayPalIsNotAvailable()
     */
    public function testSetPayPalIsNotAvailable()
    {
        $payPalService = new \OxidEsales\PayPalModule\Core\PayPalService();

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $dispatcher->setPayPalIsNotAvailable($payPalService);

        $payPalParams = $payPalService->getCaller()->getParameters();

        $this->assertEquals("61.0", $payPalParams["CALLBACKVERSION"]);
        $this->assertEquals("1", $payPalParams["NO_SHIPPING_OPTION_DETAILS"]);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::_getDeliverySetList()
     */
    public function testGetDeliverySetList()
    {
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\DeliverySetList::class);
        $mockBuilder->setMethods(['getDeliverySetList']);
        $delSetList = $mockBuilder->getMock();
        $delSetList->expects($this->once())->method("getDeliverySetList")->with($this->equalTo($user), $this->equalTo("testCountryId"))->will($this->returnValue("testValue"));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliverySetList::class, $delSetList);

        $dispatcher = new \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher();
        $this->assertEquals("testValue", $dispatcher->getDeliverySetList($user));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetailsChangedOrderTotal()
    {
        $data = array(
            'TOKEN'                                   => 'EC-3KM09768MH0883231',
            'BILLINGAGREEMENTACCEPTEDSTATUS'          => '0',
            'CHECKOUTSTATUS'                          => 'PaymentActionNotInitiated',
            'TIMESTAMP'                               => '2016-02-15T14:12:43Z',
            'CORRELATIONID'                           => '397ac1846e235',
            'ACK'                                     => 'Success',
            'VERSION'                                 => '84.0',
            'BUILD'                                   => '18308778',
            'EMAIL'                                   => 'testpp@oxideshop.dev',
            'PAYERID'                                 => 'XXXXXXXXYYYYY',
            'PAYERSTATUS'                             => 'verified',
            'FIRSTNAME'                               => 'Max',
            'LASTNAME'                                => 'Muster',
            'COUNTRYCODE'                             => 'DE',
            'SHIPTONAME'                              => 'Erna Helvetia',
            'SHIPTOSTREET'                            => 'Dorfstrasse 117',
            'SHIPTOCITY'                              => 'Oberbuchsiten',
            'SHIPTOZIP'                               => '4625',
            'SHIPTOCOUNTRYCODE'                       => 'CH',
            'SHIPTOCOUNTRYNAME'                       => 'Switzerland',
            'ADDRESSSTATUS'                           => 'Unconfirmed',
            'CURRENCYCODE'                            => 'EUR',
            'AMT'                                     => '29.90',
            'ITEMAMT'                                 => '29.90',
            'SHIPPINGAMT'                             => '0.00',
            'HANDLINGAMT'                             => '0.00',
            'TAXAMT'                                  => '0.00',
            'CUSTOM'                                  => 'Your order at PayPal Testshop in the amount of 29,90 EUR',
            'DESC'                                    => 'Your order at PayPal Testshop in the amount of 29,90 EUR',
            'INSURANCEAMT'                            => '0.00',
            'SHIPDISCAMT'                             => '0.00',
            'INSURANCEOPTIONOFFERED'                  => 'false',
            'L_NAME0'                                 => 'Kuyichi leather belt JEVER',
            'L_NUMBER0'                               => '3503',
            'L_QTY0'                                  => '1',
            'L_TAXAMT0'                               => '0.00',
            'L_AMT0'                                  => '29.90',
            'L_ITEMWEIGHTVALUE0'                      => '   0.00000',
            'L_ITEMLENGTHVALUE0'                      => '   0.00000',
            'L_ITEMWIDTHVALUE0'                       => '   0.00000',
            'L_ITEMHEIGHTVALUE0'                      => '   0.00000',
            'SHIPPINGCALCULATIONMODE'                 => 'FlatRate',
            'INSURANCEOPTIONSELECTED'                 => 'false',
            'SHIPPINGOPTIONISDEFAULT'                 => 'true',
            'SHIPPINGOPTIONAMOUNT'                    => '0.00',
            'SHIPPINGOPTIONNAME'                      => 'Standard',
            'PAYMENTREQUEST_0_CURRENCYCODE'           => 'EUR',
            'PAYMENTREQUEST_0_AMT'                    => '29.90',
            'PAYMENTREQUEST_0_ITEMAMT'                => '29.90',
            'PAYMENTREQUEST_0_SHIPPINGAMT'            => '0.00',
            'PAYMENTREQUEST_0_HANDLINGAMT'            => '0.00',
            'PAYMENTREQUEST_0_TAXAMT'                 => '0.00',
            'PAYMENTREQUEST_0_CUSTOM'                 => 'Your order at PayPal Testshop in the amount of 29,90 EUR',
            'PAYMENTREQUEST_0_DESC'                   => 'Your order at PayPal Testshop in the amount of 29,90 EUR',
            'PAYMENTREQUEST_0_INSURANCEAMT'           => '0.00',
            'PAYMENTREQUEST_0_SHIPDISCAMT'            => '0.00',
            'PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED' => 'false',
            'PAYMENTREQUEST_0_SHIPTONAME'             => 'Erna Helvetia',
            'PAYMENTREQUEST_0_SHIPTOSTREET'           => 'Dorfstrasse 117',
            'PAYMENTREQUEST_0_SHIPTOCITY'             => 'Oberbuchsiten',
            'PAYMENTREQUEST_0_SHIPTOZIP'              => '4625',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'      => 'CH',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME'      => 'Switzerland',
            'PAYMENTREQUEST_0_ADDRESSSTATUS'          => 'Unconfirmed',
            'L_PAYMENTREQUEST_0_NAME0'                => 'Kuyichi leather belt JEVER',
            'L_PAYMENTREQUEST_0_NUMBER0'              => '3503',
            'L_PAYMENTREQUEST_0_QTY0'                 => '1',
            'L_PAYMENTREQUEST_0_TAXAMT0'              => '0.00',
            'L_PAYMENTREQUEST_0_AMT0'                 => '29.90',
            'L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE0'     => '   0.00000',
            'L_PAYMENTREQUEST_0_ITEMLENGTHVALUE0'     => '   0.00000',
            'L_PAYMENTREQUEST_0_ITEMWIDTHVALUE0'      => '   0.00000',
            'L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE0'     => '   0.00000',
            'PAYMENTREQUESTINFO_0_ERRORCODE'          => '0',
        );

        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Application\Model\VatSelector::class, new modOxVatSelector);

        $article = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
        $article->disableLazyLoading();
        $article->setId(substr_replace(\OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUId(), '_', 0, 1));
        $article->oxarticles__oxprice = new \OxidEsales\Eshop\Core\Field('8.0', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->oxarticles__oxartnum = new \OxidEsales\Eshop\Core\Field('666-T-V', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->oxarticles__oxactive = new \OxidEsales\Eshop\Core\Field('1', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->save();

        $basket = oxNew(\OxidEsales\Eshop\Application\Model\Basket::class);
        $basket->addToBasket($article->getId(), 1); //8 EUR
        $this->getSession()->setBasket($basket);

        $details = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails::class);
        $details->setData($data);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['getExpressCheckoutDetails']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->any())->method('getExpressCheckoutDetails')->will($this->returnValue($details));

        $proxy = $this->getProxyClassName('\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher');
        $mockBuilder = $this->getMockBuilder($proxy);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'isPayPalPaymentValid']);
        $dispatcher = $mockBuilder->getMock();
        $dispatcher->expects($this->any())->method('isPayPalPaymentValid')->will($this->returnValue(true));
        $dispatcher->expects($this->any())->method('getPayPalCheckoutService')->will($this->returnValue($payPalService));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsView::class);
        $mockBuilder->setMethods(['addErrorToDisplay']);
        $utilsView = $mockBuilder->getMock();
        $utilsView->expects($this->once())->method('addErrorToDisplay')->with($this->equalTo('OEPAYPAL_ORDER_TOTAL_HAS_CHANGED'));
        $this->addModuleObject(\OxidEsales\Eshop\Core\UtilsView::class, $utilsView);

        $this->assertSame('basket', $dispatcher->getExpressCheckoutDetails());

        $this->getSession()->setUser(null); // make sure we get the desired active user

        //proceed in normal checkout
        $basket = $this->getSession()->getBasket();

        //verify basket calculation results
        $basket->calculateBasket(true);
        $this->assertSame(6.72, $basket->getNettoSum());
        $this->assertSame(6.72, $basket->getBruttoSum()); //no VAT for Switzerland

        //Change to german address, verify VAT for Germany is charged
        $this->changeUser();

        //during regular checkout, shop will work with a new instance of oxVatSelector
        //Make sure we use one with clean cache here
        $vatSelector = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Application\Model\VatSelector::class);
        $vatSelector->cleanInstanceCache();

        $basket = $this->getSession()->getBasket();
        $basket->calculateBasket(true);
        $this->assertSame(6.72, $basket->getNettoSum());
        $this->assertSame(8.0, $basket->getBruttoSum());
    }

    /**
     * Test helper, change user to german address.
     */
    private function changeUser()
    {
        //now change the user address
        $rawValues = array('oxuser__oxfname'     => 'Erna',
                           'oxuser__oxlname'     => 'Hahnentritt',
                           'oxuser__oxstreetnr'  => '117',
                           'oxuser__oxstreet'    => 'Landstrasse',
                           'oxuser__oxzip'       => '22769',
                           'oxuser__oxcity'      => 'Hamburg',
                           'oxuser__oxcountryid' => 'a7c40f631fc920687.20179984');

        $this->setRequestParameter('invadr', $rawValues);
        $this->setRequestParameter('stoken', $this->getSession()->getSessionChallengeToken());

        $userComponent = oxNew(\OxidEsales\Eshop\Application\Component\UserComponent::class);
        $this->assertSame('payment', $userComponent->changeUser());
    }

    /**
     * Test if PayPal Express Checkout dispatcher is able to correctly calculate Total Sum of given basket
     *
     * The issue was triggered when a shipping cost was taken into account and no user was provided.
     *
     * Test covers the case of bug #6565, more information: This test case was written to cover the fix for the bug #6565.
     *
     * @covers \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetailsIsAbleToCalculateCorrectTotalSumOfBasket()
    {
        $productPrice = 8;
        $shippingCost = 100;

        $paypalExpressResponseData = array(
            'EMAIL'                              => 'testpp@oxideshop.dev',
            'PAYMENTREQUEST_0_AMT'               => $productPrice + $shippingCost,
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'DE',
        );

        $article = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
        $article->disableLazyLoading();
        $article->setId('_test_article_for_paypal');
        $article->oxarticles__oxprice = new \OxidEsales\Eshop\Core\Field($productPrice, \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->oxarticles__oxartnum = new \OxidEsales\Eshop\Core\Field('1001', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->oxarticles__oxactive = new \OxidEsales\Eshop\Core\Field('1', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->save();

        $this->prepareFixedPriceShippingCostRuleForPayPal($shippingCost);

        $basket = oxNew(\OxidEsales\Eshop\Application\Model\Basket::class);
        $basket->addToBasket($article->getId(), 1);
        $this->getSession()->setBasket($basket);

        $paypalExpressResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails::class);
        $paypalExpressResponse->setData($paypalExpressResponseData);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['getExpressCheckoutDetails']);
        $paypalServiceStub = $mockBuilder->getMock();
        $paypalServiceStub->expects($this->any())->method('getExpressCheckoutDetails')->will($this->returnValue($paypalExpressResponse));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class);
        $mockBuilder->setMethods(['getPayPalCheckoutService', 'isPayPalPaymentValid']);
        $paypalExpressCheckoutDispatcherPartialStub = $mockBuilder->getMock();
        $paypalExpressCheckoutDispatcherPartialStub->expects($this->any())->method('isPayPalPaymentValid')->will($this->returnValue(true));
        $paypalExpressCheckoutDispatcherPartialStub->expects($this->any())->method('getPayPalCheckoutService')->will($this->returnValue($paypalServiceStub));

        /** @var \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher $paypalExpressCheckoutDispatcherPartialStub */
        $controllerNameWhichIndicatesSuccess = 'order?fnc=execute';
        $controllerNameFromPaypalExpressCheckout = $paypalExpressCheckoutDispatcherPartialStub->getExpressCheckoutDetails();

        $messageToCoverBugFix = 'Something went wrong during the calculation of Total sum for active basket. ' .
                                'This test case was written to cover the fix for the bug #6565. ' .
                                'More contextual information could be found at: https://bugs.oxid-esales.com/view.php?id=6565';
        $messageForWrongControllerName = 'The expected controller name "order" was not provided by PayPal Express ' .
                                         'Checkout process. ' . $messageToCoverBugFix;

        $this->assertSame(
            $controllerNameWhichIndicatesSuccess,
            $controllerNameFromPaypalExpressCheckout,
            $messageForWrongControllerName
        );

        $expectedTotalBasketSum = $productPrice + $shippingCost;
        $paypalExpressTotalBasketSum = $this->getSession()->getVariable("oepaypal-basketAmount");
        $messageForWrongBasketTotal = 'The Total sum of basket from PayPal Express Checkout does not match the ' .
                                      'expected one. ' . $messageToCoverBugFix;

        $this->assertSame((float) $expectedTotalBasketSum, $paypalExpressTotalBasketSum, $messageForWrongBasketTotal);
    }

    /**
     * Mock an object which is created by oxNew.
     *
     * Attention: please don't use this method, we want to get rid of it - all places can, and should, be replaced
     *            with plain mocks.
     *
     * Hint: see also Unit/Model/UserTest
     *
     * @param string $className The name under which the object will be created with oxNew.
     * @param object $object    The mocked object oxNew should return instead of the original one.
     */
    protected function addModuleObject($className, $object)
    {
        \OxidEsales\Eshop\Core\Registry::set($className, null);
        \OxidEsales\Eshop\Core\UtilsObject::setClassInstance($className, $object);
    }

    /**
     * Helper to add shipping cost rule into the database
     *
     * @param int|float $shippingCost
     */
    private function prepareFixedPriceShippingCostRuleForPayPal($shippingCost)
    {
        $this->deactivateTestDataDeliveryCostRule();

        $deliveryCostRule = oxNew(\OxidEsales\Eshop\Application\Model\Delivery::class);
        $deliveryCostRule->setId('_fixed_price_for_paypal_test');
        $deliveryCostRule->oxdelivery__oxactive = new \OxidEsales\Eshop\Core\Field(1, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxtitle = new \OxidEsales\Eshop\Core\Field('Fixed price for PayPal test', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxaddsumtype = new \OxidEsales\Eshop\Core\Field('abs', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxaddsum = new \OxidEsales\Eshop\Core\Field($shippingCost, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxdeltype = new \OxidEsales\Eshop\Core\Field('p', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxparam = new \OxidEsales\Eshop\Core\Field(0, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxparamend = new \OxidEsales\Eshop\Core\Field(1000, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->save();

        $deliveryCostRelation = oxNew(\OxidEsales\Eshop\Core\Model\BaseModel::class);
        $deliveryCostRelation->init('oxdel2delset');
        $deliveryCostRelation->setId('_fixed_price_2_oxidstandard');
        $deliveryCostRelation->oxdel2delset__oxdelid = new \OxidEsales\Eshop\Core\Field($deliveryCostRule->getId(), \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRelation->oxdel2delset__oxdelsetid = new \OxidEsales\Eshop\Core\Field('oxidstandard', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRelation->save();
    }

    /**
     * Cause the delivery costs come out of the database sometimes in different order (which makes a test fail),
     * we deactivate this one from the test data.
     */
    protected function deactivateTestDataDeliveryCostRule()
    {
        $deliveryCostRule = oxNew(\OxidEsales\Eshop\Application\Model\Delivery::class);

        $deliveryCostRule->setId('1b842e73470578914.54719298');
        $deliveryCostRule->oxdelivery__oxactive = new \OxidEsales\Eshop\Core\Field(0, \OxidEsales\Eshop\Core\Field::T_RAW);

        $deliveryCostRule->save();
    }

    /**
     * Cause the delivery costs come out of the database sometimes in different order (which makes a test fail),
     * we deactivated this one from the test data. With this method we clean up and activate it again.
     */
    protected function resetTestDataDeliveryCostRule()
    {
        $deliveryCostRule = oxNew(\OxidEsales\Eshop\Application\Model\Delivery::class);

        $deliveryCostRule->setId('1b842e73470578914.54719298');
        $deliveryCostRule->oxdelivery__oxactive = new \OxidEsales\Eshop\Core\Field(1, \OxidEsales\Eshop\Core\Field::T_RAW);

        $deliveryCostRule->save();
    }
}

class modOxVatSelector extends \OxidEsales\Eshop\Application\Model\VatSelector
{
    public static function cleanInstanceCache()
    {
        self::$_aUserVatCache = array();
    }
}
