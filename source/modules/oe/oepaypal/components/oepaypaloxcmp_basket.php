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
            //redirect back to details page and display popup if amount is more than 0
            if ( $oCurrentArticle->getArticleAmount() > 0 ) {
                $sRes .= "&showECSPopup=1&ECSArticle={$this->_getSerializedCurrentArticleInfo()}&displayCartInPayPal=" . ( ( int ) $this->_getRequest()->getPostParameter( 'displayCartInPayPal' ) );
            }
        }

        return $sRes;
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
        return 'oePayPalExpressCheckoutDispatcher&fnc=setExpressCheckout&displayCartInPayPal=' . ( int )$this->_getRequest()->getPostParameter( 'displayCartInPayPal' ) . '&oePayPalCancelURL=' . $this->_getPayPalCancelURL();
    }

    /**
     * Method returns serialized current article params.
     *
     * @return string
     */
    protected function _getSerializedCurrentArticleInfo()
    {
        $aProducts = $this->_getItems();
        $sCurrentArticleId = $this->getConfig()->getRequestParameter( 'aid' );
        $sSerializedParams = null;
        if ( !is_null( $aProducts[$sCurrentArticleId] ) ) {
            $sSerializedParams = serialize( $aProducts[$sCurrentArticleId] );
        }

        return $sSerializedParams;
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
    protected function _getPayPalCancelURL()
    {
        $sURL = $this->_getRequest()->getPostParameter( 'oePayPalCancelURL' );
        $sReplacedURL = str_replace( 'showECSPopup=1', 'showECSPopup=0', $sURL );

        return urlencode( $sReplacedURL );
    }
}