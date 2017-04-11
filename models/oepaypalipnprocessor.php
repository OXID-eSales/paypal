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
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * PayPal IPN processor class.
 */
class oePayPalIPNProcessor
{
    /**
     * PayPal request handler.
     *
     * @var oePayPalRequest
     */
    protected $_oRequest = null;

    /**
     * @var oePayPalIPNPaymentBuilder
     */
    protected $_oPaymentBuilder = null;

    /**
     * @var oePayPalOrderManager
     */
    protected $_oOrderManager = null;

    /**
     * Set object oePayPalRequest.
     *
     * @param oePayPalRequest $oRequest object to set.
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Create object oePayPalRequest to get PayPal request information.
     *
     * @return oeRequest
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
     * @param oePayPalIPNPaymentBuilder $oPaymentBuilder
     */
    public function setPaymentBuilder($oPaymentBuilder)
    {
        $this->_oPaymentBuilder = $oPaymentBuilder;
    }

    /**
     * Creates oePayPalIPNPaymentBuilder, sets if it was not set and than returns it.
     *
     * @return oePayPalIPNPaymentBuilder
     */
    public function getPaymentBuilder()
    {
        if (is_null($this->_oPaymentBuilder)) {
            $this->_oPaymentBuilder = oxNew('oePayPalIPNPaymentBuilder');
        }

        return $this->_oPaymentBuilder;
    }

    /**
     * Sets order manager.
     *
     * @param oePayPalOrderManager $oPayPalOrderManager
     */
    public function setOrderManager($oPayPalOrderManager)
    {
        $this->_oOrderManager = $oPayPalOrderManager;
    }

    /**
     * Returns order manager.
     *
     * @return oePayPalOrderManager
     */
    public function getOrderManager()
    {
        if (is_null($this->_oOrderManager)) {
            $this->_oOrderManager = oxNew('oePayPalOrderManager');
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
