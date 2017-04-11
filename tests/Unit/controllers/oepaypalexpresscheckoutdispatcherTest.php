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
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * Testing oePayPalExpressCheckoutDispatcher class.
 */
class Unit_oePayPal_Controllers_oePayPalExpressCheckoutDispatcherTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();

        $oUtilsMock = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('showMessageAndExit'));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtilsMock);

        // fix for state ID compatability between editions
        $sSqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) " .
                     "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sSqlState);
    }

    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("delete from oxaddress where OXID = '_testUserAddressId' ");

        parent::tearDown();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack()
     */
    public function testProcessCallBack()
    {
        // preparing service
        $oPayPalService = $this->getMock("oePayPalService", array("callbackResponse"));
        $oPayPalService->expects($this->once())->method("callbackResponse");

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "_setParamsForCallbackResponse"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("_setParamsForCallbackResponse")->with($this->equalTo($oPayPalService));

        // testing
        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetails()
    {
        $aDetails["SHIPPINGOPTIONNAME"] = "222";
        $aDetails["PAYERID"] = "111";
        $aDetails["PAYMENTREQUEST_0_AMT"] = "129.00";

        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData($aDetails);

        // preparing useer
        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array("getId"));
        $oUser->expects($this->any())->method("getId")->will($this->returnValue("321"));

        $oPrice = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array("getBruttoPrice"));
        $oPrice->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(129.00));

        // preparing basket
        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array("setPayment", "setShipping", "calculateBasket", "getAdditionalServicesVatPercent", "getPrice"));
        $oBasket->expects($this->once())->method("setPayment")->with($this->equalTo("oxidpaypal"));
        $oBasket->expects($this->once())->method("setShipping")->with($this->equalTo("123"));
        $oBasket->expects($this->once())->method("calculateBasket")->with($this->equalTo(true));
        $oBasket->expects($this->any())->method("getAdditionalServicesVatPercent")->will($this->returnValue(0));
        $oBasket->expects($this->once())->method("getPrice")->will($this->returnValue($oPrice));

        // preparing config
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("finalizeOrderOnPayPalSide"));
        $oPayPalConfig->expects($this->once())->method("finalizeOrderOnPayPalSide")->will($this->returnValue(true));

        // preparing service
        $oPayPalService = $this->getMock("oePayPalService", array("getExpressCheckoutDetails"));
        $oPayPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->returnValue($oDetails));

        // preparing session basket
        $oSession = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array("getBasket"));
        $oSession->expects($this->once())->method("getBasket")->will($this->returnValue($oBasket));

        // preparing payment list
        $oPaymentList = $this->getMock(\OxidEsales\Eshop\Application\Model\PaymentList::class, array("getPaymentList"));
        $oPaymentList->expects($this->once())->method("getPaymentList")->will($this->returnValue(array('oxidpaypal' => '')));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Application\Model\PaymentList::class, $oPaymentList);

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "_initializeUserData", "getSession", "getPayPalConfig", "_isPaymentValidForUserCountry", "_extractShippingId"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("_initializeUserData")->with($this->equalTo($oDetails))->will($this->returnValue($oUser));
        $oDispatcher->expects($this->any())->method("getSession")->will($this->returnValue($oSession));
        $oDispatcher->expects($this->once())->method("_extractShippingId")->with($this->equalTo("222"), $this->equalTo($oUser))->will($this->returnValue("123"));
        $oDispatcher->expects($this->once())->method("getPayPalConfig")->will($this->returnValue($oPayPalConfig));
        $oDispatcher->expects($this->once())->method("_isPaymentValidForUserCountry")->with($this->equalTo($oUser))->will($this->returnValue(true));

        // testing
        $this->assertEquals("order?fnc=execute", $oDispatcher->getExpressCheckoutDetails());
        $this->assertEquals("111", $this->getSession()->getVariable("oepaypal-payerId"));
        $this->assertEquals("321", $this->getSession()->getVariable("oepaypal-userId"));
        $this->assertEquals("129.00", $this->getSession()->getVariable("oepaypal-basketAmount"));

        // testing current active payment
        $this->assertEquals("oxidpaypal", $this->getSession()->getVariable("paymentid"));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::getExpressCheckoutDetails()
     */
    public function testGetExpressCheckoutDetails_onError_returnToBasket()
    {
        $oExcp = new \OxidEsales\Eshop\Core\Exception\StandardException();

        // preparing config
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("finalizeOrderOnPayPalSide"));
        $oPayPalConfig->expects($this->never())->method("finalizeOrderOnPayPalSide");

        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("getExpressCheckoutDetails"));
        $oPayPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->throwException($oExcp));

        // preparing utils view
        $oUtilsView = $this->getMock(\OxidEsales\Eshop\Core\UtilsView::class, array("addErrorToDisplay"));
        $oUtilsView->expects($this->once())->method("addErrorToDisplay")->with($this->equalTo($oExcp));

        // preparing logger
        $oPayPalLogger = $this->getMock("oePayPalLogger", array("log"));
        $oPayPalLogger->expects($this->once())->method("log");

        // preparing
        $oDispatcher = $this->getMock("oePayPalExpressCheckoutDispatcher", array("getPayPalCheckoutService", "getPayPalConfig", "_getUtilsView", "getLogger"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("_getUtilsView")->will($this->returnValue($oUtilsView));
        $oDispatcher->expects($this->once())->method("getLogger")->will($this->returnValue($oPayPalLogger));
        $oDispatcher->expects($this->never())->method("getPayPalConfig");

        // testing
        $this->assertEquals("basket", $oDispatcher->getExpressCheckoutDetails());
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::getExpressCheckoutDetails()
     * Testing addition validation by country
     */
    public function testGetExpressCheckoutDetails_CountryValidationError()
    {
        $aDetails["SHIPPINGOPTIONNAME"] = "222";
        $aDetails["PAYERID"] = "111";

        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData($aDetails);

        // preparing config
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("finalizeOrderOnPayPalSide"));
        $oPayPalConfig->expects($this->never())->method("finalizeOrderOnPayPalSide");

        // preparing user
        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        // preparing service
        $oPayPalService = $this->getMock("oePayPalService", array("getExpressCheckoutDetails"));
        $oPayPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->returnValue($oDetails));

        // preparing utils view
        $oUtilsView = $this->getMock(\OxidEsales\Eshop\Core\UtilsView::class, array("addErrorToDisplay"));
        $oUtilsView->expects($this->once())->method("addErrorToDisplay")->with($this->equalTo("MESSAGE_PAYMENT_SELECT_ANOTHER_PAYMENT"));

        // preparing logger
        $oPayPalLogger = $this->getMock("oePayPalLogger", array("log"));
        $oPayPalLogger->expects($this->once())->method("log");

        // preparing
        $oDispatcher = $this->getMock("oePayPalExpressCheckoutDispatcher", array("getPayPalCheckoutService", "_initializeUserData", "getPayPalConfig", "_isPaymentValidForUserCountry", "_getUtilsView", "getLogger"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("_initializeUserData")->with($this->equalTo($oDetails))->will($this->returnValue($oUser));
        $oDispatcher->expects($this->once())->method("_getUtilsView")->will($this->returnValue($oUtilsView));
        $oDispatcher->expects($this->once())->method("getLogger")->will($this->returnValue($oPayPalLogger));
        $oDispatcher->expects($this->once())->method("_isPaymentValidForUserCountry")->with($this->equalTo($oUser))->will($this->returnValue(false));
        $oDispatcher->expects($this->never())->method("getPayPalConfig");

        // testing
        $this->assertEquals("payment", $oDispatcher->getExpressCheckoutDetails());
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::setExpressCheckout()
     */
    public function testSetExpressCheckout_onSuccess()
    {
        $oResult = new oePayPalResponseSetExpressCheckout();
        $oResult->setData(array('TOKEN' => 'token'));

        $oPayPalConfig = $this->getMock("oePayPalConfig", array("getPayPalCommunicationUrl"));
        $oPayPalConfig->expects($this->once())->method("getPayPalCommunicationUrl")->with($this->equalTo($oResult->getToken()))->will($this->returnValue('url+123'));

        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("setExpressCheckout", "getRedirectUrl"));
        $oPayPalService->expects($this->once())->method("setExpressCheckout")->will($this->returnValue($oResult));

        // preparing paypal service
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array("redirect"));
        $oUtils->expects($this->once())->method("redirect")->with($this->equalTo("url+123"), $this->equalTo(false));

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "_getUtils", "getPayPalConfig"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($oPayPalConfig));
        $oDispatcher->expects($this->once())->method("_getUtils")->will($this->returnValue($oUtils));

        // testing
        $oDispatcher->setExpressCheckout();
        $this->assertEquals("token", $this->getSession()->getVariable("oepaypal-token"));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::setExpressCheckout()
     */
    public function testSetExpressCheckout_Error()
    {
        $oExcp = new \OxidEsales\Eshop\Core\Exception\StandardException();

        $oPayPalConfig = $this->getMock("oePayPalConfig", array("getPayPalCommunicationUrl"));
        $oPayPalConfig->expects($this->never())->method("getPayPalCommunicationUrl");

        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("setExpressCheckout"));
        $oPayPalService->expects($this->once())->method("setExpressCheckout")->will($this->throwException($oExcp));

        // preparing utils view
        $oUtilsView = $this->getMock(\OxidEsales\Eshop\Core\UtilsView::class, array("addErrorToDisplay"));
        $oUtilsView->expects($this->once())->method("addErrorToDisplay")->will($this->returnValue(null));

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "_getUtilsView"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("_getUtilsView")->will($this->returnValue($oUtilsView));

        // testing
        $this->assertEquals("basket", $oDispatcher->setExpressCheckout());
    }

    /**
     * Data provider for testSetExpressCheckoutSetParameters()
     *
     * @return array
     */
    public function testSetExpressCheckoutSetParameters_dataProvider()
    {
        return array(array(true), array(false));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack() - no user country id
     */
    public function testProcessCallBack_cancelPayment_noUserCountryId()
    {
        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("callbackResponse"));
        $oPayPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $oPayPalLogger = $this->getMock("oePayPalLogger", array("log"));
        $oPayPalLogger->expects($this->at(0))->method("log");
        $oPayPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: NO SHIPPING COUNTRY ID"));

        // creating user without set country id
        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->load("oxdefaultadmin");
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("");

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_setPayPalIsNotAvailable"));
        $oDispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($oPayPalLogger));

        $oDispatcher->expects($this->once())->method("_getCallBackUser")->will($this->returnValue($oUser));
        $oDispatcher->expects($this->once())->method("_setPayPalIsNotAvailable");

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack() - no delivery set
     */
    public function testProcessCallBack_cancelPayment_noDeliverySet()
    {
        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("callbackResponse"));
        $oPayPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $oPayPalLogger = $this->getMock("oePayPalLogger", array("log"));
        $oPayPalLogger->expects($this->at(0))->method("log");
        $oPayPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: NO DELIVERY LIST SET"));

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->load("oxdefaultadmin");

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_getDeliverySetList", "_setPayPalIsNotAvailable"));
        $oDispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($oPayPalLogger));

        $oDispatcher->expects($this->once())->method("_getCallBackUser")->will($this->returnValue($oUser));
        $oDispatcher->expects($this->once())->method("_getDeliverySetList")->will($this->returnValue(null));
        $oDispatcher->expects($this->once())->method("_setPayPalIsNotAvailable");

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oePayPalExpressCheckoutDispatcher::processCallBack() - PayPal is not available in user country
     */
    public function testProcessCallBack_cancelPayment_noPayPalInUserCountry()
    {

        // preparing PayPal service
        $oPayPalService = $this->getMock("oePayPalService", array("callbackResponse"));
        $oPayPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $oPayPalLogger = $this->getMock("oePayPalLogger", array("log"));
        $oPayPalLogger->expects($this->at(0))->method("log");
        $oPayPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: NOT VALID COUNTRY ID"));

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->load("oxdefaultadmin");

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_getDeliverySetList", "_isPaymentValidForUserCountry", "_setPayPalIsNotAvailable"));
        $oDispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($oPayPalLogger));

        $oDispatcher->expects($this->once())->method("_getCallBackUser")->will($this->returnValue($oUser));
        $oDispatcher->expects($this->once())->method("_getDeliverySetList")->will($this->returnValue(array(1)));
        $oDispatcher->expects($this->once())->method("_isPaymentValidForUserCountry")->will($this->returnValue(false));
        $oDispatcher->expects($this->once())->method("_setPayPalIsNotAvailable");

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack() - PayPal is not in delivery list
     */
    public function testProcessCallBack_cancelPayment_noPayPalInDeliveryListSet()
    {

        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("callbackResponse"));
        $oPayPalService->expects($this->once())->method("callbackResponse");

        // preparing logger
        $oPayPalLogger = $this->getMock("oePayPalLogger", array("log"));
        $oPayPalLogger->expects($this->at(0))->method("log");
        $oPayPalLogger->expects($this->at(1))->method("log")->with($this->equalTo("Callback error: DELIVERY SET LIST HAS NO PAYPAL"));

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->load("oxdefaultadmin");

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_getDeliverySetList", "_isPaymentValidForUserCountry", "_setDeliverySetListForCallbackResponse", "_setPayPalIsNotAvailable"));
        $oDispatcher->expects($this->any())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->any())->method("getLogger")->will($this->returnValue($oPayPalLogger));

        $oDispatcher->expects($this->once())->method("_getCallBackUser")->will($this->returnValue($oUser));
        $oDispatcher->expects($this->once())->method("_getDeliverySetList")->will($this->returnValue(array(1)));
        $oDispatcher->expects($this->once())->method("_isPaymentValidForUserCountry")->will($this->returnValue(true));
        $oDispatcher->expects($this->once())->method("_setDeliverySetListForCallbackResponse")->will($this->returnValue(0));
        $oDispatcher->expects($this->once())->method("_setPayPalIsNotAvailable");

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - new user from PayPal
     */
    public function testInitializeUserData_newPayPalUser()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData($aUserDetails);

        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array("isRealPayPalUser", "createPayPalUser"));
        $oUser->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testUserEmail"))->will($this->returnValue(false));
        $oUser->expects($this->once())->method("createPayPalUser")->with($this->equalTo($oDetails));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();

        // testing
        $oDispatcher->UNITinitializeUserData($oDetails);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - user with same email exists in shop
     * but has different address. User are not logged in.
     */
    public function testInitializeUserData_userAlreadyExistsWithDifferentAddress()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData($aUserDetails);

        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array("isRealPayPalUser", "isSamePayPalUser"));
        $oUser->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testUserEmail"))->will($this->returnValue(true));
        $oUser->expects($this->once())->method("isSamePayPalUser")->with($this->equalTo($oDetails))->will($this->returnValue(false));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        // setting expected exception
        $this->setExpectedException(\OxidEsales\Eshop\Core\Exception\StandardException::class);

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();

        // testing
        $oDispatcher->UNITinitializeUserData($oDetails);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - Logged in shop user, paypal returns user email
     * that  exists in shop but has different address. New user address should be created.
     */
    public function testInitializeUserData_loggedUser_addingNewAddress()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData($aUserDetails);

        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array("isRealPayPalUser", "isSamePayPalUser", "isSameAddressPayPalUser", 'isSameAddressUserPayPalUser'));
        $oUser->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testLoggedUserEmail"))->will($this->returnValue("testLoggedUserId"));
        $oUser->expects($this->any())->method("isSameAddressPayPalUser")->with($this->equalTo($oDetails))->will($this->returnValue(false));
        $oUser->expects($this->any())->method("isSameAddressUserPayPalUser")->with($this->equalTo($oDetails))->will($this->returnValue(false));
        $oUser->expects($this->never())->method("isSamePayPalUser");
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field("testLoggedUserEmail");

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("_createUserAddress", "getUser"));
        $oDispatcher->expects($this->once())->method("_createUserAddress")->with($this->equalTo($oDetails), $this->equalTo("testLoggedUserId"));
        $oDispatcher->expects($this->once())->method("getUser")->will($this->returnValue($oUser));

        // testing
        $oDispatcher->UNITinitializeUserData($oDetails);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - Logged in shop user, paypal returns user email
     * that  exists in shop and has same address. No new user address should be created.
     */
    public function testInitializeUserData_loggedUser_sameAddress()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData($aUserDetails);

        $this->getSession()->setVariable("deladrid", "testDelId");
        $this->assertEquals("testDelId", $this->getSession()->getVariable("deladrid"));

        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array("isRealPayPalUser", "isSamePayPalUser", "isSameAddressPayPalUser", 'isSameAddressUserPayPalUser'));
        $oUser->expects($this->once())->method("isRealPayPalUser")->with($this->equalTo("testLoggedUserEmail"))->will($this->returnValue("testLoggedUserId"));
        $oUser->expects($this->once())->method("isSameAddressPayPalUser")->with($this->equalTo($oDetails))->will($this->returnValue(true));
        $oUser->expects($this->once())->method("isSameAddressUserPayPalUser")->with($this->equalTo($oDetails))->will($this->returnValue(true));
        $oUser->expects($this->never())->method("isSamePayPalUser");
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field("testLoggedUserEmail");

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("_createUserAddress", "getUser"));
        $oDispatcher->expects($this->never())->method("_createUserAddress");
        $oDispatcher->expects($this->once())->method("getUser")->will($this->returnValue($oUser));

        // testing
        $oDispatcher->UNITinitializeUserData($oDetails);

        // delivery address id storred in session should be deleted
        $this->assertNull($this->getSession()->getVariable("deladrid"));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * No delivery sets - no params to PayPal should be setted
     */
    public function testSetDeliverySetListForCallbackResponse_noDeliverySet()
    {

        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", false);

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delyvery set
        $aDeliverySetList = array();

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->load("oxdefaultadmin");

        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array('getPriceForPayment'));
        $oBasket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertEquals(0, $oDispatcher->UNITsetDeliverySetListForCallbackResponse($oPayPalService, $aDeliverySetList, $oUser, $oBasket));

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * PayPal not assigned to delivery set
     */
    public function testSetDeliverySetListForCallbackResponse_PayPalNotAssignedToDeliverySet()
    {

        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", false);

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array("oxidstandart" => "DeliverySet Name");

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array('getPriceForPayment'));
        $oBasket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet"));
        $oDispatcher->expects($this->once())->method("_isPayPalInDeliverySet")->with($this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($oUser))->will($this->returnValue(false));

        $this->assertEquals(0, $oDispatcher->UNITsetDeliverySetListForCallbackResponse($oPayPalService, $aDeliverySetList, $oUser, $oBasket));

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * No fitted deliveries found in selected delivery set
     */
    public function testSetDeliverySetListForCallbackResponse_noFittedDeliveriesInDeliverySet()
    {
        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", false);

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array("oxidstandart" => "DeliverySet Name");

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array('getPriceForPayment'));
        $oBasket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));

        $oDeliveryList = $this->getMock(\OxidEsales\Eshop\Application\Model\DeliveryList::class, array('hasDeliveries'));
        $oDeliveryList->expects($this->once())->method("hasDeliveries")->with($this->equalTo($oBasket), $this->equalTo($oUser), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart"))->will($this->returnValue(false));
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $oDeliveryList);

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet"));
        $oDispatcher->expects($this->once())->method("_isPayPalInDeliverySet")->with($this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($oUser))->will($this->returnValue(true));

        $this->assertEquals(0, $oDispatcher->UNITsetDeliverySetListForCallbackResponse($oPayPalService, $aDeliverySetList, $oUser, $oBasket));

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertNull($aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
    }

    /**
     * Data provider for testSetExpressCheckoutSetParameters()
     *
     * @return array
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInDeliverySet_dataProvider()
    {
        return array(array(true), array(false));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * Deliveries found in selected delivery set
     *
     * @param bool $blIsNettoMode if netto mode true
     *
     * @dataProvider testSetDeliverySetListForCallbackResponse_deliveriesFitsInDeliverySet_dataProvider
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInDeliverySet($blIsNettoMode)
    {
        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array("oxidstandart" => "DeliverySet Name");

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array('getPriceForPayment', 'getAdditionalServicesVatPercent', 'isCalculationModeNetto', 'getPayPalBasketVatValue'));
        $oBasket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));
        $oBasket->expects($this->once())->method('getAdditionalServicesVatPercent')->will($this->returnValue(0));
        $oBasket->expects($this->any())->method('isCalculationModeNetto')->will($this->returnValue($blIsNettoMode));
        $oBasket->expects($this->any())->method('getPayPalBasketVatValue')->will($this->returnValue(13.12));

        // preparing delivery
        $oPrice = new oxPrice();
        $oPrice->setPrice(27);

        $oDelivery = $this->getMock(\OxidEsales\Eshop\Application\Model\Delivery::class, array('getDeliveryPrice'));
        $oDelivery->expects($this->once())->method('getDeliveryPrice')->with($this->equalTo(0))->will($this->returnValue($oPrice));
        $aDeliveryList = array($oDelivery);

        $oDeliveryList = $this->getMock(\OxidEsales\Eshop\Application\Model\DeliveryList::class, array('hasDeliveries', 'getDeliveryList'));
        $oDeliveryList->expects($this->once())->method("hasDeliveries")->with($this->equalTo($oBasket), $this->equalTo($oUser), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart"))->will($this->returnValue(true));
        $oDeliveryList->expects($this->once())->method("getDeliveryList")->with($this->equalTo($oBasket), $this->equalTo($oUser), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart"))->will($this->returnValue($aDeliveryList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $oDeliveryList);

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet"));
        $oDispatcher->expects($this->once())->method("_isPayPalInDeliverySet")->with($this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($oUser))->will($this->returnValue(true));

        $this->assertEquals(1, $oDispatcher->UNITsetDeliverySetListForCallbackResponse($oPayPalService, $aDeliverySetList, $oUser, $oBasket));

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        if ($blIsNettoMode) {
            $this->assertEquals(13.12, $aPayPalParams["L_TAXAMT0"]);
        } else {
            $this->assertEquals(0, $aPayPalParams["L_TAXAMT0"]);
        }

        $this->assertEquals("DeliverySet Name", $aPayPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertEquals(27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
        $this->assertEquals('true', $aPayPalParams["L_SHIPPINGOPTIONISDEFAULT0"]);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * Deliveries found in selected delivery set - more than one delivery set fits.
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInMultipleDeliverySet()
    {
        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array("oxidstandart" => "DeliverySet Name", "oxidstandart2" => "DeliverySet Name 2");

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        // setting basket delivery set
        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array('getPriceForPayment', "getShippingId", 'getAdditionalServicesVatPercent', 'getPayPalBasketVatValue'));
        $oBasket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));
        $oBasket->expects($this->once())->method('getShippingId')->will($this->returnValue("oxidstandart2"));
        $oBasket->expects($this->once())->method('getAdditionalServicesVatPercent')->will($this->returnValue(0));
        $oBasket->expects($this->any())->method('getPayPalBasketVatValue');

        // preparing delivery
        $oPrice = new \OxidEsales\Eshop\Core\Price();
        $oPrice->setPrice(27);

        $oDelivery = $this->getMock(\OxidEsales\Eshop\Application\Model\Delivery::class, array('getDeliveryPrice'));
        $oDelivery->expects($this->exactly(2))->method('getDeliveryPrice')->with($this->equalTo(0))->will($this->returnValue($oPrice));
        $aDeliveryList = array($oDelivery);

        $oDeliveryList = $this->getMock(\OxidEsales\Eshop\Application\Model\DeliveryList::class, array('hasDeliveries', 'getDeliveryList'));
        $oDeliveryList->expects($this->exactly(2))->method("hasDeliveries")->will($this->returnValue(true));
        $oDeliveryList->expects($this->exactly(2))->method("getDeliveryList")->will($this->returnValue($aDeliveryList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $oDeliveryList);

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet"));
        $oDispatcher->expects($this->exactly(2))->method("_isPayPalInDeliverySet")->will($this->returnValue(true));

        $this->assertEquals(2, $oDispatcher->UNITsetDeliverySetListForCallbackResponse($oPayPalService, $aDeliverySetList, $oUser, $oBasket));

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertEquals("DeliverySet Name", $aPayPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertEquals(27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"]);

        $this->assertEquals("DeliverySet Name 2", $aPayPalParams["L_SHIPPINGOPTIONNAME1"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL1"]);
        $this->assertEquals(27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT1"]);

        // second shipping should be active
        $this->assertEquals('true', $aPayPalParams["L_SHIPPINGOPTIONISDEFAULT1"]);
    }


    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * Applying delivery VAT
     */
    public function testSetDeliverySetListForCallbackResponse_applyingDeliveryVat()
    {
        //disabling delivery VAT check
        $this->setConfigParam("blShowVATForDelivery", true);

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array("oxidstandart" => "DeliverySet Name");

        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array('getPriceForPayment', 'getAdditionalServicesVatPercent', 'getPayPalBasketVatValue'));
        $oBasket->expects($this->once())->method('getPriceForPayment')->will($this->returnValue(5));
        $oBasket->expects($this->once())->method('getAdditionalServicesVatPercent')->will($this->returnValue(19));
        $oBasket->expects($this->any())->method('getPayPalBasketVatValue');

        // preparing delivery
        $oPrice = new \OxidEsales\Eshop\Core\Price();
        $oPrice->setPrice(27);

        // delivery VAT should be passed to "getDeliveryPrice" method
        $oDelivery = $this->getMock(\OxidEsales\Eshop\Application\Model\Delivery::class, array('getDeliveryPrice'));
        $oDelivery->expects($this->once())->method('getDeliveryPrice')->with($this->equalTo(19))->will($this->returnValue($oPrice));
        $aDeliveryList = array($oDelivery);

        $oDeliveryList = $this->getMock(\OxidEsales\Eshop\Application\Model\DeliveryList::class, array('hasDeliveries', 'getDeliveryList'));
        $oDeliveryList->expects($this->once())->method("hasDeliveries")->will($this->returnValue(true));
        $oDeliveryList->expects($this->once())->method("getDeliveryList")->will($this->returnValue($aDeliveryList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $oDeliveryList);

        // preparing
        $oDispatcher = $this->getMock("oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet"));
        $oDispatcher->expects($this->once())->method("_isPayPalInDeliverySet")->will($this->returnValue(true));

        $this->assertEquals(1, $oDispatcher->UNITsetDeliverySetListForCallbackResponse($oPayPalService, $aDeliverySetList, $oUser, $oBasket));

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertEquals("DeliverySet Name", $aPayPalParams["L_SHIPPINGOPTIONNAME0"]);
        $this->assertEquals(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL0"]);
        $this->assertEquals(27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"]);
        $this->assertEquals('true', $aPayPalParams["L_SHIPPINGOPTIONISDEFAULT0"]);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getCallBackUser()
     */
    public function testGetCallBackUser()
    {
        $aPayPalData["SHIPTOSTREET"] = "testStreetName str. 12a";
        $aPayPalData["SHIPTOCITY"] = "testCity";
        $aPayPalData["SHIPTOSTATE"] = "SS";
        $aPayPalData["SHIPTOCOUNTRY"] = "US";
        $aPayPalData["SHIPTOZIP"] = "testZip";

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, new oePayPalOxUser());
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new oePayPalOxAddress());

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();

        $oPayPalUser = $oDispatcher->UNITgetCallBackUser($aPayPalData);

        $this->assertTrue(is_string($oPayPalUser->getId()));
        $this->assertEquals('testStreetName str.', $oPayPalUser->oxuser__oxstreet->value);
        $this->assertEquals('12a', $oPayPalUser->oxuser__oxstreetnr->value);
        $this->assertEquals('testCity', $oPayPalUser->oxuser__oxcity->value);
        $this->assertEquals('testZip', $oPayPalUser->oxuser__oxzip->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $oPayPalUser->oxuser__oxcountryid->value);
        $this->assertEquals('333', $oPayPalUser->oxuser__oxstateid->value);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getPayPalUser()
     * No user id setted to session
     */
    public function testGetPayPalUser_noUserIdInSession()
    {
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, new oePayPalOxUser());

        // setting user id to session
        $this->setSessionParam("oepaypal-userId", null);

        $oTestUser = new \OxidEsales\Eshop\Application\Model\User();
        $oTestUser->setId("testUserId");

        $oDispatcher = $this->getMock("oePayPalExpressCheckoutDispatcher", array("getUser"));
        $oDispatcher->expects($this->once())->method("getUser")->will($this->returnValue($oTestUser));

        $oPayPalUser = $oDispatcher->UNITgetPayPalUser();
        $this->assertEquals("testUserId", $oPayPalUser->getId());
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getPayPalUser()
     */
    public function testGetPayPalUser()
    {
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\User::class, new oePayPalOxUser());

        // setting user id to session
        $this->setSessionParam("oepaypal-userId", "oxdefaultadmin");

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $oPayPalUser = $oDispatcher->UNITgetPayPalUser();
        $this->assertEquals("oxdefaultadmin", $oPayPalUser->getId());
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_extractShippingId()
     */
    public function testExtractShippingId()
    {
        $oLang = $this->getMock(\OxidEsales\Eshop\Core\Language::class, array("translateString"));
        $oLang->expects($this->once())->method("translateString")->with($this->equalTo("OEPAYPAL_PRICE"))->will($this->returnValue("Price:"));
        $this->addModuleObject(\OxidEsales\Eshop\Core\Language::class, $oLang);

        $oPayPalConfig = $this->_createStub('ePayPalConfig', array('getCharset' => 'UTF-8'));

        $aDeliverySetList = array("oxidstandart" => "Delivery Set Name");
        $this->setSessionParam("oepaypal-oxDelSetList", $aDeliverySetList);

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $oDispatcher->setPayPalConfig($oPayPalConfig);
        $sId = $oDispatcher->UNITextractShippingId("Delivery Set Name Price:", null);
        $this->assertEquals("oxidstandart", $sId);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPaymentValidForUserCountry()
     */
    public function testIsPaymentValidForUserCountry()
    {
        $aPaymentCountries = array("testCountryId");

        $oPayment = $this->getMock(\OxidEsales\Eshop\Application\Model\Payment::class, array("load", "getCountries"));
        $oPayment->expects($this->atLeastOnce())->method("load")->with($this->equalTo("oxidpaypal"));
        $oPayment->expects($this->atLeastOnce())->method("getCountries")->will($this->returnValue($aPaymentCountries));;

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Payment::class, $oPayment);

        $oUser1 = new \OxidEsales\Eshop\Application\Model\User();
        $oUser1->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $oUser2 = new \OxidEsales\Eshop\Application\Model\User();
        $oUser2->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId_2");

        $oUser3 = new \OxidEsales\Eshop\Application\Model\User();

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertTrue($oDispatcher->UNITisPaymentValidForUserCountry($oUser1));
        $this->assertFalse($oDispatcher->UNITisPaymentValidForUserCountry($oUser2));
        $this->assertFalse($oDispatcher->UNITisPaymentValidForUserCountry($oUser3));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPaymentValidForUserCountry()
     * No country assigned to PayPal payment
     */
    public function testIsPaymentValidForUserCountry_noAssignedCountries()
    {
        $aPaymentCountries = array();

        $oPayment = $this->getMock(\OxidEsales\Eshop\Application\Model\Payment::class, array("load", "getCountries"));
        $oPayment->expects($this->atLeastOnce())->method("load")->with($this->equalTo("oxidpaypal"));
        $oPayment->expects($this->atLeastOnce())->method("getCountries")->will($this->returnValue($aPaymentCountries));;

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Payment::class, $oPayment);

        $oUser1 = new \OxidEsales\Eshop\Application\Model\User();

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertTrue($oDispatcher->UNITisPaymentValidForUserCountry($oUser1));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPayPalInDeliverySet()
     */
    public function testIsPayPalInDeliverySet()
    {
        $oUser = new \OxidEsales\Eshop\Application\Model\User();

        $aPaymentList = array("oxidpaypal" => "1", "oxidstandart" => "2");
        $oPaymentList = $this->getMock(\OxidEsales\Eshop\Application\Model\PaymentList::class, array("getPaymentList"));
        $oPaymentList->expects($this->once())->method("getPaymentList")->with($this->equalTo("testDelSetId"), $this->equalTo(5), $this->equalTo($oUser))->will($this->returnValue($aPaymentList));;

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\PaymentList::class, $oPaymentList);

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertTrue($oDispatcher->UNITisPayPalInDeliverySet("testDelSetId", 5, $oUser));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPayPalInDeliverySet()
     * PayPal payment not assigned to delivery set
     */
    public function testIsPayPalInDeliverySet_notInList()
    {
        $oUser = new \OxidEsales\Eshop\Application\Model\User();

        $aPaymentList = array("oxidstandart" => "2");
        $oPaymentList = $this->getMock(\OxidEsales\Eshop\Application\Model\PaymentList::class, array("getPaymentList"));
        $oPaymentList->expects($this->once())->method("getPaymentList")->with($this->equalTo("testDelSetId"), $this->equalTo(5), $this->equalTo($oUser))->will($this->returnValue($aPaymentList));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\PaymentList::class, $oPaymentList);

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertFalse($oDispatcher->UNITisPayPalInDeliverySet("testDelSetId", 5, $oUser));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setPayPalIsNotAvailable()
     */
    public function testSetPayPalIsNotAvailable()
    {
        $oPayPalService = new oePayPalService();

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $oDispatcher->UNITsetPayPalIsNotAvailable($oPayPalService);

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertEquals("61.0", $aPayPalParams["CALLBACKVERSION"]);
        $this->assertEquals("1", $aPayPalParams["NO_SHIPPING_OPTION_DETAILS"]);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getDeliverySetList()
     */
    public function testGetDeliverySetList()
    {
        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testCountryId");

        $oDelSetList = $this->getMock(\OxidEsales\Eshop\Application\Model\DeliverySetList::class, array("getDeliverySetList"));
        $oDelSetList->expects($this->once())->method("getDeliverySetList")->with($this->equalTo($oUser), $this->equalTo("testCountryId"))->will($this->returnValue("testValue"));

        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\DeliverySetList::class, $oDelSetList);

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertEquals("testValue", $oDispatcher->UNITgetDeliverySetList($oUser));
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::getExpressCheckoutDetails()
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
        $article->setId(substr_replace( \OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUId(), '_', 0, 1 ));
        $article->oxarticles__oxprice = new \OxidEsales\Eshop\Core\Field('8.0', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->oxarticles__oxartnum = new \OxidEsales\Eshop\Core\Field('666-T-V', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->oxarticles__oxactive = new \OxidEsales\Eshop\Core\Field('1', \OxidEsales\Eshop\Core\Field::T_RAW);
        $article->save();

        $basket = oxNew(\OxidEsales\Eshop\Application\Model\Basket::class);
        $basket->addToBasket($article->getId(), 1); //8 EUR
        $this->getSession()->setBasket($basket);

        $details = oxNew('oePayPalResponseGetExpressCheckoutDetails');
        $details->setData($data);

        $payPalService = $this->getMock('oePayPalService', array('getExpressCheckoutDetails'));
        $payPalService->expects($this->any())->method('getExpressCheckoutDetails')->will($this->returnValue($details));

        $proxy = $this->getProxyClassName('oePayPalExpressCheckoutDispatcher');
        $dispatcher = $this->getMock($proxy, array('getPayPalCheckoutService', '_isPayPalPaymentValid'));
        $dispatcher->expects($this->any())->method('_isPayPalPaymentValid')->will($this->returnValue(true));
        $dispatcher->expects($this->any())->method('getPayPalCheckoutService')->will($this->returnValue($payPalService));

        $utilsView = $this->getMock(\OxidEsales\Eshop\Core\UtilsView::class, array('addErrorToDisplay'));
        $utilsView->expects($this->once())->method('addErrorToDisplay')-> with($this->equalTo('OEPAYPAL_ORDER_TOTAL_HAS_CHANGED'));
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
     * @covers oePayPalExpressCheckoutDispatcher::getExpressCheckoutDetails()
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

        $paypalExpressResponse = oxNew('oePayPalResponseGetExpressCheckoutDetails');
        $paypalExpressResponse->setData($paypalExpressResponseData);

        $paypalServiceStub = $this->getMock('oePayPalService', array('getExpressCheckoutDetails'));
        $paypalServiceStub->expects($this->any())->method('getExpressCheckoutDetails')->will($this->returnValue($paypalExpressResponse));

        $paypalExpressCheckoutDispatcherProxyClassName = $this->getProxyClassName('oePayPalExpressCheckoutDispatcher');
        $paypalExpressCheckoutDispatcherPartialStub = $this->getMock($paypalExpressCheckoutDispatcherProxyClassName, array('getPayPalCheckoutService', '_isPayPalPaymentValid'));
        $paypalExpressCheckoutDispatcherPartialStub->expects($this->any())->method('_isPayPalPaymentValid')->will($this->returnValue(true));
        $paypalExpressCheckoutDispatcherPartialStub->expects($this->any())->method('getPayPalCheckoutService')->will($this->returnValue($paypalServiceStub));

        /** @var oePayPalExpressCheckoutDispatcher $paypalExpressCheckoutDispatcherPartialStub */
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

        $this->assertSame((float)$expectedTotalBasketSum, $paypalExpressTotalBasketSum, $messageForWrongBasketTotal);
    }

    /**
     * Mock an object which is created by oxNew.
     *
     * Attention: please don't use this method, we want to get rid of it - all places can, and should, be replaced
     *            with plain mocks.
     *
     * Hint: see also unit/model/oepaypaloxuserTest
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
        $deliveryCostRule = new \OxidEsales\Eshop\Application\Model\Delivery();
        $deliveryCostRule->setId('_fixed_price_for_paypal_test');
        $deliveryCostRule->oxdelivery__oxactive = new \OxidEsales\Eshop\Core\Field(1, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxtitle = new \OxidEsales\Eshop\Core\Field('Fixed price for PayPal test', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxaddsumtype = new \OxidEsales\Eshop\Core\Field('abs', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxaddsum = new \OxidEsales\Eshop\Core\Field($shippingCost, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxdeltype = new \OxidEsales\Eshop\Core\Field('p', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxparam = new \OxidEsales\Eshop\Core\Field(0, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->oxdelivery__oxparamend = new \OxidEsales\Eshop\Core\Field(1000, \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRule->save();

        $deliveryCostRelation = new \OxidEsales\Eshop\Core\Model\BaseModel();
        $deliveryCostRelation->init('oxdel2delset');
        $deliveryCostRelation->setId('_fixed_price_2_oxidstandard');
        $deliveryCostRelation->oxdel2delset__oxdelid = new \OxidEsales\Eshop\Core\Field($deliveryCostRule->getId(), \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRelation->oxdel2delset__oxdelsetid = new \OxidEsales\Eshop\Core\Field('oxidstandard', \OxidEsales\Eshop\Core\Field::T_RAW);
        $deliveryCostRelation->save();
    }
}

class modOxVatSelector extends \OxidEsales\Eshop\Application\Model\VatSelector
{
    public static function cleanInstanceCache()
    {
        self::$_aUserVatCache = array();
    }
}
