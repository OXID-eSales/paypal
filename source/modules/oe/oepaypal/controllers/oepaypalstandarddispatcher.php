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
 * PayPal Standard Checkout dispatcher class
 */
class oePayPalStandardDispatcher extends oePayPalDispatcher
{
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
        $oSession->setVariable( "oepaypal", "1" );
        try {
            $oBuilder = oxNew( 'oePayPalSetExpressCheckoutRequestBuilder' );

            $oBasket = $oSession->getBasket();
            $oUser = $this->getUser();

            $oBasket->setPayment( "oxidpaypal" );
            $oBasket->onUpdate();
            $oBasket->calculateBasket( true );

            $oValidator = oxNew( 'oePayPalPaymentValidator' );
            $oValidator->setUser( $oUser );
            $oValidator->setConfig( $this->getConfig() );
            $oValidator->setPrice( $oBasket->getPrice()->getPrice() );

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
            $oBuilder->setUser( $this->getUser() );
            $oBuilder->setReturnUrl( $this->_getReturnUrl() );
            $oBuilder->setCancelUrl( $this->_getCancelUrl() );
            $oBuilder->setShowCartInPayPal( $this->getRequest()->getRequestParameter( "displayCartInPayPal" ) );
            $oBuilder->setTransactionMode( $this->_getTransactionMode( $oBasket ) );

            $oRequest = $oBuilder->buildStandardCheckoutRequest();

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
        } catch ( oxException $oExcp ) {
            // display error message
            $this->_getUtilsView()->addErrorToDisplay( $oExcp );

            // problems fetching user info - redirect to payment selection
            return "payment";
        }

        $this->getSession()->setVariable( "oepaypal-payerId", $oDetails->getPayerId() );
        $this->getSession()->setVariable( "oepaypal-basketAmount", $oDetails->getAmount() );

        // next step - order page
        $sNext = "order";

        // finalize order on paypal side?
        if ( $this->getPayPalConfig()->finalizeOrderOnPayPalSide() ) {
            $sNext .= "?fnc=execute";
        }

        // everything is fine - redirect to order
        return $sNext;
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
        return $this->getSession()->processUrl( $this->_getBaseUrl() . "&cl=payment" );
    }
}