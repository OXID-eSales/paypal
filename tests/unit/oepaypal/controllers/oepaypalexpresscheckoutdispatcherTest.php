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

require_once realpath( "." ).'/unit/OxidTestCase.php';
require_once realpath( "." ).'/unit/test_config.inc.php';

if ( ! class_exists('oePayPalOxUser_parent')) {
    class oePayPalOxUser_parent extends oxUser {}
}


if ( ! class_exists('oePayPalOxAddress_parent')) {
    class oePayPalOxAddress_parent extends oxAddress {}
}


/**
 * Testing oePayPalExpressCheckoutDispatcher class.
 */
class Unit_oePayPal_Controllers_oePayPalExpressCheckoutDispatcherTest extends OxidTestCase
{
    /**
     * Set up
     */
    protected function setUp()
    {
        // fix for state ID compatability between editions
        $sSqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) ".
            "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        oxDb::getDb()->execute( $sSqlState );
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        oxDb::getDB()->execute( "delete from oxaddress where OXID = '_testUserAddressId' ");

        parent::tearDown();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack()
     *
     * @return null
     */
    public function testProcessCallBack()
    {
        // preparing service
        $oPayPalService = $this->getMock( "oePayPalService", array( "callbackResponse" ) );
        $oPayPalService->expects( $this->once() )->method( "callbackResponse" );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "_setParamsForCallbackResponse" ) );
        $oDispatcher->expects( $this->once() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->once() )->method( "_setParamsForCallbackResponse" )->with( $this->equalTo( $oPayPalService ) );

        // testing
        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::getExpressCheckoutDetails()
     *
     * @return null
     */
    public function testGetExpressCheckoutDetails()
    {
        $aDetails["SHIPPINGOPTIONNAME"] = "222";
        $aDetails["PAYERID"] = "111";
        $aDetails["PAYMENTREQUEST_0_AMT"] = "129.00";

        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData( $aDetails );


        // preparing useer
        $oUser = $this->getMock( "oxUser", array( "getId" ) );
        $oUser->expects( $this->any() )->method( "getId" )->will( $this->returnValue( "321" ) );

        $oPrice = $this->getMock( "oxPrice", array( "getBruttoPrice" ) );
        $oPrice->expects( $this->once() )->method( "getBruttoPrice" )->will( $this->returnValue( 129.00 ) );

        // preparing basket
        $oBasket = $this->getMock( "oxBasket", array( "setBasketUser", "setPayment", "setShipping", "calculateBasket", "getAdditionalServicesVatPercent", "getPrice" ) );
        $oBasket->expects( $this->once() )->method( "setBasketUser" )->with( $this->equalTo( $oUser ) );
        $oBasket->expects( $this->once() )->method( "setPayment" )->with( $this->equalTo( "oxidpaypal" ) );
        $oBasket->expects( $this->once() )->method( "setShipping" )->with( $this->equalTo( "123" ) );
        $oBasket->expects( $this->once() )->method( "calculateBasket" )->with( $this->equalTo( true ) );
        $oBasket->expects( $this->any() )->method( "getAdditionalServicesVatPercent" )->will( $this->returnValue( 0 ) );
        $oBasket->expects( $this->once() )->method( "getPrice" )->will( $this->returnValue( $oPrice ) );

        // preparing config
        $oPayPalConfig = $this->getMock( "oePayPalConfig", array( "finalizeOrderOnPayPalSide" ) );
        $oPayPalConfig->expects( $this->once() )->method( "finalizeOrderOnPayPalSide" )->will( $this->returnValue( true ) );

        // preparing service
        $oPayPalService = $this->getMock( "oePayPalService", array( "getExpressCheckoutDetails" ) );
        $oPayPalService->expects( $this->once() )->method( "getExpressCheckoutDetails" )->will( $this->returnValue( $oDetails ) );

        // preparing session basket
        $oSession = $this->getMock( "oxSession", array( "getBasket" ) );
        $oSession->expects( $this->once() )->method( "getBasket" )->will( $this->returnValue( $oBasket ) );

        // preparing payment list
        $oPaymentList = $this->getMock( "oxPaymentList", array( "getPaymentList" ) );
        $oPaymentList->expects( $this->once() )->method( "getPaymentList" )->will( $this->returnValue( array( 'oxidpaypal' => '' ) ) );
        oxRegistry::set("oxPaymentList", $oPaymentList);

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "_initializeUserData", "getSession", "getPayPalConfig", "_isPaymentValidForUserCountry", "_extractShippingId" ) );
        $oDispatcher->expects( $this->once() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->once() )->method( "_initializeUserData" )->with( $this->equalTo( $oDetails ) )->will( $this->returnValue( $oUser ) );
        $oDispatcher->expects( $this->any() )->method( "getSession" )->will( $this->returnValue( $oSession ) );
        $oDispatcher->expects( $this->once() )->method( "_extractShippingId" )->with( $this->equalTo( "222" ), $this->equalTo( $oUser ) )->will( $this->returnValue( "123" ) );
        $oDispatcher->expects( $this->once() )->method( "getPayPalConfig" )->will( $this->returnValue( $oPayPalConfig ) );
        $oDispatcher->expects( $this->once() )->method( "_isPaymentValidForUserCountry" )->with( $this->equalTo( $oUser ) )->will( $this->returnValue( true ) );

        // testing
        $this->assertEquals( "order?fnc=execute", $oDispatcher->getExpressCheckoutDetails() );
        $this->assertEquals( "111", $this->getSession()->getVariable( "oepaypal-payerId" ) );
        $this->assertEquals( "321", $this->getSession()->getVariable( "oepaypal-userId" ) );
        $this->assertEquals( "129.00", $this->getSession()->getVariable( "oepaypal-basketAmount" ) );

        // testing current active payment
        $this->assertEquals( "oxidpaypal", $this->getSession()->getVariable( "paymentid" ) );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::getExpressCheckoutDetails()
     *
     * @return null
     */
    public function testGetExpressCheckoutDetails_onError_returnToBasket()
    {
        $oExcp = new oxException();

        // preparing config
        $oPayPalConfig = $this->getMock( "oePayPalConfig", array( "finalizeOrderOnPayPalSide" ) );
        $oPayPalConfig->expects( $this->never() )->method( "finalizeOrderOnPayPalSide" );

        // preparing paypal service
        $oPayPalService = $this->getMock( "oePayPalService", array( "getExpressCheckoutDetails" ) );
        $oPayPalService->expects( $this->once() )->method( "getExpressCheckoutDetails" )->will( $this->throwException( $oExcp ) );

        // preparing utils view
        $oUtilsView = $this->getMock( "oxUtilsView", array( "addErrorToDisplay" ) );
        $oUtilsView->expects( $this->once() )->method( "addErrorToDisplay" )->with( $this->equalTo( $oExcp ) );

        // preparing logger
        $oPayPalLogger = $this->getMock( "oePayPalLogger", array( "log" ) );
        $oPayPalLogger->expects( $this->once() )->method( "log" );

        // preparing
        $oDispatcher = $this->getMock( "oePayPalExpressCheckoutDispatcher", array( "getPayPalCheckoutService", "getPayPalConfig", "_getUtilsView", "getLogger" ) );
        $oDispatcher->expects( $this->once() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->once() )->method( "_getUtilsView" )->will( $this->returnValue( $oUtilsView ) );
        $oDispatcher->expects( $this->once() )->method( "getLogger" )->will( $this->returnValue( $oPayPalLogger ) );
        $oDispatcher->expects( $this->never() )->method( "getPayPalConfig" );

        // testing
        $this->assertEquals( "basket", $oDispatcher->getExpressCheckoutDetails() );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::getExpressCheckoutDetails()
     * Testing addition validation by country
     *
     * @return null
     */
    public function testGetExpressCheckoutDetails_CountryValidationError()
    {
        $aDetails["SHIPPINGOPTIONNAME"] = "222";
        $aDetails["PAYERID"] = "111";

        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData( $aDetails );

        // preparing config
        $oPayPalConfig = $this->getMock( "oePayPalConfig", array( "finalizeOrderOnPayPalSide" ) );
        $oPayPalConfig->expects( $this->never() )->method( "finalizeOrderOnPayPalSide" );

        // preparing user
        $oUser = new oxUser();
        $oUser->oxuser__oxcountryid = new oxField( "testCountryId" );

        // preparing service
        $oPayPalService = $this->getMock( "oePayPalService", array( "getExpressCheckoutDetails" ) );
        $oPayPalService->expects( $this->once() )->method( "getExpressCheckoutDetails" )->will( $this->returnValue( $oDetails ) );

        // preparing utils view
        $oUtilsView = $this->getMock( "oxUtilsView", array( "addErrorToDisplay" ) );
        $oUtilsView->expects( $this->once() )->method( "addErrorToDisplay" )->with( $this->equalTo( "MESSAGE_PAYMENT_SELECT_ANOTHER_PAYMENT" ) );

        // preparing logger
        $oPayPalLogger = $this->getMock( "oePayPalLogger", array( "log" ) );
        $oPayPalLogger->expects( $this->once() )->method( "log" );

        // preparing
        $oDispatcher = $this->getMock( "oePayPalExpressCheckoutDispatcher", array( "getPayPalCheckoutService", "_initializeUserData", "getPayPalConfig", "_isPaymentValidForUserCountry", "_getUtilsView", "getLogger" ) );
        $oDispatcher->expects( $this->once() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->once() )->method( "_initializeUserData" )->with( $this->equalTo( $oDetails ) )->will( $this->returnValue( $oUser ) );
        $oDispatcher->expects( $this->once() )->method( "_getUtilsView" )->will( $this->returnValue( $oUtilsView ) );
        $oDispatcher->expects( $this->once() )->method( "getLogger" )->will( $this->returnValue( $oPayPalLogger ) );
        $oDispatcher->expects( $this->once() )->method( "_isPaymentValidForUserCountry" )->with( $this->equalTo( $oUser ) )->will( $this->returnValue( false ) );
        $oDispatcher->expects( $this->never() )->method( "getPayPalConfig" );

        // testing
        $this->assertEquals( "payment", $oDispatcher->getExpressCheckoutDetails() );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::setExpressCheckout()
     *
     * @return null
     */
    public function testSetExpressCheckout_onSuccess()
    {
        $oResult = new oePayPalResponseSetExpressCheckout();
        $oResult->setData( array('TOKEN'=>'token') );

        $oPayPalConfig = $this->getMock( "oePayPalConfig", array( "getPayPalCommunicationUrl" ) );
        $oPayPalConfig->expects( $this->once() )->method( "getPayPalCommunicationUrl" )->with( $this->equalTo( $oResult->getToken() ) )->will( $this->returnValue( 'url+123' ) );

        // preparing paypal service
        $oPayPalService = $this->getMock( "oePayPalService", array( "setExpressCheckout", "getRedirectUrl" ) );
        $oPayPalService->expects( $this->once() )->method( "setExpressCheckout" )->will( $this->returnValue( $oResult ) );

        // preparing paypal service
        $oUtils = $this->getMock( "oxUtils", array( "redirect" ) );
        $oUtils->expects( $this->once() )->method( "redirect" )->with( $this->equalTo( "url+123" ), $this->equalTo( false ) );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "_getUtils", "getPayPalConfig" ) );
        $oDispatcher->expects( $this->once() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->any() )->method( "getPayPalConfig" )->will( $this->returnValue( $oPayPalConfig ) );
        $oDispatcher->expects( $this->once() )->method( "_getUtils" )->will( $this->returnValue( $oUtils ) );

        // testing
        $oDispatcher->setExpressCheckout();
        $this->assertEquals( "token", $this->getSession()->getVariable( "oepaypal-token" ) );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::setExpressCheckout()
     *
     * @return null
     */
    public function testSetExpressCheckout_Error()
    {
        $oExcp = new oxException();

        $oPayPalConfig = $this->getMock( "oePayPalConfig", array( "getPayPalCommunicationUrl"  ) );
        $oPayPalConfig->expects( $this->never() )->method( "getPayPalCommunicationUrl" );

        // preparing paypal service
        $oPayPalService = $this->getMock( "oePayPalService", array( "setExpressCheckout" ) );
        $oPayPalService->expects( $this->once() )->method( "setExpressCheckout" )->will( $this->throwException( $oExcp ) );

        // preparing utils view
        $oUtilsView = $this->getMock( "oxUtilsView", array( "addErrorToDisplay" ) );
        $oUtilsView->expects( $this->once() )->method( "addErrorToDisplay" )->with( $this->equalTo( $oExcp ) );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "_getUtilsView" ) );
        $oDispatcher->expects( $this->once() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->once() )->method( "_getUtilsView" )->will( $this->returnValue( $oUtilsView ) );

        // testing
        $this->assertEquals( "basket", $oDispatcher->setExpressCheckout() );
    }

    /**
     * Data provider for testSetExpressCheckoutSetParameters()
     *
     * @return array
     */
    public function testSetExpressCheckoutSetParameters_dataProvider()
    {
        return array( array( true ), array( false )  );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack() - no user country id
     *
     * @return null
     */
    public function testProcessCallBack_cancelPayment_noUserCountryId() {

        // preparing paypal service
        $oPayPalService = $this->getMock( "oePayPalService", array( "callbackResponse" ) );
        $oPayPalService->expects( $this->once() )->method( "callbackResponse" );

        // preparing logger
        $oPayPalLogger = $this->getMock( "oePayPalLogger", array( "log" ) );
        $oPayPalLogger->expects( $this->at(0) )->method( "log" );
        $oPayPalLogger->expects( $this->at(1) )->method( "log" )->with( $this->equalTo( "Callback error: NO SHIPPING COUNTRY ID" ) );

        // creating user without set country id
        $oUser = oxNew( "oxUser" );
        $oUser->load( "oxdefaultadmin" );
        $oUser->oxuser__oxcountryid = new oxField( "" );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_setPayPalIsNotAvailable" ) );
        $oDispatcher->expects( $this->any() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->any() )->method( "getLogger" )->will( $this->returnValue( $oPayPalLogger ) );

        $oDispatcher->expects( $this->once() )->method( "_getCallBackUser" )->will( $this->returnValue( $oUser ) );
        $oDispatcher->expects( $this->once() )->method( "_setPayPalIsNotAvailable" );

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack() - no delivery set
     *
     * @return null
     */
    public function testProcessCallBack_cancelPayment_noDeliverySet() {

        // preparing paypal service
        $oPayPalService = $this->getMock( "oePayPalService", array( "callbackResponse" ) );
        $oPayPalService->expects( $this->once() )->method( "callbackResponse" );

        // preparing logger
        $oPayPalLogger = $this->getMock( "oePayPalLogger", array( "log" ) );
        $oPayPalLogger->expects( $this->at(0) )->method( "log" );
        $oPayPalLogger->expects( $this->at(1) )->method( "log" )->with( $this->equalTo( "Callback error: NO DELIVERY LIST SET" ) );

        $oUser = oxNew( "oxUser" );
        $oUser->load( "oxdefaultadmin" );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_getDeliverySetList", "_setPayPalIsNotAvailable" ) );
        $oDispatcher->expects( $this->any() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->any() )->method( "getLogger" )->will( $this->returnValue( $oPayPalLogger ) );

        $oDispatcher->expects( $this->once() )->method( "_getCallBackUser" )->will( $this->returnValue( $oUser ) );
        $oDispatcher->expects( $this->once() )->method( "_getDeliverySetList" )->will( $this->returnValue( null ) );
        $oDispatcher->expects( $this->once() )->method( "_setPayPalIsNotAvailable" );

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oePayPalExpressCheckoutDispatcher::processCallBack() - PayPal is not available in user country
     *
     * @return null
     */
    public function testProcessCallBack_cancelPayment_noPayPalInUserCountry() {

        // preparing PayPal service
        $oPayPalService = $this->getMock( "oePayPalService", array( "callbackResponse" ) );
        $oPayPalService->expects( $this->once() )->method( "callbackResponse" );

        // preparing logger
        $oPayPalLogger = $this->getMock( "oePayPalLogger", array( "log" ) );
        $oPayPalLogger->expects( $this->at(0) )->method( "log" );
        $oPayPalLogger->expects( $this->at(1) )->method( "log" )->with( $this->equalTo( "Callback error: NOT VALID COUNTRY ID" ) );

        $oUser = oxNew( "oxUser" );
        $oUser->load( "oxdefaultadmin" );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_getDeliverySetList", "_isPaymentValidForUserCountry", "_setPayPalIsNotAvailable" ) );
        $oDispatcher->expects( $this->any() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->any() )->method( "getLogger" )->will( $this->returnValue( $oPayPalLogger ) );

        $oDispatcher->expects( $this->once() )->method( "_getCallBackUser" )->will( $this->returnValue( $oUser ) );
        $oDispatcher->expects( $this->once() )->method( "_getDeliverySetList" )->will( $this->returnValue( array(1) ) );
        $oDispatcher->expects( $this->once() )->method( "_isPaymentValidForUserCountry" )->will( $this->returnValue( false ) );
        $oDispatcher->expects( $this->once() )->method( "_setPayPalIsNotAvailable" );

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::processCallBack() - PayPal is not in delivery list
     *
     * @return null
     */
    public function testProcessCallBack_cancelPayment_noPayPalInDeliveryListSet() {

        // preparing paypal service
        $oPayPalService = $this->getMock( "oePayPalService", array( "callbackResponse" ) );
        $oPayPalService->expects( $this->once() )->method( "callbackResponse" );

        // preparing logger
        $oPayPalLogger = $this->getMock( "oePayPalLogger", array( "log" ) );
        $oPayPalLogger->expects( $this->at(0) )->method( "log" );
        $oPayPalLogger->expects( $this->at(1) )->method( "log" )->with( $this->equalTo( "Callback error: DELIVERY SET LIST HAS NO PAYPAL" ) );

        $oUser = oxNew( "oxUser" );
        $oUser->load( "oxdefaultadmin" );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array( "getPayPalCheckoutService", "getLogger", "_getCallBackUser", "_getDeliverySetList", "_isPaymentValidForUserCountry", "_setDeliverySetListForCallbackResponse", "_setPayPalIsNotAvailable" ) );
        $oDispatcher->expects( $this->any() )->method( "getPayPalCheckoutService" )->will( $this->returnValue( $oPayPalService ) );
        $oDispatcher->expects( $this->any() )->method( "getLogger" )->will( $this->returnValue( $oPayPalLogger ) );

        $oDispatcher->expects( $this->once() )->method( "_getCallBackUser" )->will( $this->returnValue( $oUser ) );
        $oDispatcher->expects( $this->once() )->method( "_getDeliverySetList" )->will( $this->returnValue( array(1) ) );
        $oDispatcher->expects( $this->once() )->method( "_isPaymentValidForUserCountry" )->will( $this->returnValue( true ) );
        $oDispatcher->expects( $this->once() )->method( "_setDeliverySetListForCallbackResponse" )->will( $this->returnValue( 0 ) );
        $oDispatcher->expects( $this->once() )->method( "_setPayPalIsNotAvailable" );

        $oDispatcher->processCallBack();
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - new user from PayPal
     *
     * @return null
     */
    public function testInitializeUserData_newPayPalUser()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData( $aUserDetails );


        $oUser = $this->getMock( "oxUser", array( "isRealPayPalUser", "createPayPalUser" ) );
        $oUser->expects( $this->once() )->method( "isRealPayPalUser" )->with( $this->equalTo( "testUserEmail" ) )->will( $this->returnValue( false ) );
        $oUser->expects( $this->once() )->method( "createPayPalUser" )->with( $this->equalTo( $oDetails ) );

        oxTestModules::addModuleObject( 'oxUser', $oUser );

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();

        // testing
        $oDispatcher->UNITinitializeUserData( $oDetails );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - user with same email exists in shop
     * but has different address. User are not logged in.
     *
     * @return null
     */
    public function testInitializeUserData_userAlreadyExistsWithDifferentAddress()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData( $aUserDetails );

        $oUser = $this->getMock( "oxUser", array( "isRealPayPalUser", "isSamePayPalUser" ) );
        $oUser->expects( $this->once() )->method( "isRealPayPalUser" )->with( $this->equalTo("testUserEmail") )->will( $this->returnValue(true) );
        $oUser->expects( $this->once() )->method( "isSamePayPalUser" )->with( $this->equalTo( $oDetails ) )->will( $this->returnValue( false ) );

        oxTestModules::addModuleObject( 'oxUser', $oUser );

        // setting expected exception
        $this->setExpectedException('oxException');

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();

        // testing
        $oDispatcher->UNITinitializeUserData( $oDetails );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - Logged in shop user, paypal returns user email
     * that  exists in shop but has different address. New user address should be created.
     *
     * @return null
     */
    public function testInitializeUserData_loggedUser_addingNewAddress()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData( $aUserDetails );

        $oUser = $this->getMock( "oxUser", array( "isRealPayPalUser", "isSamePayPalUser", "isSameAddressPayPalUser", 'isSameAddressUserPayPalUser' ) );
        $oUser->expects( $this->once() )->method( "isRealPayPalUser" )->with( $this->equalTo("testLoggedUserEmail") )->will( $this->returnValue( "testLoggedUserId" ) );
        $oUser->expects( $this->any() )->method( "isSameAddressPayPalUser" )->with( $this->equalTo( $oDetails ) )->will( $this->returnValue( false ) );
        $oUser->expects( $this->any() )->method( "isSameAddressUserPayPalUser" )->with( $this->equalTo( $oDetails ) )->will( $this->returnValue( false ) );
        $oUser->expects( $this->never() )->method( "isSamePayPalUser" );
        $oUser->oxuser__oxusername = new oxField( "testLoggedUserEmail" );

        oxTestModules::addModuleObject( 'oxUser', $oUser );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array("_createUserAddress", "getUser") );
        $oDispatcher->expects( $this->once() )->method( "_createUserAddress" )->with( $this->equalTo( $oDetails ), $this->equalTo("testLoggedUserId") );
        $oDispatcher->expects( $this->once() )->method( "getUser" )->will( $this->returnValue($oUser) );

        // testing
        $oDispatcher->UNITinitializeUserData( $oDetails );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_initializeUserData() - Logged in shop user, paypal returns user email
     * that  exists in shop and has same address. No new user address should be created.
     *
     * @return null
     */
    public function testInitializeUserData_loggedUser_sameAddress()
    {
        $aUserDetails["EMAIL"] = "testUserEmail";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData( $aUserDetails );

        $this->getSession()->setVariable( "deladrid", "testDelId" );
        $this->assertEquals( "testDelId", $this->getSession()->getVariable( "deladrid" ) );

        $oUser = $this->getMock( "oxUser", array( "isRealPayPalUser", "isSamePayPalUser", "isSameAddressPayPalUser", 'isSameAddressUserPayPalUser' ) );
        $oUser->expects( $this->once() )->method( "isRealPayPalUser" )->with( $this->equalTo("testLoggedUserEmail") )->will( $this->returnValue("testLoggedUserId") );
        $oUser->expects( $this->once() )->method( "isSameAddressPayPalUser" )->with( $this->equalTo( $oDetails ) )->will( $this->returnValue(true) );
        $oUser->expects( $this->once() )->method( "isSameAddressUserPayPalUser" )->with( $this->equalTo( $oDetails ) )->will( $this->returnValue( true ) );
        $oUser->expects( $this->never() )->method( "isSamePayPalUser" );
        $oUser->oxuser__oxusername = new oxField( "testLoggedUserEmail" );

        oxTestModules::addModuleObject( 'oxUser', $oUser );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array("_createUserAddress", "getUser") );
        $oDispatcher->expects( $this->never() )->method( "_createUserAddress" );
        $oDispatcher->expects( $this->once() )->method( "getUser" )->will( $this->returnValue($oUser) );

        // testing
        $oDispatcher->UNITinitializeUserData( $oDetails );

        // delivery address id storred in session should be deleted
        $this->assertNull( $this->getSession()->getVariable( "deladrid" ) );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * No delivery sets - no params to PayPal should be setted
     *
     * @return null
     */
    public function testSetDeliverySetListForCallbackResponse_noDeliverySet() {

        //disabling delivery VAT check
        $this->setConfigParam( "blShowVATForDelivery", false );

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delyvery set
        $aDeliverySetList = array();

        $oUser = oxNew( "oxUser" );
        $oUser->load( "oxdefaultadmin" );

        $oBasket = $this->getMock( 'oxBasket', array( 'getPriceForPayment' ) );
        $oBasket->expects( $this->once() )->method( 'getPriceForPayment' )->will( $this->returnValue( 5 ) );

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertEquals( 0, $oDispatcher->UNITsetDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket ) );

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONNAME0"] );
        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONLABEL0"] );
        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"] );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * PayPal not assigned to delivery set
     *
     * @return null
     */
    public function testSetDeliverySetListForCallbackResponse_PayPalNotAssignedToDeliverySet() {

        //disabling delivery VAT check
        $this->setConfigParam( "blShowVATForDelivery", false );

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array( "oxidstandart" => "DeliverySet Name" );

        $oUser = oxNew( "oxUser" );

        $oBasket = $this->getMock( 'oxBasket', array('getPriceForPayment') );
        $oBasket->expects( $this->once() )->method( 'getPriceForPayment' )->will( $this->returnValue( 5 ) );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet") );
        $oDispatcher->expects( $this->once() )->method( "_isPayPalInDeliverySet" )->with( $this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($oUser) )->will( $this->returnValue( false ) );

        $this->assertEquals( 0, $oDispatcher->UNITsetDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket ) );

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONNAME0"] );
        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONLABEL0"] );
        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"] );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * No fitted deliveries found in selected delivery set
     *
     * @return null
     */
    public function testSetDeliverySetListForCallbackResponse_noFittedDeliveriesInDeliverySet()
    {
        //disabling delivery VAT check
        $this->setConfigParam( "blShowVATForDelivery", false );

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array( "oxidstandart" => "DeliverySet Name" );

        $oUser = oxNew( "oxUser" );
        $oUser->oxuser__oxcountryid = new oxField( "testCountryId" );

        $oBasket = $this->getMock( 'oxBasket', array('getPriceForPayment') );
        $oBasket->expects( $this->once() )->method( 'getPriceForPayment' )->will( $this->returnValue( 5 ) );

        $oDeliveryList = $this->getMock( 'oxDeliveryList', array('hasDeliveries') );
        $oDeliveryList->expects( $this->once() )->method( "hasDeliveries" )->with( $this->equalTo($oBasket), $this->equalTo($oUser), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart") )->will( $this->returnValue( false ) );
        oxTestModules::addModuleObject( 'oxDeliveryList', $oDeliveryList );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet") );
        $oDispatcher->expects( $this->once() )->method( "_isPayPalInDeliverySet" )->with( $this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($oUser) )->will( $this->returnValue( true ) );

        $this->assertEquals( 0, $oDispatcher->UNITsetDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket ) );

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONNAME0"] );
        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONLABEL0"] );
        $this->assertNull( $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"] );
    }

    /**
     * Data provider for testSetExpressCheckoutSetParameters()
     *
     * @return array
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInDeliverySet_dataProvider()
    {
        return array( array( true ), array( false )  );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * Deliveries found in selected delivery set
     *
     * @param bool $blIsNettoMode if netto mode true
     *
     * @dataProvider testSetDeliverySetListForCallbackResponse_deliveriesFitsInDeliverySet_dataProvider
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInDeliverySet( $blIsNettoMode )
    {
        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array( "oxidstandart" => "DeliverySet Name" );

        $oUser = oxNew( "oxUser" );
        $oUser->oxuser__oxcountryid = new oxField( "testCountryId" );

        $oBasket = $this->getMock( 'oxBasket', array('getPriceForPayment', 'getAdditionalServicesVatPercent', 'isCalculationModeNetto', 'getPayPalBasketVatValue') );
        $oBasket->expects( $this->once() )->method( 'getPriceForPayment' )->will( $this->returnValue( 5 ) );
        $oBasket->expects( $this->once() )->method( 'getAdditionalServicesVatPercent' )->will( $this->returnValue( 0 ) );
        $oBasket->expects( $this->any() )->method( 'isCalculationModeNetto' )->will( $this->returnValue( $blIsNettoMode ) );
        $oBasket->expects( $this->any() )->method( 'getPayPalBasketVatValue' )->will( $this->returnValue( 13.12 ) );

        // preparing delivery
        $oPrice = new oxPrice();
        $oPrice->setPrice( 27 );

        $oDelivery = $this->getMock( 'oxDelivery', array('getDeliveryPrice') );
        $oDelivery->expects( $this->once() )->method( 'getDeliveryPrice' )->with( $this->equalTo(0) )->will( $this->returnValue( $oPrice ) );
        $aDeliveryList = array( $oDelivery );

        $oDeliveryList = $this->getMock( 'oxDeliveryList', array('hasDeliveries', 'getDeliveryList') );
        $oDeliveryList->expects( $this->once() )->method( "hasDeliveries" )->with( $this->equalTo($oBasket), $this->equalTo($oUser), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart") )->will( $this->returnValue( true ) );
        $oDeliveryList->expects( $this->once() )->method( "getDeliveryList" )->with( $this->equalTo($oBasket), $this->equalTo($oUser), $this->equalTo("testCountryId"), $this->equalTo("oxidstandart") )->will( $this->returnValue( $aDeliveryList ) );

        oxTestModules::addModuleObject( 'oxDeliveryList', $oDeliveryList );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet") );
        $oDispatcher->expects( $this->once() )->method( "_isPayPalInDeliverySet" )->with( $this->equalTo("oxidstandart"), $this->equalTo(5), $this->equalTo($oUser) )->will( $this->returnValue( true ) );

        $this->assertEquals( 1, $oDispatcher->UNITsetDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket ) );

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        if ( $blIsNettoMode ) {
            $this->assertEquals( 13.12, $aPayPalParams["L_TAXAMT0"] );
        } else {
            $this->assertEquals( 0, $aPayPalParams["L_TAXAMT0"] );
        }

        $this->assertEquals( "DeliverySet Name", $aPayPalParams["L_SHIPPINGOPTIONNAME0"] );
        $this->assertEquals( oxRegistry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL0"] );
        $this->assertEquals( 27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"] );
        $this->assertEquals( 'true', $aPayPalParams["L_SHIPPINGOPTIONISDEFAULT0"] );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * Deliveries found in selected delivery set - more than one delivery set fits.
     *
     * @return null
     */
    public function testSetDeliverySetListForCallbackResponse_deliveriesFitsInMultipleDeliverySet()
    {
        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array( "oxidstandart" => "DeliverySet Name", "oxidstandart2" => "DeliverySet Name 2" );

        $oUser = oxNew( "oxUser" );
        $oUser->oxuser__oxcountryid = new oxField( "testCountryId" );

        // setting basket delivery set
        $oBasket = $this->getMock( 'oxBasket', array('getPriceForPayment', "getShippingId", 'getAdditionalServicesVatPercent', 'getPayPalBasketVatValue') );
        $oBasket->expects( $this->once() )->method( 'getPriceForPayment' )->will( $this->returnValue( 5 ) );
        $oBasket->expects( $this->once() )->method( 'getShippingId' )->will( $this->returnValue( "oxidstandart2" ) );
        $oBasket->expects( $this->once() )->method( 'getAdditionalServicesVatPercent' )->will( $this->returnValue( 0 ) );
        $oBasket->expects( $this->any() )->method( 'getPayPalBasketVatValue' );

        // preparing delivery
        $oPrice = new oxPrice();
        $oPrice->setPrice( 27 );

        $oDelivery = $this->getMock( 'oxDelivery', array('getDeliveryPrice') );
        $oDelivery->expects( $this->exactly(2) )->method( 'getDeliveryPrice' )->with( $this->equalTo(0) )->will( $this->returnValue( $oPrice ) );
        $aDeliveryList = array( $oDelivery );

        $oDeliveryList = $this->getMock( 'oxDeliveryList', array('hasDeliveries', 'getDeliveryList') );
        $oDeliveryList->expects( $this->exactly(2) )->method( "hasDeliveries" )->will( $this->returnValue( true ) );
        $oDeliveryList->expects( $this->exactly(2) )->method( "getDeliveryList" )->will( $this->returnValue( $aDeliveryList ) );

        oxTestModules::addModuleObject( 'oxDeliveryList', $oDeliveryList );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet") );
        $oDispatcher->expects( $this->exactly(2) )->method( "_isPayPalInDeliverySet" )->will( $this->returnValue( true ) );

        $this->assertEquals( 2, $oDispatcher->UNITsetDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket ) );

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertEquals( "DeliverySet Name", $aPayPalParams["L_SHIPPINGOPTIONNAME0"] );
        $this->assertEquals( oxRegistry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL0"] );
        $this->assertEquals( 27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"] );

        $this->assertEquals( "DeliverySet Name 2", $aPayPalParams["L_SHIPPINGOPTIONNAME1"] );
        $this->assertEquals( oxRegistry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL1"] );
        $this->assertEquals( 27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT1"] );

        // second shipping should be active
        $this->assertEquals( 'true', $aPayPalParams["L_SHIPPINGOPTIONISDEFAULT1"] );
    }


    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setDeliverySetListForCallbackResponse()
     * Applying delivery VAT
     *
     * @return null
     */
    public function testSetDeliverySetListForCallbackResponse_applyingDeliveryVat()
    {
        //disabling delivery VAT check
        $this->setConfigParam( "blShowVATForDelivery", true );

        // preparing config
        $oPayPalService = new oePayPalService();

        // preparing delivery set
        $aDeliverySetList = array( "oxidstandart" => "DeliverySet Name" );

        $oUser = oxNew( "oxUser" );
        $oUser->oxuser__oxcountryid = new oxField( "testCountryId" );

        $oBasket = $this->getMock( 'oxBasket', array('getPriceForPayment', 'getAdditionalServicesVatPercent', 'getPayPalBasketVatValue') );
        $oBasket->expects( $this->once() )->method( 'getPriceForPayment' )->will( $this->returnValue( 5 ) );
        $oBasket->expects( $this->once() )->method( 'getAdditionalServicesVatPercent' )->will( $this->returnValue( 19 ) );
        $oBasket->expects( $this->any() )->method( 'getPayPalBasketVatValue' );

        // preparing delivery
        $oPrice = new oxPrice();
        $oPrice->setPrice( 27 );

        // delivery VAT should be passed to "getDeliveryPrice" method
        $oDelivery = $this->getMock( 'oxDelivery', array('getDeliveryPrice') );
        $oDelivery->expects( $this->once() )->method( 'getDeliveryPrice' )->with( $this->equalTo(19) )->will( $this->returnValue( $oPrice ) );
        $aDeliveryList = array( $oDelivery );

        $oDeliveryList = $this->getMock( 'oxDeliveryList', array('hasDeliveries', 'getDeliveryList') );
        $oDeliveryList->expects( $this->once() )->method( "hasDeliveries" )->will( $this->returnValue( true ) );
        $oDeliveryList->expects( $this->once() )->method( "getDeliveryList" )->will( $this->returnValue( $aDeliveryList ) );

        oxTestModules::addModuleObject( 'oxDeliveryList', $oDeliveryList );

        // preparing
        $oDispatcher = $this->getMock( "oepaypalexpresscheckoutdispatcher", array("_isPayPalInDeliverySet") );
        $oDispatcher->expects( $this->once() )->method( "_isPayPalInDeliverySet" )->will( $this->returnValue( true ) );

        $this->assertEquals( 1, $oDispatcher->UNITsetDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket ) );

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertEquals( "DeliverySet Name", $aPayPalParams["L_SHIPPINGOPTIONNAME0"] );
        $this->assertEquals( oxRegistry::getLang()->translateString("OEPAYPAL_PRICE"), $aPayPalParams["L_SHIPPINGOPTIONLABEL0"] );
        $this->assertEquals( 27, $aPayPalParams["L_SHIPPINGOPTIONAMOUNT0"] );
        $this->assertEquals( 'true', $aPayPalParams["L_SHIPPINGOPTIONISDEFAULT0"] );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getCallBackUser()
     *
     * @return null
     */
    public function testGetCallBackUser()
    {
        $aPayPalData["SHIPTOSTREET"] = "testStreetName str. 12a";
        $aPayPalData["SHIPTOCITY"] = "testCity";
        $aPayPalData["SHIPTOSTATE"] = "SS";
        $aPayPalData["SHIPTOCOUNTRY"] = "US";
        $aPayPalData["SHIPTOZIP"] = "testZip";

        oxTestModules::addModuleObject( 'oxUser', new oePayPalOxUser() );
        oxTestModules::addModuleObject( 'oxAddress', new oePayPalOxAddress() );

        // preparing
        $oDispatcher = new oePayPalExpressCheckoutDispatcher();

        $oPayPalUser = $oDispatcher->UNITgetCallBackUser( $aPayPalData );

        $this->assertTrue( is_string($oPayPalUser->getId()) );
        $this->assertEquals( 'testStreetName str.', $oPayPalUser->oxuser__oxstreet->value);
        $this->assertEquals( '12a', $oPayPalUser->oxuser__oxstreetnr->value);
        $this->assertEquals( 'testCity', $oPayPalUser->oxuser__oxcity->value);
        $this->assertEquals( 'testZip', $oPayPalUser->oxuser__oxzip->value);
        $this->assertEquals( '8f241f11096877ac0.98748826', $oPayPalUser->oxuser__oxcountryid->value);
        $this->assertEquals( '333', $oPayPalUser->oxuser__oxstateid->value);
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getPayPalUser()
     * No user id setted to session
     *
     * @return null
     */
    public function testGetPayPalUser_noUserIdInSession()
    {
        oxTestModules::addModuleObject( 'oxUser', new oePayPalOxUser() );

        // setting user id to session
        $this->setSessionParam( "oepaypal-userId", null );

        $oTestUser = new oxUser();
        $oTestUser->setId( "testUserId" );

        $oDispatcher = $this->getMock( "oePayPalExpressCheckoutDispatcher", array("getUser") );
        $oDispatcher->expects( $this->once() )->method( "getUser" )->will( $this->returnValue( $oTestUser ) );

        $oPayPalUser = $oDispatcher->UNITgetPayPalUser();
        $this->assertEquals( "testUserId", $oPayPalUser->getId() );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getPayPalUser()
     *
     * @return null
     */
    public function testGetPayPalUser()
    {
        oxTestModules::addModuleObject( 'oxUser', new oePayPalOxUser() );

        // setting user id to session
        $this->setSessionParam( "oepaypal-userId", "oxdefaultadmin" );

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $oPayPalUser = $oDispatcher->UNITgetPayPalUser();
        $this->assertEquals( "oxdefaultadmin", $oPayPalUser->getId() );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_extractShippingId()
     *
     * @return null
     */
    public function testExtractShippingId()
    {
        $oLang = $this->getMock( "oxLang", array("translateString") );
        $oLang->expects( $this->once() )->method( "translateString" )->with( $this->equalTo("OEPAYPAL_PRICE") )->will( $this->returnValue( "Price:" ) );
        oxTestModules::addModuleObject( 'oxLang', $oLang );

        $oPayPalConfig = $this->_createStub( 'ePayPalConfig', array( 'getCharset' => 'UTF-8' ) );

        $aDeliverySetList = array( "oxidstandart" => "Delivery Set Name" );
        $this->setSessionParam( "oepaypal-oxDelSetList", $aDeliverySetList );

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $oDispatcher->setPayPalConfig( $oPayPalConfig );
        $sId = $oDispatcher->UNITextractShippingId( "Delivery Set Name Price:", null );
        $this->assertEquals( "oxidstandart", $sId );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPaymentValidForUserCountry()
     *
     * @return null
     */
    public function testIsPaymentValidForUserCountry()
    {
        $aPaymentCountries = array( "testCountryId" );

        $oPayment = $this->getMock( "oxPayment", array("load", "getCountries") );
        $oPayment->expects( $this->atLeastOnce() )->method( "load" )->with( $this->equalTo("oxidpaypal") );
        $oPayment->expects( $this->atLeastOnce() )->method( "getCountries" )->will( $this->returnValue( $aPaymentCountries ) );;

        oxTestModules::addModuleObject( 'oxPayment', $oPayment );

        $oUser1 = new oxUser();
        $oUser1->oxuser__oxcountryid = new oxField( "testCountryId" );

        $oUser2 = new oxUser();
        $oUser2->oxuser__oxcountryid = new oxField( "testCountryId_2" );

        $oUser3 = new oxUser();

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertTrue( $oDispatcher->UNITisPaymentValidForUserCountry($oUser1) );
        $this->assertFalse( $oDispatcher->UNITisPaymentValidForUserCountry($oUser2) );
        $this->assertFalse( $oDispatcher->UNITisPaymentValidForUserCountry($oUser3) );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPaymentValidForUserCountry()
     * No country assigned to PayPal payment
     *
     * @return null
     */
    public function testIsPaymentValidForUserCountry_noAssignedCountries()
    {
        $aPaymentCountries = array();

        $oPayment = $this->getMock( "oxPayment", array("load", "getCountries") );
        $oPayment->expects( $this->atLeastOnce() )->method( "load" )->with( $this->equalTo("oxidpaypal") );
        $oPayment->expects( $this->atLeastOnce() )->method( "getCountries" )->will( $this->returnValue( $aPaymentCountries ) );;

        oxTestModules::addModuleObject( 'oxPayment', $oPayment );

        $oUser1 = new oxUser();

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertTrue( $oDispatcher->UNITisPaymentValidForUserCountry($oUser1) );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPayPalInDeliverySet()
     *
     * @return null
     */
    public function testIsPayPalInDeliverySet()
    {
        $oUser = new oxUser();

        $aPaymentList = array( "oxidpaypal"=>"1", "oxidstandart"=>"2" );
        $oPaymentList = $this->getMock( "oxPaymentList", array("getPaymentList") );
        $oPaymentList->expects( $this->once() )->method( "getPaymentList" )->with( $this->equalTo("testDelSetId"), $this->equalTo(5), $this->equalTo($oUser) )->will( $this->returnValue( $aPaymentList ) );;

        oxTestModules::addModuleObject( 'oxPaymentList', $oPaymentList );

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertTrue( $oDispatcher->UNITisPayPalInDeliverySet("testDelSetId", 5, $oUser) );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_isPayPalInDeliverySet()
     * PayPal payment not assigned to delivery set
     *
     * @return null
     */
    public function testIsPayPalInDeliverySet_notInList()
    {
        $oUser = new oxUser();

        $aPaymentList = array( "oxidstandart"=>"2" );
        $oPaymentList = $this->getMock( "oxPaymentList", array("getPaymentList") );
        $oPaymentList->expects( $this->once() )->method( "getPaymentList" )->with( $this->equalTo("testDelSetId"), $this->equalTo(5), $this->equalTo($oUser) )->will( $this->returnValue( $aPaymentList ) );

        oxTestModules::addModuleObject( 'oxPaymentList', $oPaymentList );

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertFalse( $oDispatcher->UNITisPayPalInDeliverySet("testDelSetId", 5, $oUser) );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_setPayPalIsNotAvailable()
     *
     * @return null
     */
    public function testSetPayPalIsNotAvailable()
    {
        $oPayPalService = new oePayPalService();

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $oDispatcher->UNITsetPayPalIsNotAvailable( $oPayPalService );

        $aPayPalParams = $oPayPalService->getCaller()->getParameters();

        $this->assertEquals( "61.0", $aPayPalParams["CALLBACKVERSION"] );
        $this->assertEquals( "1", $aPayPalParams["NO_SHIPPING_OPTION_DETAILS"] );
    }

    /**
     * Test case for oepaypalexpresscheckoutdispatcher::_getDeliverySetList()
     *
     * @return null
     */
    public function testGetDeliverySetList()
    {
        $oUser = new oxUser();
        $oUser->oxuser__oxcountryid = new oxField( "testCountryId" );

        $oDelSetList = $this->getMock( "oxDeliverySetList", array("getDeliverySetList") );
        $oDelSetList->expects( $this->once() )->method( "getDeliverySetList" )->with( $this->equalTo($oUser), $this->equalTo("testCountryId") )->will( $this->returnValue( "testValue" ) );

        oxTestModules::addModuleObject( 'oxDeliverySetList', $oDelSetList );

        $oDispatcher = new oePayPalExpressCheckoutDispatcher();
        $this->assertEquals( "testValue", $oDispatcher->UNITgetDeliverySetList($oUser) );
    }
}