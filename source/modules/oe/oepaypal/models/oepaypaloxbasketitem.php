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
 * PayPal oxBasketItem class
 */
class oePayPalOxBasketItem extends oePayPalOxBasketItem_parent
{
    /**
     * Checks if validation should be skipped when getting article object.
     * This is done only when PayPal finalizes order.
     *
     * @param bool   $blCheckProduct       checks if product is buyable and visible
     * @param string $sProductId           product id
     * @param bool   $blDisableLazyLoading disable lazy loading
     *
     * @throws oxArticleException, oxNoArticleException exception
     *
     * @return oxArticle
     */
    public function getArticle( $blCheckProduct = true, $sProductId = null, $blDisableLazyLoading = false )
    {
        $sFncName = $this->getConfig()->getActiveView()->getFncName();

        if ( $sFncName == "doExpressCheckoutPayment" ) {
            // disabling product validation checking if finalizing PayPal payment (#4271)
            return parent::getArticle( false, $sProductId, $blDisableLazyLoading );
        }

        return parent::getArticle( $blCheckProduct, $sProductId, $blDisableLazyLoading );
    }
}
