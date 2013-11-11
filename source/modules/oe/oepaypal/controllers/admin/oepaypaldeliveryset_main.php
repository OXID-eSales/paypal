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

class oePayPalDeliverySet_Main extends oePayPalDeliverySet_Main_parent
{
    /**
     * Add default PayPal mobile payment.
     *
     * @return string
     */
    public function render()
    {
        $sTemplate =  parent::render();

        $sDeliverySetId = $this->getEditObjectId();
        if ( $sDeliverySetId != "-1" && isset( $sDeliverySetId ) ) {
            $oConfig = oxNew( 'oePayPalConfig' );

            $blIsPayPalDefaultMobilePayment = ( $sDeliverySetId == $oConfig->getMobileECDefaultShippingId() );

            $this->_aViewData[ 'blIsPayPalDefaultMobilePayment' ] = $blIsPayPalDefaultMobilePayment;
        }

        return $sTemplate;
    }

    /**
     * Saves default PayPal mobile payment.
     *
     * @return mixed
     */
    public function save()
    {
        parent::save();

        $oConfig = $this->getConfig();
        $oPayPalConfig = oxNew( 'oePayPalConfig' );

        $sDeliverySetId = $this->getEditObjectId();
        $blDeliverySetMarked = (bool) $oConfig->getRequestParameter( 'isPayPalDefaultMobilePayment' );
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        if( $blDeliverySetMarked && $sDeliverySetId != $sMobileECDefaultShippingId ) {
            $this->_saveECDefaultShippingId( $oConfig, $sDeliverySetId, $oPayPalConfig );
        } elseif ( !$blDeliverySetMarked  && $sDeliverySetId == $sMobileECDefaultShippingId) {
            $this->_saveECDefaultShippingId( $oConfig, '', $oPayPalConfig );
        }
    }

    /**
     * Save default shipping id.
     *
     * @param oxConfig $oConfig config object to save.
     * @param string $sShippingId shipping id.
     * @param oePayPalConfig $oPayPalConfig PayPal config.
     */
    protected function _saveECDefaultShippingId( $oConfig, $sShippingId, $oPayPalConfig )
    {
        $sPayPalModuleId = 'module:' . $oPayPalConfig->getModuleId();
        $oConfig->saveShopConfVar( 'string', 'sOEPayPalMECDefaultShippingId', $sShippingId, null, $sPayPalModuleId );
    }
}