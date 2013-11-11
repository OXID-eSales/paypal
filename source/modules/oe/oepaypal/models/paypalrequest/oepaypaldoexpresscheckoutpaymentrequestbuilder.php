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
 * PayPal request builder class for do express checkout payment
 */
class oePayPalDoExpressCheckoutPaymentRequestBuilder
{
    /**
     * @var oePayPalPayPalRequest
     */
    protected $_oRequest = null;

    /**
     * @var oePayPalConfig
     */
    protected $_oPayPalConfig = null;

    /**
     * @var oxBasket
     */
    protected $_oBasket = null;

    /**
     * @var oxUser
     */
    protected $_oUser = null;

    /**
     * @var oxSession
     */
    protected $_oSession = null;

    /**
     * @var sTransactionMode : Sale or Authorization
     */
    protected $_sTransactionMode;

    /**
     * @var oxOrder
     */
    protected $_oOrder = null;

    /**
     * @var oePayPalConfig
     */
    protected $_oLang = null;

    /**
     * @param oePayPalPayPalRequest $oRequest
     */
    public function setRequest( $oRequest )
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Returns request object
     */
    public function getRequest()
    {
        if ( $this->_oRequest === null ) {
            $this->_oRequest = oxNew( 'oePayPalPayPalRequest' );
        }
        return $this->_oRequest;
    }

    /**
     * Returns request object
     *
     * @param oePayPalConfig $oConfig
     */
    public function setPayPalConfig( $oConfig )
    {
        $this->_oPayPalConfig = $oConfig;
    }

    /**
     * Returns request object
     */
    public function getPayPalConfig()
    {
        return $this->_oPayPalConfig;
    }

    /**
     * @param oxBasket $oBasket
     */
    public function setBasket( $oBasket )
    {
        $this->_oBasket = $oBasket;
    }

    /**
     * @return oxBasket
     *
     * @throws oePayPalMissingParameterException
     */
    public function getBasket()
    {
        if ( is_null( $this->_oBasket ) ) {
            /**
             * @var oePayPalMissingParameterException $oException
             */
            $oException = oxNew( 'oePayPalMissingParameterException' );
            throw $oException;
        }
        return $this->_oBasket;
    }

    /**
     * @param oxOrder $oOrder
     */
    public function setOrder( $oOrder )
    {
        $this->_oOrder = $oOrder;
    }

    /**
     * @return oxBasket
     *
     * @throws oePayPalResponseException
     */
    public function getOrder()
    {
        if ( is_null( $this->_oOrder ) ) {
            /**
             * @var oePayPalResponseException $oException
             */
            $oException = oxNew( 'oePayPalResponseException' );
            $oException->setMessage( 'OEPAYPAL_ORDER_ERROR' );
            throw $oException;
        }
        return $this->_oOrder;
    }

    /**
     * @param oxSession $oSession
     */
    public function setSession( $oSession )
    {
        $this->_oSession = $oSession;
    }

    /**
     * @return oxSession
     */
    public function getSession()
    {
        return $this->_oSession;
    }

    /**
     * Returns request object
     *
     * @param oxLang $oLang
     */
    public function setLang( $oLang )
    {
        $this->_oLang = $oLang;
    }

    /**
     * Returns request object
     */
    public function getLang()
    {
        if ( $this->_oLang === null ) {
            $this->_oLang = $this->getPayPalConfig()->getLang();
        }
        return $this->_oLang;
    }

    /**
     * @param string $sTransactionMode
     */
    public function setTransactionMode( $sTransactionMode )
    {
        $this->_sTransactionMode = $sTransactionMode;
    }

    /**
     * @return string $sTransactionMode
     */
    public function getTransactionMode()
    {
        return $this->_sTransactionMode;
    }

    /**
     * Sets User object
     *
     * @param oePayPalOxUser $oUser
     */
    public function setUser( $oUser )
    {
        $this->_oUser = $oUser;
    }

    /**
     * Returns User object
     *
     * @return oePayPalOxUser
     */
    public function getUser()
    {
        if ( is_null( $this->_oUser ) ) {
            /**
             * @var oePayPalMissingParameterException $oException
             */
            $oException = oxNew( 'oePayPalMissingParameterException' );
            throw $oException;
        }
        return $this->_oUser;
    }

    /**
     * Sets base parameters to request
     */
    public function buildRequest()
    {
        $this->addBaseParams();
        $this->addAddressParams();

        return $this->getRequest();
    }

    /**
     * Sets Address parameters to request.
     */
    public function addAddressParams()
    {
        $oUser = $this->getUser();
        if ( !$oUser ) {
            return;
        }
        $oRequest = $this->getRequest();

        $sAddressId = $oUser->getSelectedAddressId();
        if ( $sAddressId ) {
            $oAddress = oxNew( "oxAddress" );
            $oAddress->load( $sAddressId );

            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTONAME", getStr()->html_entity_decode($oAddress->oxaddress__oxfname->value . " " . $oAddress->oxaddress__oxlname->value) );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOSTREET", getStr()->html_entity_decode($oAddress->oxaddress__oxstreet->value . " " . $oAddress->oxaddress__oxstreetnr->value) );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOCITY", $oAddress->oxaddress__oxcity->value );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOZIP", $oAddress->oxaddress__oxzip->value  );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOPHONENUM", $oAddress->oxaddress__oxfon->value);

            $oCountry = oxNew( "oxCountry" );
            $oCountry->load( $oAddress->oxaddress__oxcountryid->value );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE", $oCountry->oxcountry__oxisoalpha2->value );

            if( $oAddress->oxaddress__oxstateid->value ) {
                $oState = oxNew( "oxState" );
                $oState->load( $oAddress->oxaddress__oxstateid->value );
                $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOSTATE", $oState->oxstates__oxisoalpha2->value );
            }
        } else {
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTONAME", getStr()->html_entity_decode($oUser->oxuser__oxfname->value . " " . $oUser->oxuser__oxlname->value) );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOSTREET", getStr()->html_entity_decode($oUser->oxuser__oxstreet->value . " " . $oUser->oxuser__oxstreetnr->value) );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOCITY", $oUser->oxuser__oxcity->value );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOZIP", $oUser->oxuser__oxzip->value  );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOPHONENUM", $oUser->oxuser__oxfon->value);

            $oCountry = oxNew( "oxCountry" );
            $oCountry->load( $oUser->oxuser__oxcountryid->value );
            $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE", $oCountry->oxcountry__oxisoalpha2->value );

            if ( $oUser->oxuser__oxstateid->value ){
                $oState = oxNew( "oxState" );
                $oState->load( $oUser->oxuser__oxstateid->value );
                $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPTOSTATE", $oState->oxstates__oxisoalpha2->value );
            }
        }
    }

    /**
     * Sets basic parameters to request.
     */
    public function addBaseParams()
    {
        $oOrder = $this->getOrder();
        $oConfig = $this->getPayPalConfig();
        $oBasket = $this->getBasket();
        $oSession = $this->getSession();
        $oLang = $this->getLang();
        $oRequest = $this->getRequest();

        $oRequest->setParameter( "TOKEN", $oSession->getVariable( "oepaypal-token" ) );
        $oRequest->setParameter( "PAYERID", $oSession->getVariable( "oepaypal-payerId" ) );

        $oRequest->setParameter( "PAYMENTREQUEST_0_PAYMENTACTION", $this->getTransactionMode() );
        $oRequest->setParameter( "PAYMENTREQUEST_0_AMT", $this->_formatFloat( $oBasket->getPrice()->getBruttoPrice() ) );
        $oRequest->setParameter( "PAYMENTREQUEST_0_CURRENCYCODE", $oBasket->getBasketCurrency()->name );
        // IPN notify URL for PayPal
        $oRequest->setParameter( "PAYMENTREQUEST_0_NOTIFYURL", $oConfig->getIPNCallbackUrl() );

        // payment description
        $sSubj = sprintf( $oLang->translateString( "OEPAYPAL_ORDER_CONF_SUBJECT" ), $oOrder->oxorder__oxordernr->value );
        $oRequest->setParameter( "PAYMENTREQUEST_0_DESC", $sSubj );
        $oRequest->setParameter( "PAYMENTREQUEST_0_CUSTOM", $sSubj );

        // Please do not change this place.
        // It is important to guarantee the future development of this OXID eShop extension and to keep it free of charge.
        // Thanks!
        $oRequest->setParameter( "BUTTONSOURCE", $oConfig->getPartnerCode() );
    }

    /**
     * Formats given float/int value into PayPal friendly form
     *
     * @param float $fIn value to format
     *
     * @return string
     */
    protected function _formatFloat( $fIn )
    {
        return sprintf( "%.2f", $fIn );
    }
}