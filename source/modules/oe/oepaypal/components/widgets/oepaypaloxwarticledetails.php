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
 * Article box widget
 */
class oePayPalOxwArticleDetails extends oePayPalOxwArticleDetails_parent
{
    /**
     * Returns products amount to .tpl pages.
     *
     * @return int
     */
    public function oePayPalGetArticleAmount()
    {
        $aArticle = $this->_oePayPalGetECSArticle();

        return isset( $aArticle['am'] ) ? ( int ) $aArticle['am'] : 1;
    }

    /**
     * Returns persistent parameter.
     *
     * @return string
     */
    public function oePayPalGetPersistentParam()
    {
        $aArticle = $this->_oePayPalGetECSArticle();

        return $aArticle['persparam']['details'];
    }

    /**
     * Returns selections array.
     *
     * @return array
     */
    public function oePayPalGetSelection()
    {
        $aArticle = $this->_oePayPalGetECSArticle();

        return $aArticle['sel'];
    }

    /**
     * Checks if showECSPopup parameter was passed.
     *
     * @return bool
     */
    public function oePayPalShowECSPopup()
    {
        return $this->getComponent( 'oxcmp_basket' )->shopECSPopUp();
    }

    /**
     * Checks if showECSPopup parameter was passed.
     *
     * @return bool
     */
    public function oePayPalGetCancelUrl()
    {
        return $this->getComponent( 'oxcmp_basket' )->getPayPalCancelURL();
    }

    /**
     * Checks if displayCartInPayPal parameter was passed.
     *
     * @return bool
     */
    public function oePayPalDisplayCartInPayPal()
    {
        $blDisplayCartInPayPal = false;
        if ( $this->_oePayPalGetRequest()->getPostParameter( 'displayCartInPayPal' ) ) {
            $blDisplayCartInPayPal = true;
        }

        return $blDisplayCartInPayPal;
    }

    /**
     * Method returns request object.
     *
     * @return oePayPalRequest
     */
    protected function _oePayPalGetRequest()
    {
        return oxNew( 'oePayPalRequest' );
    }

    /**
     * Gets ECSArticle, unserializes and returns it.
     *
     * @return array
     */
    protected function _oePayPalGetECSArticle()
    {
        $aProducts = $this->getComponent( 'oxcmp_basket' )->getCurrentArticleInfo();
        return $aProducts;
    }

}