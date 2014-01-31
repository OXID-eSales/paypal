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
 * PayPal order action manager class
 */
class oePayPalOrderActionManager
{
    /**
     * States related with transaction mode
     *
     * @var oePayPalPayPalOrder
     */
    protected $_aAvailableActions = array(
        'Sale' => array(),
        'Authorization' => array( 'capture', 'reauthorize', 'void' ),
    );

    /**
     * Order object
     *
     * @var oePayPalPayPalOrder
     */
    protected $_oOrder = null;

    /**
     * Sets order
     *
     * @param oePayPalPayPalOrder $oOrder
     */
    public function setOrder( $oOrder )
    {
        $this->_oOrder = $oOrder;
    }

    /**
     * Returns order
     *
     * @return oePayPalPayPalOrder
     */
    public function getOrder()
    {
        return $this->_oOrder;
    }

    /**
     * Return state for given transaction mode
     *
     * @param string $sMode transaction mode
     *
     * @return array
     */
    protected function _getAvailableAction( $sMode )
    {
        $aActions = $this->_aAvailableActions[ $sMode ];

        return $aActions? $aActions : array();
    }

    /**
     * Checks whether action is available for given order
     *
     * @param $sAction
     * @return bool
     */
    public function isActionAvailable( $sAction )
    {
        $oOrder = $this->getOrder();

        $aAvailableActions = $this->_getAvailableAction( $oOrder->getTransactionMode() );

        $blIsAvailable = in_array( $sAction, $aAvailableActions );

        if ( $blIsAvailable ) {
            $blIsAvailable = false;

            switch ($sAction) {
                case 'capture':
                case 'reauthorize':
                case 'void':
                    if ( $oOrder->getRemainingOrderSum() > 0 && $oOrder->getVoidedAmount() < $oOrder->getRemainingOrderSum() ) {
                        $blIsAvailable = true;
                    }
                    break;
            }
        }

        return $blIsAvailable;
    }

}