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
 * PayPal Order payment action manager class
 */
class oePayPalOrderPaymentActionManager
{

    /**
     * Array of available actions for payment action
     *
     * @var array
     */
    protected $_availableActions = array(
        "capture" => array(
            "Completed" => array(
                'refund'
            )
        )
    );

    /**
     * Order object
     *
     * @var oePayPalOrderPayment
     */
    protected $_oPayment = null;

    /**
     * Sets order
     *
     * @param oePayPalOxOrder $oPayment
     */
    public function setPayment( $oPayment )
    {
        $this->_oPayment = $oPayment;
    }

    /**
     * Returns order
     *
     * @return oePayPalOrderPayment
     */
    public function getPayment()
    {
        return $this->_oPayment;
    }

    /**
     * Returns available actions for given payment action
     *
     * @param $sPaymentAction
     * @param $sPaymentStatus
     *
     * @return array
     */
    protected function _getAvailableActions( $sPaymentAction, $sPaymentStatus )
    {
        $aActions = $this->_availableActions[ $sPaymentAction ][ $sPaymentStatus ];
        return $aActions? $aActions : array();
    }


    /**
     * Checks whether action is available for given order
     *
     * @param $oPayment
     * @param $sAction
     *
     * @return bool
     */
    public function isActionAvailable( $sAction, $oPayment = null )
    {
        if ( $oPayment ) {
            $this->setPayment( $oPayment );
        }

        $oPayment = $this->getPayment();

        $blIsAvailable = in_array($sAction, $this->_getAvailableActions( $oPayment->getAction(), $oPayment->getStatus() ) );

         if ( $blIsAvailable ) {
             $blIsAvailable = false;

             switch ( $sAction ) {
                 case 'refund':
                     $blIsAvailable = ( $oPayment->getAmount() > $oPayment->getRefundedAmount() );
                     break;
             }
         }

        return $blIsAvailable;
    }

}