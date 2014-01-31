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
 * PayPal oxArticle class
 */
class oePayPalOxArticle extends oePayPalOxArticle_parent
{
    /**
     * Check if Product is virtual: is non material and is downloadable
     *
     * @return bool
     */
    public function isVirtualPayPalArticle()
    {
        $blVirtual = true;

        // non material products
        if ( !$this->oxarticles__oxnonmaterial->value ) {
            $blVirtual = false;
        } elseif ( isset( $this->oxarticles__oxisdownloadable ) &&
                   !$this->oxarticles__oxisdownloadable->value ) {
            $blVirtual = false;
        }

        return $blVirtual;
    }

    /**
     * Gets stock amount for article
     * @return float
     */
    public function getStockAmount()
    {
        return $this->oxarticles__oxstock->value;
    }
}