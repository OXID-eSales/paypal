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

namespace OxidEsales\PayPalModule\Model\Action\Handler;

/**
 * PayPal order action class
 */
abstract class OrderActionHandler
{
    /**
     * @var object
     */
    protected $data = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\PayPalService
     */
    protected $payPalService = null;

    /**
     * PayPal order
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $payPalRequestBuilder = null;

    /**
     * Sets data object.
     *
     * @param object $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Returns Data object
     *
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets PayPal request builder
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder $builder
     */
    public function setPayPalRequestBuilder($builder)
    {
        $this->payPalRequestBuilder = $builder;
    }

    /**
     * Returns PayPal request builder
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder
     */
    public function getPayPalRequestBuilder()
    {
        if ($this->payPalRequestBuilder === null) {
            $this->payPalRequestBuilder = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder::class);
        }

        return $this->payPalRequestBuilder;
    }

    /**
     * Sets PayPal service
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $service
     */
    public function setPayPalService($service)
    {
        $this->payPalService = $service;
    }

    /**
     * Returns PayPal service
     *
     * @return \OxidEsales\PayPalModule\Core\PayPalService
     */
    public function getPayPalService()
    {
        if ($this->payPalService === null) {
            $this->payPalService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        }

        return $this->payPalService;
    }
}
