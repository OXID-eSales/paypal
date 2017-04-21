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

namespace OxidEsales\PayPalModule\Model\Action\Handler;

/**
 * PayPal order action class
 */
abstract class OrderActionHandler
{
    /**
     * @var object
     */
    protected $_oData = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\PayPalService
     */
    protected $_oPayPalService = null;

    /**
     * PayPal order
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $_oPayPalRequestBuilder = null;

    /**
     * Sets data object.
     *
     * @param object $oData
     */
    public function __construct($oData)
    {
        $this->_oData = $oData;
    }

    /**
     * Returns Data object
     *
     * @return object
     */
    public function getData()
    {
        return $this->_oData;
    }

    /**
     * Sets PayPal request builder
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder $oBuilder
     */
    public function setPayPalRequestBuilder($oBuilder)
    {
        $this->_oPayPalRequestBuilder = $oBuilder;
    }

    /**
     * Returns PayPal request builder
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder
     */
    public function getPayPalRequestBuilder()
    {
        if ($this->_oPayPalRequestBuilder === null) {
            $this->_oPayPalRequestBuilder = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder::class);
        }

        return $this->_oPayPalRequestBuilder;
    }

    /**
     * Sets PayPal service
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $oService
     */
    public function setPayPalService($oService)
    {
        $this->_oPayPalService = $oService;
    }

    /**
     * Returns PayPal service
     *
     * @return \OxidEsales\PayPalModule\Core\PayPalService
     */
    public function getPayPalService()
    {
        if ($this->_oPayPalService === null) {
            $this->_oPayPalService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        }

        return $this->_oPayPalService;
    }
}
