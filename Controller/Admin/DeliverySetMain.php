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
 * @copyright (C) OXID eSales AG 2003-2018
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
        $template = parent::render();

        $deliverySetId = $this->getEditObjectId();
        if ($deliverySetId != "-1" && isset($deliverySetId)) {
            /** @var \OxidEsales\PayPalModule\Core\Config $config */
            $config = oxNew(\OxidEsales\PayPalModule\Core\Config::class);

            $isPayPalDefaultMobilePayment = ($deliverySetId == $config->getMobileECDefaultShippingId());

            $this->_aViewData['isPayPalDefaultMobilePayment'] = $isPayPalDefaultMobilePayment;
        }

        return $template;
    }

    /**
     * Saves default PayPal mobile payment.
     */
    public function save()
    {
        parent::save();

        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        /** @var \OxidEsales\PayPalModule\Core\Config $payPalConfig */
        $payPalConfig = oxNew(\OxidEsales\PayPalModule\Core\Config::class);

        $deliverySetId = $this->getEditObjectId();
        $deliverySetMarked = (bool) $config->getRequestParameter('isPayPalDefaultMobilePayment');
        $mobileECDefaultShippingId = $payPalConfig->getMobileECDefaultShippingId();

        if ($deliverySetMarked && $deliverySetId != $mobileECDefaultShippingId) {
            $this->saveECDefaultShippingId($config, $deliverySetId, $payPalConfig);
        } elseif (!$deliverySetMarked && $deliverySetId == $mobileECDefaultShippingId) {
            $this->saveECDefaultShippingId($config, '', $payPalConfig);
        }
    }

    /**
     * Save default shipping id.
     *
     * @param \OxidEsales\Eshop\Core\Config        $config       Config object to save.
     * @param string                               $shippingId   Shipping id.
     * @param \OxidEsales\PayPalModule\Core\Config $payPalConfig PayPal config.
     */
    protected function saveECDefaultShippingId($config, $shippingId, $payPalConfig)
    {
        $payPalModuleId = 'module:' . $payPalConfig->getModuleId();
        $config->saveShopConfVar('string', 'sOEPayPalMECDefaultShippingId', $shippingId, null, $payPalModuleId);
    }
}
