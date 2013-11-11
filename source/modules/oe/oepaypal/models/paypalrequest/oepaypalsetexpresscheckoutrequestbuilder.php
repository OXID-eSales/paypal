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
 * PayPal request builder class for set express checkout
 */
class oePayPalSetExpressCheckoutRequestBuilder
{
    /**
     * PayPal Request
     *
     * @var oePayPalPayPalRequest
     */
    protected $_oPayPalRequest = null;

    /**
     * PayPal Config
     *
     * @var oePayPalConfig
     */
    protected $_oPayPalConfig = null;

    /**
     * Basket object
     *
     * @var oePayPalOxBasket
     */
    protected $_oBasket = null;

    /**
     * User object
     *
     * @var oePayPalOxUser
     */
    protected $_oUser = null;

    /**
     * Language object
     *
     * @var oxLang
     */
    protected $_oLang = null;

    /**
     * Url to return to after PayPal payment is done
     *
     * @var string
     */
    protected $_sReturnUrl = null;

    /**
     * Url to return to if PayPal payment was canceled
     *
     * @var string
     */
    protected $_sCancelUrl = null;

    /**
     * Url for PayPal CallBack
     *
     * @var string
     */
    protected $_sCallBackUrl = null;

    /**
     * Show basket items in PayPal
     *
     * @var bool
     */
    protected $_blShowCartInPayPal = false;

    /**
     * Transaction mode: Sale|Authorization
     *
     * @var string
     */
    protected $_sTransactionMode;

    /**
     * Maximum possible delivery costs value.
     *
     * @var double
     */
    protected $_dMaxDeliveryAmount = 0;


    /**
     * @param \double $dMaxDeliveryAmount
     */
    public function setMaxDeliveryAmount( $dMaxDeliveryAmount )
    {
        $this->_dMaxDeliveryAmount = $dMaxDeliveryAmount;
    }

    /**
     * @return \double
     */
    public function getMaxDeliveryAmount()
    {
        return $this->_dMaxDeliveryAmount;
    }

    /**
     * Sets PayPal request object
     *
     * @param oePayPalPayPalRequest $oRequest
     */
    public function setPayPalRequest( $oRequest )
    {
        $this->_oPayPalRequest = $oRequest;
    }

    /**
     * Returns PayPal request object; initiates if not set
     *
     * Returns request object
     */
    public function getPayPalRequest()
    {
        if ( $this->_oPayPalRequest === null ) {
            $this->_oPayPalRequest = oxNew( 'oePayPalPayPalRequest' );
        }
        return $this->_oPayPalRequest;
    }

    /**
     * Returns config object
     *
     * @param oePayPalConfig $oConfig
     */
    public function setPayPalConfig( $oConfig )
    {
        $this->_oPayPalConfig = $oConfig;
    }

    /**
     * Returns config object
     *
     * @return oePayPalConfig
     *
     * @throws oePayPalMissingParameterException
     */
    public function getPayPalConfig()
    {
        if ( !$this->_oPayPalConfig ) {
            throw oxNew( 'oePayPalMissingParameterException' );
        }
        return $this->_oPayPalConfig;
    }

    /**
     * Sets Basket object
     *
     * @param oxBasket $oBasket
     */
    public function setBasket( $oBasket )
    {
        $this->_oBasket = $oBasket;
    }

    /**
     * Returns basket object
     *
     * @return oxBasket
     *
     * @throws oePayPalMissingParameterException
     */
    public function getBasket()
    {
        if ( is_null( $this->_oBasket ) ) {
            throw oxNew( 'oePayPalMissingParameterException' );
        }
        return $this->_oBasket;
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
        return $this->_oUser;
    }

    /**
     * Sets Language object
     *
     * @param oxLang $oLang
     */
    public function setLang( $oLang )
    {
        $this->_oLang = $oLang;
    }

    /**
     * Returns Language object
     */
    public function getLang()
    {
        if ( is_null( $this->_oLang ) ) {
            $this->_oLang = $this->getPayPalConfig()->getLang();
        }
        return $this->_oLang;
    }

    /**
     * Sets CallBack url
     *
     * @param string $sCallBackUrl
     */
    public function setCallBackUrl( $sCallBackUrl )
    {
        $this->_sCallBackUrl = $sCallBackUrl;
    }

    /**
     * Returns CallBack url
     *
     * @return string
     */
    public function getCallBackUrl()
    {
        return $this->_sCallBackUrl;
    }

    /**
     * Sets Cancel Url
     *
     * @param string $sCancelUrl
     */
    public function setCancelUrl( $sCancelUrl )
    {
        $this->_sCancelUrl = $sCancelUrl;
    }

    /**
     * Returns Cancel Url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_sCancelUrl;
    }

    /**
     * Sets Return Url
     *
     * @param string $sReturnUrl
     */
    public function setReturnUrl( $sReturnUrl )
    {
        $this->_sReturnUrl = $sReturnUrl;
    }

    /**
     * Returns Return Url
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_sReturnUrl;
    }

    /**
     * Sets whether to show basket in PayPal
     *
     * @param string $blShowCartInPayPal
     */
    public function setShowCartInPayPal( $blShowCartInPayPal )
    {
        $this->_blShowCartInPayPal = $blShowCartInPayPal;
    }

    /**
     * Returns whether to show basket in PayPal
     *
     * @return string
     */
    public function getShowCartInPayPal()
    {
        return $this->_blShowCartInPayPal;
    }

    /**
     * Sets Transaction mode
     *
     * @param string $sTransactionMode
     */
    public function setTransactionMode( $sTransactionMode )
    {
        $this->_sTransactionMode = $sTransactionMode;
    }

    /**
     * Returns Transaction mode
     *
     * @return string $sTransactionMode
     */
    public function getTransactionMode()
    {
        return $this->_sTransactionMode;
    }

    /**
     * Builds PayPal request for express checkout
     */
    public function buildExpressCheckoutRequest()
    {
        $this->addBaseParams();
        $this->addCallBackUrl();
        $this->addBasketParams();
        $this->addDescriptionParams();
        $this->turnOffShippingAddressCollection();
        $this->setMaximumOrderAmount();

        if ( $this->getShowCartInPayPal() ) {
            $this->addBasketItemParams();
        } else {
            $this->addBasketGrandTotalParams();
        }
        $this->addAddressParams();

        return $this->getPayPalRequest();
    }

    /**
     * Builds PayPal request for standard checkout
     */
    public function buildStandardCheckoutRequest()
    {
        $this->addBaseParams();
        $this->addBasketParams();
        $this->addDescriptionParams();
        $this->disableSelectingDifferentAddressInPayPal();
        $this->setMaximumOrderAmount();

        if ( $this->getShowCartInPayPal() ) {
            $this->addBasketItemParams();
        } else {
            $this->addBasketGrandTotalParams();
        }
        $this->addAddressParams();

        return $this->getPayPalRequest();
    }

    /**
     * Sets base parameters to request
     */
    public function addBaseParams()
    {
        $oRequest = $this->getPayPalRequest();
        $oPayPalConfig = $this->getPayPalConfig();

        $oRequest->setParameter( "CALLBACKVERSION", "84.0" );
        $oRequest->setParameter( "LOCALECODE", $this->getLang()->translateString( "OEPAYPAL_LOCALE" ) );
        // enabled guest buy (Buyer does not need to create a PayPal account to check out)
        $oRequest->setParameter( "SOLUTIONTYPE", ($oPayPalConfig->isGuestBuyEnabled() ? "Sole" : "Mark") );
        $oRequest->setParameter( "BRANDNAME", $oPayPalConfig->getBrandName() );
        $oRequest->setParameter( "CARTBORDERCOLOR", $oPayPalConfig->getBorderColor() );

        $oRequest->setParameter( "RETURNURL", $this->getReturnUrl() );
        $oRequest->setParameter( "CANCELURL", $this->getCancelUrl() );

        if ( $sLogoImage = $oPayPalConfig->getLogoUrl() ) {
            $oRequest->setParameter( "LOGOIMG", $sLogoImage );
        }

        $oRequest->setParameter( "PAYMENTREQUEST_0_PAYMENTACTION", $this->getTransactionMode() );
    }

    /**
     * Adds callback parameters to request
     */
    public function addCallBackUrl()
    {
        $oRequest = $this->getPayPalRequest();

        $oRequest->setParameter( "CALLBACK", $this->getCallbackUrl() );
        $oRequest->setParameter( "CALLBACKTIMEOUT", 6 );
    }

    /**
     * Turn off shipping address collection
     */
    public function turnOffShippingAddressCollection()
    {
        $this->getPayPalRequest()->setParameter( "NOSHIPPING", "2" );
    }

    /**
     * Disables selecting different address in PayPal side
     */
    public function disableSelectingDifferentAddressInPayPal()
    {
        $this->getPayPalRequest()->setParameter( "ADDROVERRIDE", "1" );
    }

    /**
    * Calculating maximum order amount
    * and adding all used discounts (needed because of bug in PayPal - somehow it substract discount from MAXAMT)
    * additionally +1 as PayPal recommends this value a little bit greater than original
     */
    public function setMaximumOrderAmount()
    {
        $oBasket = $this->getBasket();
        $oRequest = $this->getPayPalRequest();

        $oRequest->setParameter( "MAXAMT", $this->_formatFloat( ( $oBasket->getPrice()->getBruttoPrice() + $oBasket->getDiscountSumPayPalBasket() + $this->getMaxDeliveryAmount() + 1 ) ) );
    }

    /**
     * Sets basket parameters to request
     */
    public function addBasketParams()
    {
        $oRequest = $this->getPayPalRequest();
        $oBasket = $this->getBasket();

        $blVirtualBasket = $oBasket->isVirtualPayPalBasket();

        // only downloadable products? missing getter on oxBasket yet
        $oRequest->setParameter( "NOSHIPPING", $blVirtualBasket ? "1" : "0" );

        if ( $blVirtualBasket ) {
            $oRequest->setParameter( "REQCONFIRMSHIPPING", "0" );
        }
        // passing basket VAT (tax) value. It is required as in Net mode articles are without VAT, but basket is with VAT.
        // PayPal need this value to check if all articles sum match basket sum.
        if ( $oBasket->isCalculationModeNetto() ) {
            $oRequest->setParameter( "PAYMENTREQUEST_0_TAXAMT", $this->_formatFloat( $oBasket->getPayPalBasketVatValue() ) );
        }

        $oRequest->setParameter( "PAYMENTREQUEST_0_AMT", $this->_formatFloat( $oBasket->getPrice()->getBruttoPrice() ) );
        $oRequest->setParameter( "PAYMENTREQUEST_0_CURRENCYCODE", $oBasket->getBasketCurrency()->name );
        $oRequest->setParameter( "PAYMENTREQUEST_0_ITEMAMT", $this->_formatFloat( $oBasket->getSumOfCostOfAllItemsPayPalBasket() ) );
        $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPPINGAMT", $this->_formatFloat( $oBasket->getDeliveryCosts() ) );
        $oRequest->setParameter( "PAYMENTREQUEST_0_SHIPDISCAMT", $this->_formatFloat( $oBasket->getDiscountSumPayPalBasket() * -1 ) );

        $oDelivery = oxNew( "oxDeliverySet" );
        $sDeliveryName =  ( $oDelivery->load($oBasket->getShippingId()) ) ? $oDelivery->oxdeliveryset__oxtitle->value : "#1";

        $oRequest->setParameter( "L_SHIPPINGOPTIONISDEFAULT0", "true" );
        $oRequest->setParameter( "L_SHIPPINGOPTIONNAME0", $sDeliveryName );
        $oRequest->setParameter( "L_SHIPPINGOPTIONAMOUNT0", $this->_formatFloat( $oBasket->getDeliveryCosts() ) );
    }

    /**
     * Sets transaction description parameters
     */
    public function addDescriptionParams()
    {
        $oBasket = $this->getBasket();
        $oConfig = $this->getPayPalConfig();
        $oRequest = $this->getPayPalRequest();

        // description
        $sShopNameFull = $oConfig->getBrandName();
        $sShopName = substr( $sShopNameFull, 0, 70 );
        if ( $sShopNameFull != $sShopName ) {
            $sShopName .= "...";
        }

        $sSubj = sprintf( $this->getLang()->translateString( "OEPAYPAL_ORDER_SUBJECT" ), $sShopName, $oBasket->getFPrice(), $oBasket->getBasketCurrency()->name );
        $oRequest->setParameter( "PAYMENTREQUEST_0_DESC", $sSubj );
        $oRequest->setParameter( "PAYMENTREQUEST_0_CUSTOM", $sSubj );
    }

    /**
     * Sets basket items parameters to request
     */
    public function addBasketItemParams()
    {
        $oBasket = $this->getBasket();
        $oLang    = $this->getLang();
        $oRequest = $this->getPayPalRequest();

        $iPos = 0;
        foreach ( $oBasket->getContents() as $oBasketItem ) {
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_NAME{$iPos}", getStr()->html_entity_decode( $oBasketItem->getTitle() ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_AMT{$iPos}", $this->_formatFloat( $oBasketItem->getUnitPrice()->getPrice() ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_QTY{$iPos}", $oBasketItem->getAmount() );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_ITEMURL{$iPos}", $oBasketItem->getLink() );

            $oBasketProduct = $oBasketItem->getArticle();
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_NUMBER{$iPos}", $oBasketProduct->oxarticles__oxartnum->value );

            $iPos++;
        }

        //adding payment costs as product
        if ( $oBasket->getPayPalPaymentCosts() > 0 ) {
            $sPaymentTitle = $oLang->translateString( "OEPAYPAL_SURCHARGE" ) . " " . $oLang->translateString( "OEPAYPAL_TYPE_OF_PAYMENT" );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_NAME{$iPos}", $sPaymentTitle );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_AMT{$iPos}", $this->_formatFloat( $oBasket->getPayPalPaymentCosts() ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_QTY{$iPos}", 1 );

            $iPos++;
        }

        //adding wrapping as product
        if ( $oBasket->getPayPalWrappingCosts() > 0 ) {
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_NAME{$iPos}", $oLang->translateString( "OEPAYPAL_GIFTWRAPPER" ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_AMT{$iPos}", $this->_formatFloat( $oBasket->getPayPalWrappingCosts() ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_QTY{$iPos}", 1 );

            $iPos++;
        }

        //adding greeting card as product
        if ( $oBasket->getPayPalGiftCardCosts() > 0 ) {
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_NAME{$iPos}", $oLang->translateString( "OEPAYPAL_GREETING_CARD" ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_AMT{$iPos}", $this->_formatFloat( $oBasket->getPayPalGiftCardCosts() ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_QTY{$iPos}", 1 );

            $iPos++;
        }

        //adding trusted shops protection as product
        if ( $oBasket->getPayPalTsProtectionCosts() > 0 ) {
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_NAME{$iPos}", $oLang->translateString( "OEPAYPAL_TRUSTED_SHOP_PROTECTION" ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_AMT{$iPos}", $this->_formatFloat( $oBasket->getPayPalTsProtectionCosts() ) );
            $oRequest->setParameter( "L_PAYMENTREQUEST_0_QTY{$iPos}", 1 );
        }
    }

    /**
     * Sets basket Grand Total params to request
     */
    public function addBasketGrandTotalParams()
    {
        $oBasket = $this->getBasket();
        $oRequest = $this->getPayPalRequest();

        $oRequest->setParameter( "L_PAYMENTREQUEST_0_NAME0", $this->getLang()->translateString( "OEPAYPAL_GRAND_TOTAL" ) );
        $oRequest->setParameter( "L_PAYMENTREQUEST_0_AMT0", $this->_formatFloat( $oBasket->getSumOfCostOfAllItemsPayPalBasket() ) );
        $oRequest->setParameter( "L_PAYMENTREQUEST_0_QTY0", 1 );
    }

    /**
     * Sets Address parameters to request
     */
    public function addAddressParams()
    {
        $oUser = $this->getUser();
        if ( !$oUser ) {
            return;
        }
        $oRequest = $this->getPayPalRequest();

        $oRequest->setParameter( "EMAIL", $oUser->oxuser__oxusername->value );

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