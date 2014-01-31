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
 * Basket component
 */
class oePayPalOxcmp_Basket extends oePayPalOxcmp_Basket_parent
{

    /**
     * Show ECS PopUp
     *
     * @var bool
     */
    protected $_blShopPopUp = false;

    /**
     * Method returns URL to checkout products OR to show popup.
     *
     * @return string
     */
    public function actionExpressCheckoutFromDetailsPage()
    {
        $oValidator = $this->_getValidator();
        $oCurrentArticle = $this->_getCurrentArticle();
        $oValidator->setItemToValidate( $oCurrentArticle );
        $oValidator->setBasket( $this->getSession()->getBasket() );
        if( $oValidator->isArticleValid() ) {
            //Make express checkout
            $sRes = $this->actionAddToBasketAndGoToCheckout();
        } else {
            $sRes = $this->_getRedirectUrl();
            //if amount is more than 0, do not redirect, show ESC popup instead
            if ( $oCurrentArticle->getArticleAmount() > 0 ) {
                $this->_blShopPopUp = true;
                $sRes = null;
            }
        }

        return $sRes;
    }

    /**
     * Returns whether ECS popup should be shown
     *
     * @return bool
     */
    public function shopECSPopUp()
    {
        return $this->_blShopPopUp;
    }

    /**
     * Action method to add product to basket and return checkout URL.
     *
     * @return string
     */
    public function actionAddToBasketAndGoToCheckout()
    {
        parent::tobasket();
        return $this->_getExpressCheckoutUrl();
    }

    /**
     * Action method to return checkout URL.
     *
     * @return string
     */
    public function actionNotAddToBasketAndGoToCheckout()
    {
        return $this->_getExpressCheckoutUrl();
    }

    /**
     * Returns express checkout URL
     *
     * @return string
     */
    protected function _getExpressCheckoutUrl()
    {
        return 'oePayPalExpressCheckoutDispatcher&fnc=setExpressCheckout&displayCartInPayPal=' . ( int )$this->_getRequest()->getPostParameter( 'displayCartInPayPal' ) . '&oePayPalCancelURL=' . $this->getPayPalCancelURL();
    }

    /**
     * Method returns serialized current article params.
     *
     * @return string
     */
    public function getCurrentArticleInfo()
    {
        $aProducts = $this->_getItems();
        $sCurrentArticleId = $this->getConfig()->getRequestParameter( 'aid' );
        $aParams = null;
        if ( !is_null( $aProducts[$sCurrentArticleId] ) ) {
            $aParams = $aProducts[$sCurrentArticleId];
        }

        return $aParams;
    }

    /**
     * Method sets params for article and returns it's object.
     *
     * @return oePayPalArticleToExpressCheckoutCurrentItem
     */
    protected  function _getCurrentArticle()
    {
        $oCurrentItem = oxNew( 'oePayPalArticleToExpressCheckoutCurrentItem' );
        $sCurrentArticleId = $this->_getRequest()->getPostParameter( 'aid' );
        $aProducts = $this->_getItems();
        $aProductInfo = $aProducts[$sCurrentArticleId];
        $oCurrentItem->setArticleId( $sCurrentArticleId );
        $oCurrentItem->setSelectList( $aProductInfo['sel'] );
        $oCurrentItem->setPersistParam( $aProductInfo['persparam'] );
        $oCurrentItem->setArticleAmount( $aProductInfo['am'] );

        return $oCurrentItem;
    }

    /**
     * Method returns request object.
     *
     * @return oePayPalRequest
     */
    protected function _getRequest()
    {
        return oxNew( 'oePayPalRequest' );
    }

    /**
     * Method sets params for validator and returns it's object.
     *
     * @return oePayPalArticleToExpressCheckoutValidator
     */
    protected function _getValidator()
    {
        $oValidator = oxNew( 'oePayPalArticleToExpressCheckoutValidator' );

        return $oValidator;
    }

    /**
     * Changes oePayPalCancelURL by changing popup showing parameter.
     *
     * @return string
     */
    public function getPayPalCancelURL()
    {
        $sUrl = $this->_formatUrl( $this->_getRedirectUrl() );
        $sReplacedURL = str_replace( 'showECSPopup=1', 'showECSPopup=0', $sUrl );

        return urlencode( $sReplacedURL );
    }

    /**
     * Formats Redirect URL to normal url
     *
     * @param string $sUnformedUrl
     * @return string
     */
    protected function _formatUrl( $sUnformedUrl )
    {
        $myConfig  = $this->getConfig();
        $aParams = explode( '?', $sUnformedUrl );
        $sPageParams = isset( $aParams[1] )?$aParams[1]:null;
        $aParams    = explode( '/', $aParams[0] );
        $sClassName = $aParams[0];

        $sHeader  = ( $sClassName )?"cl=$sClassName&":'';  // adding view name
        $sHeader .= ( $sPageParams )?"$sPageParams&":'';   // adding page params
        $sHeader .= $this->getSession()->sid();            // adding session Id

        $sUrl = $myConfig->getCurrentShopUrl($this->isAdmin());

        $sUrl = "{$sUrl}index.php?{$sHeader}";

        $sUrl = oxRegistry::get("oxUtilsUrl")->processUrl($sUrl);

        if ( oxRegistry::getUtils()->seoIsActive() && $sSeoUrl = oxRegistry::get( "oxSeoEncoder" )->getStaticUrl( $sUrl ) ) {
            $sUrl = $sSeoUrl;
        }

        return $sUrl;
    }
}