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
 * PayPal order payment status list class
 */
class oePayPalOrderPaymentStatusList extends oePayPalList
{
    /**
     * All available statuses
     *
     * @return array
     */
    protected $_aArray = array(
            'completed',
            'pending',
            'canceled',
            'failed'
        );

    /**
     * Available statuses depending on action
     *
     * @var array
     */
    protected $_aAvailableStatuses = array(
        'capture' => array( 'completed' ),
        'capture_partial' => array( 'completed', 'pending' ),
        'refund' => array( 'completed', 'pending', 'canceled' ),
        'refund_partial' => array( 'completed', 'pending', 'canceled' ),
        'void' => array( 'completed', 'pending', 'canceled' ),
    );

    /**
     * Returns the list of available statuses to choose from for admin
     *
     * @param $sAction
     *
     * @return array
     */
    public function getAvailableStatuses( $sAction )
    {
        $aList = $this->_aAvailableStatuses[$sAction];

        return $aList? $aList : array();
    }
}