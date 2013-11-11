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
 * PayPal Current item Article validator class
 */
class oePayPalArticleToExpressCheckoutValidator
{
    /**
     * Item that will be validated
     * @var object
     */
    protected $_oItemToValidate;

    /**
     * User basket
     * @var object
     */
    protected $_oBasket;

    /**
     *Sets current item of details page
     *
     * @param object $oItemToValidate
     */
    public function setItemToValidate( $oItemToValidate )
    {
        $this->_oItemToValidate = $oItemToValidate;
    }

    /**
     * Returns details page current item
     *
     * @return oePayPalArticleToExpressCheckoutCurrentItem
     */
    public function getItemToValidate()
    {
        return $this->_oItemToValidate;
    }

    /**
     * Method sets basket object
     * @param oxBasket
     */
    public function setBasket( $oBasket )
    {
        $this->_oBasket = $oBasket;
    }

    /**
     * Methods returns basket object
     *
     * @return oxBasket
     */
    public function getBasket()
    {
        return $this->_oBasket;
    }

    /**
     * Method returns if article valid
     *
     * @return bool
     */
    public function isArticleValid()
    {
        $blValid = true;
        if ( $this->_isArticleAmountZero() || $this->_isSameItemInBasket() ) {
            $blValid = false;
        }

        return $blValid;
    }

    /**
     * Check if same article is in basket
     *
     * @return bool
     */
    protected function _isSameItemInBasket()
    {
        $aBasketContents = $this->getBasket()->getContents();
        foreach ( $aBasketContents as $oBasketItem ) {
            if( $this->_isArticleParamsEqual( $oBasketItem ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if Article params equals with current items params
     * @param oePayPalOxBasketItem $oBasketItem
     *
     * @return bool
     */
    protected function _isArticleParamsEqual( $oBasketItem )
    {
        return ( $oBasketItem->getProductId() == $this->getItemToValidate()->getArticleId() &&
            $oBasketItem->getPersParams() == $this->getItemToValidate()->getPersistParam() &&
            $oBasketItem->getSelList() == $this->getItemToValidate()->getSelectList() );
    }

    /**
     * Checks if article amount 0
     *
     * @return bool
     */
    protected function _isArticleAmountZero()
    {
        $iArticleAmount = $this->getItemToValidate()->getArticleAmount();
        return 0 == $iArticleAmount;
    }

}