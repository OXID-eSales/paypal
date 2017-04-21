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

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal IPN processor class.
 */
class IPNProcessor
{
    /**
     * PayPal request handler.
     *
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $_oRequest = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\IPNPaymentBuilder
     */
    protected $_oPaymentBuilder = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\OrderManager
     */
    protected $_oOrderManager = null;

    /**
     * Set object \OxidEsales\PayPalModule\Core\Request.
     *
     * @param \OxidEsales\PayPalModule\Core\Request $oRequest object to set.
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Create object \OxidEsales\PayPalModule\Core\Request to get PayPal request information.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Sets language object.
     *
     * @param \OxidEsales\Eshop\Core\Language $oLang
     */
    public function setLang($oLang)
    {
        $this->_oLang = $oLang;
    }

    /**
     * Returns language object.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    public function getLang()
    {
        return $this->_oLang;
    }

    /**
     * Sets payment builder.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNPaymentBuilder $oPaymentBuilder
     */
    public function setPaymentBuilder($oPaymentBuilder)
    {
        $this->_oPaymentBuilder = $oPaymentBuilder;
    }

    /**
     * Creates \OxidEsales\PayPalModule\Model\IPNPaymentBuilder, sets if it was not set and than returns it.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNPaymentBuilder
     */
    public function getPaymentBuilder()
    {
        if (is_null($this->_oPaymentBuilder)) {
            $this->_oPaymentBuilder = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentBuilder::class);
        }

        return $this->_oPaymentBuilder;
    }

    /**
     * Sets order manager.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderManager $oPayPalOrderManager
     */
    public function setOrderManager($oPayPalOrderManager)
    {
        $this->_oOrderManager = $oPayPalOrderManager;
    }

    /**
     * Returns order manager.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderManager
     */
    public function getOrderManager()
    {
        if (is_null($this->_oOrderManager)) {
            $this->_oOrderManager = oxNew(\OxidEsales\PayPalModule\Model\OrderManager::class);
        }

        return $this->_oOrderManager;
    }

    /**
     * Initiate payment status changes according to IPN information.
     */
    public function process()
    {
        $oLang = $this->getLang();
        $oRequest = $this->getRequest();
        $oPaymentBuilder = $this->getPaymentBuilder();
        $oPayPalOrderManager = $this->getOrderManager();

        // Create Payment from Request.
        $oPaymentBuilder->setLang($oLang);
        $oPaymentBuilder->setRequest($oRequest);
        $oOrderPayment = $oPaymentBuilder->buildPayment();

        $oPayPalOrderManager->setOrderPayment($oOrderPayment);
        $blProcessSuccess = $oPayPalOrderManager->updateOrderStatus();

        return $blProcessSuccess;
    }
}
