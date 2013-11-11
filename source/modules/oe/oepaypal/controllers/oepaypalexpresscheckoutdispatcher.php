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

/**
 * PayPal Express Checkout dispatcher class
 */
class oePayPalExpressCheckoutDispatcher extends oePayPalDispatcher
{
    /**
     * Service type identifier - Express Checkout = 2
     * @var int
     */
    protected $_iServiceType = 2;

    /**
     * Action for express checkout process
     * @var string
     */
    protected $_sUserAction = "commit";

    /**
     * Processes PayPal callback
     *
     * @return null
     */
    public function processCallBack()
    {
        $oPayPalService = $this->getPayPalCheckoutService();
        $this->_setParamsForCallbackResponse( $oPayPalService );
        $sRequest = $oPayPalService->callbackResponse();
        oxRegistry::getUtils()->showMessageAndExit( $sRequest );
    }

    /**
     * Executes "SetExpressCheckout" and on SUCCESS response - redirects to PayPal
     * login/registration page, on error - returns "basket", which means - redirect
     * to basket view and display error message
     *
     * @return string
     */
    public function setExpressCheckout()
    {
        $oSession = $this->getSession();
        $oSession->setVariable( "oepaypal", "2" );
        try {
            $oBuilder = oxNew( 'oePayPalSetExpressCheckoutRequestBuilder' );

            $oBasket = $oSession->getBasket();
            $oUser = $this->getUser();

            $oBasket->setPayment( "oxidpaypal" );

            $blPrevOptionValue = $this->getConfig()->getConfigParam( 'blCalculateDelCostIfNotLoggedIn' );
            if ( $this->getPayPalConfig()->isDeviceMobile() ){
                if ( $this->getPayPalConfig()->getMobileECDefaultShippingId() ){
                    $this->getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', true);
                    $oBasket->setShipping( $this->getPayPalConfig()->getMobileECDefaultShippingId() );
                }else{
                    $this->getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', false);
                }
            }

            $oBasket->onUpdate();
            $oBasket->calculateBasket( true );
            $this->getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', $blPrevOptionValue);

            $oValidator = oxNew( 'oePayPalPaymentValidator' );
            $oValidator->setUser( $oUser );
            $oValidator->setConfig( $this->getConfig() );
            $oValidator->setPrice( $oBasket->getPrice()->getPrice() );
            $oValidator->setCheckCountry( false );

            if ( !$oValidator->isPaymentValid() ) {
                /**
                 * @var oePayPalException $oEx
                 */
                $oEx = oxNew( "oePayPalException" );
                $oEx->setMessage( oxRegistry::getLang()->translateString( "OEPAYPAL_PAYMENT_NOT_VALID" ) );
                throw $oEx;
            }

            $oBuilder->setPayPalConfig( $this->getPayPalConfig() );
            $oBuilder->setBasket( $oBasket );
            $oBuilder->setUser( $oUser );
            $oBuilder->setReturnUrl( $this->_getReturnUrl() );
            $oBuilder->setCancelUrl( $this->_getCancelUrl() );

            if (! $this->getPayPalConfig()->isDeviceMobile() ){
                $oBuilder->setCallBackUrl( $this->_getCallBackUrl() );
                $oBuilder->setMaxDeliveryAmount( $this->getPayPalConfig()->getMaxPayPalDeliveryAmount() );
            }

            $oBuilder->setShowCartInPayPal( $this->getRequest()->getRequestParameter( "displayCartInPayPal" ) );
            $oBuilder->setTransactionMode( $this->_getTransactionMode( $oBasket ) );

            $oRequest = $oBuilder->buildExpressCheckoutRequest();

            $oPayPalService = $this->getPayPalCheckoutService();
            $oResult = $oPayPalService->setExpressCheckout( $oRequest );

        } catch ( oxException $oExcp ) {
            // error - unable to set order info - display error message
            $this->_getUtilsView()->addErrorToDisplay( $oExcp );

            // return to basket view
            return "basket";
        }

        // saving PayPal token into session
        $this->getSession()->setVariable( "oepaypal-token", $oResult->getToken() );

        // extracting token and building redirect url
        $sUrl = $this->getPayPalConfig()->getPayPalCommunicationUrl( $oResult->getToken(), $this->_sUserAction );

        // redirecting to PayPal's login/registration page
        $this->_getUtils()->redirect( $sUrl, false );
    }

    /**
     * Executes "GetExpressCheckoutDetails" and on SUCCESS response - saves
     * user information and redirects to order page, on failure - sets error
     * message and redirects to basket page
     *
     * @return string
     */
    public function getExpressCheckoutDetails()
    {
        try {
            $oPayPalService = $this->getPayPalCheckoutService();
            $oBuilder = oxNew( 'oePayPalGetExpressCheckoutDetailsRequestBuilder' );
            $oBuilder->setSession( $this->getSession() );
            $oRequest = $oBuilder->buildRequest();
            $oDetails = $oPayPalService->getExpressCheckoutDetails( $oRequest );

            // creating new or using session user
            $oUser = $this->_initializeUserData( $oDetails );
        } catch ( oxException $oExcp ) {
            $this->_getUtilsView()->addErrorToDisplay( $oExcp );

            $oLogger = $this->getLogger();
            $oLogger->log( "PayPal error: " . $oExcp->getMessage() );

            return "basket";
        }

        $oBasket = $this->getSession()->getBasket();
        $oBasket->setBasketUser( $oUser );

        // setting PayPal as current active payment
        $this->getSession()->setVariable( 'paymentid', "oxidpaypal" );
        $oBasket->setPayment( "oxidpaypal" );

        if ( !$this->_isPaymentValidForUserCountry( $oUser ) ) {
            $this->_getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_SELECT_ANOTHER_PAYMENT');

            $oLogger = $this->getLogger();
            $oLogger->log( "Shop error: PayPal payment validation by user country failed. Payment is not valid for this country." );

            return "payment";
        }

        $sShippingId = $this->_extractShippingId( urldecode( $oDetails->getShippingOptionName() ), $oUser );

        $oBasket->setShipping( $sShippingId );
        $oBasket->onUpdate();
        $oBasket->calculateBasket( true );

        $dBasketPrice = $oBasket->getPrice()->getBruttoPrice();

        if ( !$this->_isPayPalPaymentValid( $oUser, $dBasketPrice, $oBasket->getShippingId() ) ) {
            $this->_getUtilsView()->addErrorToDisplay( "OEPAYPAL_SELECT_ANOTHER_SHIPMENT" );

            return "order";
        }

        // Checking if any additional discount was applied after we returned from PayPal.
        if ( $dBasketPrice != $oDetails->getAmount() ) {
            $this->_getUtilsView()->addErrorToDisplay( "OEPAYPAL_ORDER_TOTAL_HAS_CHANGED" );

            return "basket";
        }

        $this->getSession()->setVariable( "oepaypal-payerId", $oDetails->getPayerId() );
        $this->getSession()->setVariable( "oepaypal-userId", $oUser->getId() );
        $this->getSession()->setVariable( "oepaypal-basketAmount", $oDetails->getAmount() );

        $sNext = "order";

        if ( $this->getPayPalConfig()->finalizeOrderOnPayPalSide() ) {
            $sNext .= "?fnc=execute";
        }

        return $sNext;
    }

    /**
     * @param $oBasket
     * @return string
     */
    protected function _getTransactionMode( $oBasket )
    {
        $sTransactionMode = $this->getPayPalConfig()->getTransactionMode();

        if ( $sTransactionMode == "Automatic" ) {

            $oOutOfStockValidator = new oePayPalOutOfStockValidator();
            $oOutOfStockValidator->setBasket( $oBasket );
            $oOutOfStockValidator->setEmptyStockLevel( $this->getPayPalConfig()->getEmptyStockLevel() );

            $sTransactionMode = ( $oOutOfStockValidator->hasOutOfStockArticles() ) ? "Authorization" : "Sale";
            return $sTransactionMode;
        }
        return $sTransactionMode;
    }

    /**
     * Returns RETURN URL
     *
     * @return string
     */
    protected function _getReturnUrl()
    {
        return $this->getSession()->processUrl( $this->_getBaseUrl() . "&cl=" . get_class() . "&fnc=getExpressCheckoutDetails" );
    }

    /**
     * Returns CANCEL URL
     *
     * @return string
     */
    protected function _getCancelUrl()
    {
        $sCancelUrl = $this->getSession()->processUrl( $this->_getBaseUrl() . "&cl=basket" );
        if ( $sCancelURLFromRequest = $this->getRequest()->getRequestParameter( 'oePayPalCancelURL' ) ) {
            $sCancelUrl = html_entity_decode( urldecode( $sCancelURLFromRequest ) );
        }

        return $sCancelUrl;
    }

    /**
     * Returns CALLBACK URL
     *
     * @return string
     */
    protected function _getCallBackUrl()
    {
        return $this->getSession()->processUrl( $this->_getBaseUrl() . "&cl=" . get_class() . "&fnc=processCallBack" );
    }

    /**
     *  Initialize new user from user data
     *
     * @param $aData User data array
     *
     * @return oxUser
     */
    protected function _getCallBackUser( $aData )
    {
        // simulating user object
        $oUser = oxNew( "oxUser" );
        $oUser->initializeUserForCallBackPayPalUser( $aData );
        return $oUser;
    }

    /**
     * Sets parameters to PayPal callback
     *
     * @param oePayPalService $oPayPalService PayPal service
     *
     * @return null
     */
    protected function _setParamsForCallbackResponse( $oPayPalService )
    {
        //logging request from PayPal
        $oLogger = $this->getLogger();
        $oLogger->setTitle( "CALLBACK REQUEST FROM PAYPAL" );
        $oLogger->log( http_build_query( $_REQUEST, "", "&" ) );

        // initializing user..
        $oUser = $this->_getCallBackUser( $_REQUEST );

        // unknown country?
        if ( !$this->_getUserShippingCountryId( $oUser ) ) {
            $oLogger = $this->getLogger();
            $oLogger->log( "Callback error: NO SHIPPING COUNTRY ID" );

            // unknown country - no delivery
            $this->_setPayPalIsNotAvailable( $oPayPalService );
            return;
        }

        //basket
        $oBasket = $this->getSession()->getBasket();

        // get possible delivery sets
        $oDelSetList = $this->_getDeliverySetList( $oUser );

        //no shipping methods for user country
        if ( !count( $oDelSetList ) ) {
            $oLogger = $this->getLogger();
            $oLogger->log( "Callback error: NO DELIVERY LIST SET" );

            $this->_setPayPalIsNotAvailable( $oPayPalService );
            return;
        }

        $aDeliverySetList = $this->_makeUniqueNames( $oDelSetList );

        // checking if PayPal is valid payment for selected user country
        if ( !$this->_isPaymentValidForUserCountry( $oUser )  ) {
            $oLogger->log( "Callback error: NOT VALID COUNTRY ID" );

            // PayPal payment is not possible for user country
            $this->_setPayPalIsNotAvailable( $oPayPalService );
            return;
        }

        $this->getSession()->setVariable( 'oepaypal-oxDelSetList', $aDeliverySetList );

        $iTotalDeliveries = $this->_setDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket );

        // if none of deliveries contain PayPal - disabling PayPal
        if ( $iTotalDeliveries == 0 ) {
            $oLogger->log( "Callback error: DELIVERY SET LIST HAS NO PAYPAL" );

            $this->_setPayPalIsNotAvailable( $oPayPalService );
            return;
        }

        $oPayPalService->setParameter( "OFFERINSURANCEOPTION", "false" );
    }

    /**
     * Sets delivery sets parameters to PayPal callback
     *
     * @param oePayPalService $oPayPalService   PayPal service
     * @param oxDeliverySetList       $aDeliverySetList Delivery list
     * @param oxUser                  $oUser            User object
     * @param oxBasket                $oBasket          Basket object
     *
     * @return int Total amount of deliveries
     */
    protected function _setDeliverySetListForCallbackResponse( $oPayPalService, $aDeliverySetList, $oUser, $oBasket )
    {
        $dMaxDeliveryAmount = $this->getPayPalConfig()->getMaxPayPalDeliveryAmount();
        $oCur               = $this->getConfig()->getActShopCurrencyObject();
        $dBasketPrice       = $oBasket->getPriceForPayment() / $oCur->rate;
        $sActShipSet        = $oBasket->getShippingId();
        $blHasActShipSet    = false;
        $iCnt               = 0;

        // VAT for delivery will be calculated always
        $fDelVATPercent = $oBasket->getAdditionalServicesVatPercent();

        foreach ( $aDeliverySetList as $sDelSetId => $sDelSetName ) {

            // checking if PayPal is valid payment for selected delivery set
            if ( !$this->_isPayPalInDeliverySet( $sDelSetId, $dBasketPrice, $oUser ) ) {
                continue;
            }

            $oDeliveryList = oxNew( 'oxDeliveryList' );
            $aDeliveryList = array();

            // list of active delivery costs
            if ( $oDeliveryList->hasDeliveries( $oBasket, $oUser, $this->_getUserShippingCountryId( $oUser ), $sDelSetId ) ) {
                $aDeliveryList = $oDeliveryList->getDeliveryList( $oBasket, $oUser, $this->_getUserShippingCountryId( $oUser ), $sDelSetId );
            }

            if ( count( $aDeliveryList ) > 0 ) {

                $dPrice = 0;

                if ( $this->getConfig()->getConfigParam('bl_perfLoadDelivery') ) {
                    foreach ( $aDeliveryList as $oDelivery ) {
                        $dPrice += $oDelivery->getDeliveryPrice( $fDelVATPercent )->getBruttoPrice();
                    }
                }

                if ( $dPrice <= $dMaxDeliveryAmount ) {
                    $oPayPalService->setParameter( "L_SHIPPINGOPTIONNAME{$iCnt}", getStr()->html_entity_decode( $sDelSetName ) );
                    $oPayPalService->setParameter( "L_SHIPPINGOPTIONLABEL{$iCnt}", oxRegistry::getLang()->translateString( "OEPAYPAL_PRICE" ) );
                    $oPayPalService->setParameter( "L_SHIPPINGOPTIONAMOUNT{$iCnt}", $this->_formatFloat( $dPrice ) );

                    //setting active delivery set
                    if ( $sDelSetId == $sActShipSet ) {
                        $blHasActShipSet = true;
                        $oPayPalService->setParameter( "L_SHIPPINGOPTIONISDEFAULT{$iCnt}",  "true" );
                    } else {
                        $oPayPalService->setParameter( "L_SHIPPINGOPTIONISDEFAULT{$iCnt}",  "false" );
                    }

                    if ( $oBasket->isCalculationModeNetto() ) {
                        $oPayPalService->setParameter( "L_TAXAMT{$iCnt}", $this->_formatFloat( $oBasket->getPayPalBasketVatValue() ));
                    } else {
                        $oPayPalService->setParameter( "L_TAXAMT{$iCnt}", $this->_formatFloat( 0 ) );
                    }
                }

                $iCnt++;
            }
        }

        //checking if active delivery set was set - if not, setting first in the list
        if ( $iCnt > 0 && !$blHasActShipSet ) {
            $oPayPalService->setParameter( "L_SHIPPINGOPTIONISDEFAULT0",  "true" );
        }

        return $iCnt;
    }

    /**
     * Makes delivery set array with unique names
     *
     * @param oxDeliveryList $oDelSetList delivery list
     *
     * @return array
     */
    public function _makeUniqueNames( $oDelSetList  )
    {
        $aDelSetList = array();
        $aNameCounts = array();

        foreach ( $oDelSetList as $oDelSet ) {

            $sDelSetName = trim( $oDelSet->oxdeliveryset__oxtitle->value );

            if ( isset( $aNameCounts[ $sDelSetName ] )  ) {
                $aNameCounts[ $sDelSetName ] += 1;
            } else {
                $aNameCounts[ $sDelSetName ] = 1;
            }

            $sSuffix = ( $aNameCounts[$sDelSetName] > 1 ) ? " (". $aNameCounts[$sDelSetName] .")" : '';
            $aDelSetList[$oDelSet->oxdeliveryset__oxid->value] = $sDelSetName . $sSuffix;
        }

        return $aDelSetList;
    }

    /**
     * Returns PayPal user
     *
     * @return oxUser
     */
    protected function _getPayPalUser()
    {
        $oUser = oxNew( 'oxUser' );
        if (! $oUser->loadUserPayPalUser() ){
           $oUser = $this->getUser();
        }
        return $oUser;
    }

    /**
     * Extracts shipping id from given parameter
     *
     * @param string $sShippingOptionName shipping option name, which comes from PayPal
     * @param string $oUser
     *
     * @return string
     */
    protected function _extractShippingId( $sShippingOptionName, $oUser )
    {
        $sCharset = $this->getPayPalConfig()->getCharset();
        $sShippingOptionName = htmlentities( html_entity_decode( $sShippingOptionName, ENT_QUOTES, $sCharset ), ENT_QUOTES, $sCharset );

        $sName = trim( str_replace( oxRegistry::getLang()->translateString( "OEPAYPAL_PRICE" ), "", $sShippingOptionName ) );

        $aDeliverySetList = $this->getSession()->getVariable( "oepaypal-oxDelSetList" );

        if ( !$aDeliverySetList ) {
            $oDelSetList = $this->_getDeliverySetList( $oUser );
            $aDeliverySetList = $this->_makeUniqueNames( $oDelSetList );
        }

        if ( is_array( $aDeliverySetList ) ) {
            $aFlipped = array_flip( $aDeliverySetList );

            return $aFlipped[ $sName ] ;
        }
    }

    /**
     * Creates new or returns session user
     *
     * @param oePayPalResponseGetExpressCheckoutDetails $oDetails
     *
     * @throws oxException
     * @return oxUser
     */
    protected function _initializeUserData( $oDetails )
    {
        $sUserEmail = $oDetails->getEmail();
        $oLoggedUser = $this->getUser();
        if( $oLoggedUser ){
            $sUserEmail = $oLoggedUser->oxuser__oxusername->value;
        }

        $oUser = oxNew ( "oxUser" );
        if ( $sUserId = $oUser->isRealPayPalUser( $sUserEmail ) ) {
            // if user exist
            $oUser->load( $sUserId );

            if ( !$oLoggedUser  ) {
                if ( !$oUser->isSamePayPalUser( $oDetails ) ) {
                    /**
                     * @var $oEx oxException
                     */
                    $oEx = oxNew( 'oxException' );
                    $oEx->setMessage( 'OEPAYPAL_ERROR_USER_ADDRESS' );
                    throw $oEx;
                }
            } elseif ( !$oUser->isSameAddressUserPayPalUser( $oDetails ) || !$oUser->isSameAddressPayPalUser( $oDetails ) ) {
                // user has selected different address in PayPal (not equal with usr shop address)
                // so adding PayPal address as new user address to shop user account
                $this->_createUserAddress( $oDetails, $sUserId );
            } else {
                // removing custom shipping address ID from session as user uses billing
                // address for shipping
                $this->getSession()->deleteVariable( 'deladrid' );
            }
        } else {
            $oUser->createPayPalUser( $oDetails );
        }

        $this->getSession()->setVariable( 'usr', $oUser->getId() );

        return $oUser;
    }

    /**
     * Creates user address and sets address id into session
     *
     * @param oePayPalResponseGetExpressCheckoutDetails $oDetails user address info
     * @param string $sUserId  user id
     *
     * @return bool
     */
    protected function _createUserAddress( $oDetails, $sUserId )
    {
        $oAddress = oxNew( "oxAddress" );
        return $oAddress->createPayPalAddress( $oDetails, $sUserId );
    }

    /**
     * Checking if PayPal payment is available in user country
     *
     * @param oxUser $oUser User object
     *
     * @return boolean
     */
    protected function _isPaymentValidForUserCountry( $oUser )
    {
        $oPayment = oxNew( "oxPayment" );
        $oPayment->load( "oxidpaypal" );
        $aPaymentCountries = $oPayment->getCountries();

        if ( !is_array( $aPaymentCountries ) || empty( $aPaymentCountries ) ) {
            // not assigned to any country - valid to all countries
            return true;
        }

        return in_array( $this->_getUserShippingCountryId( $oUser ), $aPaymentCountries );
    }

    /**
     * Checks if selected delivery set has PayPal payment
     *
     * @param string $sDelSetId    Delivery set ID
     * @param double $dBasketPrice Basket price
     * @param oxUser $oUser        User object
     *
     * @return boolean
     */
    protected function _isPayPalInDeliverySet( $sDelSetId, $dBasketPrice, $oUser )
    {
        $aPaymentList = oxRegistry::get("oxPaymentList")->getPaymentList( $sDelSetId, $dBasketPrice, $oUser );

        if ( is_array($aPaymentList) && array_key_exists( "oxidpaypal", $aPaymentList ) ) {
            return true;
        }

        return false;
    }

    /**
     * Disables PayPal payment in PayPal side
     *
     * @param oePayPalService $oPayPalService PayPal service
     *
     * @return null
     */
    protected function _setPayPalIsNotAvailable( $oPayPalService )
    {
        // "NO_SHIPPING_OPTION_DETAILS" works only in version 61, so need to switch version
        $oPayPalService->setParameter( "CALLBACKVERSION", "61.0" );
        $oPayPalService->setParameter( "NO_SHIPPING_OPTION_DETAILS", "1" );
    }

    /**
     * Get delivery set list for PayPal callback
     *
     * @param oxUser $oUser User object
     *
     * @return array
     */
    protected function _getDeliverySetList( $oUser )
    {
        $oDelSetList = oxNew( "oxDeliverySetList" );

        return $oDelSetList->getDeliverySetList( $oUser, $this->_getUserShippingCountryId( $oUser ) );
    }

    /**
     * Returns user shipping address country id
     * @param oxUser $oUser
     *
     * @return string
     */
    protected function _getUserShippingCountryId( $oUser )
    {
        if ( $oUser->getSelectedAddressId() && $oUser->getSelectedAddress() ) {
            $sCountryId = $oUser->getSelectedAddress()->oxaddress__oxcountryid->value;
        } else {
            $sCountryId = $oUser->oxuser__oxcountryid->value;
        }
        return $sCountryId;
    }

    /**
     * Checks whether PayPal payment is available
     * @param $oUser
     * @param $dBasketPrice
     * @param $sShippingId
     *
     * @return bool
     */
    protected function _isPayPalPaymentValid( $oUser, $dBasketPrice, $sShippingId )
    {
        $blValid = true;

        $oPayPalPayment = oxNew( 'oxPayment');
        $oPayPalPayment->load( 'oxidpaypal' );
        if ( !$oPayPalPayment->isValidPayment( null, null, $oUser, $dBasketPrice, $sShippingId ) ) {
            $blValid = $this->_isEmptyPaymentValid( $oUser, $dBasketPrice, $sShippingId );
        }

        return $blValid;
    }

    /**
     * Checks whether Empty payment is available.
     * @param $sShippingId
     * @param $dBasketPrice
     * @param $oUser
     *
     * @return bool
     */
    protected function _isEmptyPaymentValid( $oUser, $dBasketPrice, $sShippingId )
    {
        $blValid = true;

        $oEmptyPayment = oxNew( 'oxPayment' );
        $oEmptyPayment->load( 'oxempty' );
        if ( !$oEmptyPayment->isValidPayment( null, null, $oUser, $dBasketPrice, $sShippingId ) ) {
            $blValid = false;
        }

        return $blValid;
    }
}