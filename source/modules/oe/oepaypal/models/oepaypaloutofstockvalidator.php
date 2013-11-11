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
 * PayPal out of stock validator class
 */
class oePayPalOutOfStockValidator {

    /**
     * Basket object
     *
     * @var mixed
     */
    private $_oBasket;

    /**
     * Level of empty stock level
     *
     * @var integer
     */
    private $_iEmptyStockLevel;

    /**
     * @param mixed $iEmptyStockLevel
     */
    public function setEmptyStockLevel( $iEmptyStockLevel )
    {
        $this->_iEmptyStockLevel = $iEmptyStockLevel;
    }

    /**
     * @return mixed
     */
    public function getEmptyStockLevel()
    {
        return $this->_iEmptyStockLevel;
    }

    /**
     * @param mixed $oBasket
     */
    public function setBasket( $oBasket )
    {
        $this->_oBasket = $oBasket;
    }

    /**
     * @return mixed
     */
    public function getBasket()
    {
        return $this->_oBasket;
    }

    /**
     * Checks if basket has Articles that are out of stock
     */
    public function hasOutOfStockArticles()
    {
        $blResult = false;

        $aBasketContents = $this->getBasket()->getContents();

        foreach ( $aBasketContents as $oBasketItem ) {
                $oArticle = $oBasketItem->getArticle();
                if ( ( $oArticle->getStockAmount() - $oBasketItem->getAmount() ) < $this->getEmptyStockLevel() ) {
                    $blResult = true;
                    break;
                }
        }

        return $blResult;
    }

}