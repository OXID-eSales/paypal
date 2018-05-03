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
 * PayPal order action reauthorize class
 */
class OrderReauthorizeActionHandler extends \OxidEsales\PayPalModule\Model\Action\Handler\OrderActionHandler
{
    /**
     * PayPal Request.
     *
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $payPalRequest = null;

    /**
     * Returns PayPal response; initiates if not set.
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund|\OxidEsales\PayPalModule\Model\Response\Response
     */
    public function getPayPalResponse()
    {
        $service = $this->getPayPalService();
        $request = $this->getPayPalRequest();

        return $service->doReAuthorization($request);
    }

    /**
     * Returns PayPal request; initiates if not set.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getPayPalRequest()
    {
        if (is_null($this->payPalRequest)) {
            $requestBuilder = $this->getPayPalRequestBuilder();

            $data = $this->getData();

            $requestBuilder->setAuthorizationId($data->getAuthorizationId());
            $requestBuilder->setAmount($data->getAmount(), $data->getCurrency());

            $this->payPalRequest = $requestBuilder->getRequest();
        }

        return $this->payPalRequest;
    }

    /**
     * Sets PayPal request.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $payPalRequest
     */
    public function setPayPalRequest($payPalRequest)
    {
        $this->payPalRequest = $payPalRequest;
    }
}
