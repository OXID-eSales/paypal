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

namespace OxidEsales\PayPalModule\Model\PayPalRequest;

/**
 * PayPal request builder class for get express checkout details
 */
class GetExpressCheckoutDetailsRequestBuilder
{
    /**
     * PayPal Request
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    protected $payPalRequest = null;

    /** @var ?string */
    protected $token = null;

    /**
     * Sets PayPal request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     */
    public function setPayPalRequest($request)
    {
        $this->payPalRequest = $request;
    }

    /**
     * Returns PayPal request object.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getPayPalRequest()
    {
        if ($this->payPalRequest === null) {
            $this->payPalRequest = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->payPalRequest;
    }

    /**
     * Sets Session.
     *
     * @param \OxidEsales\Eshop\Core\Session $session
     * @deprecated Use self::setToken to set token or omit this method if it should be taken from session
     */
    public function setSession($session)
    {
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        if (!$this->token) {
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            $this->token = $session->getVariable('oepaypal-token');
        }

        return $this->token;
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token)
    {
        $this->token = $token;
    }

    /**
     * Builds Request.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function buildRequest()
    {
        $request = $this->getPayPalRequest();
        $request->setParameter('TOKEN', $this->getToken());

        return $request;
    }
}
