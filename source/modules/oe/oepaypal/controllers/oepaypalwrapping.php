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
 * PayPal Wrapping class
 */
class oePayPalWrapping extends oePayPalWrapping_parent
{
    /**
     * Checks if payment action is processed by PayPal
     *
     * @return bool
     */
    public function isPayPal()
    {
        return ( $this->getSession()->getVariable( "paymentid" ) == "oxidpaypal" ) ? true : false;
    }

    /**
     * Detects is current payment must be processed by PayPal and instead of standard validation
     * redirects to standard PayPal dispatcher
     *
     * @return bool
     */
    public function changeWrapping()
    {
        $sReturn = parent::changeWrapping();

        // in case user adds wrapping, basket info must be resubmitted..
        if ( $this->isPayPal() ) {
            $iPayPalType = (int) $this->getSession()->getVariable( "oepaypal" );

            if ( $iPayPalType == 1 ) {
                $sReturn = "payment";
            } else {
                $sReturn = "basket";
            }
        }

        return  $sReturn;
    }
}