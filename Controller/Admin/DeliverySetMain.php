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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Controller\Admin;

/**
 * Adds additional functionality needed for PayPal when managing delivery sets.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\DeliverySetMain
 */
class DeliverySetMain extends DeliverySetMain_parent
{
    /**
     * Add default PayPal mobile payment.
     *
     * @return string
     */
    public function render()
    {
        $sTemplate = parent::render();

        $sDeliverySetId = $this->getEditObjectId();
        if ($sDeliverySetId != "-1" && isset($sDeliverySetId)) {
            /** @var \OxidEsales\PayPalModule\Core\Config $oConfig */
            $oConfig = oxNew(\OxidEsales\PayPalModule\Core\Config::class);

            $blIsPayPalDefaultMobilePayment = ($sDeliverySetId == $oConfig->getMobileECDefaultShippingId());

            $this->_aViewData['blIsPayPalDefaultMobilePayment'] = $blIsPayPalDefaultMobilePayment;
        }

        return $sTemplate;
    }

    /**
     * Saves default PayPal mobile payment.
     */
    public function save()
    {
        parent::save();

        $oConfig = $this->getConfig();
        /** @var \OxidEsales\PayPalModule\Core\Config $oPayPalConfig */
        $oPayPalConfig = oxNew(\OxidEsales\PayPalModule\Core\Config::class);

        $sDeliverySetId = $this->getEditObjectId();
        $blDeliverySetMarked = (bool) $oConfig->getRequestParameter('isPayPalDefaultMobilePayment');
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        if ($blDeliverySetMarked && $sDeliverySetId != $sMobileECDefaultShippingId) {
            $this->_saveECDefaultShippingId($oConfig, $sDeliverySetId, $oPayPalConfig);
        } elseif (!$blDeliverySetMarked && $sDeliverySetId == $sMobileECDefaultShippingId) {
            $this->_saveECDefaultShippingId($oConfig, '', $oPayPalConfig);
        }
    }

    /**
     * Save default shipping id.
     *
     * @param \OxidEsales\Eshop\Core\Config        $oConfig       Config object to save.
     * @param string                               $sShippingId   Shipping id.
     * @param \OxidEsales\PayPalModule\Core\Config $oPayPalConfig PayPal config.
     */
    protected function _saveECDefaultShippingId($oConfig, $sShippingId, $oPayPalConfig)
    {
        $sPayPalModuleId = 'module:' . $oPayPalConfig->getModuleId();
        $oConfig->saveShopConfVar('string', 'sOEPayPalMECDefaultShippingId', $sShippingId, null, $sPayPalModuleId);
    }
}
