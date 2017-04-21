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
    protected $_oPayPalRequest = null;

    /**
     * Session object
     *
     * @var \OxidEsales\Eshop\Core\Session
     */
    protected $_oSession = null;

    /**
     * Sets PayPal request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     */
    public function setPayPalRequest($oRequest)
    {
        $this->_oPayPalRequest = $oRequest;
    }

    /**
     * Returns PayPal request object.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getPayPalRequest()
    {
        if ($this->_oPayPalRequest === null) {
            $this->_oPayPalRequest = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->_oPayPalRequest;
    }

    /**
     * Sets Session.
     *
     * @param \OxidEsales\Eshop\Core\Session $oSession
     */
    public function setSession($oSession)
    {
        $this->_oSession = $oSession;
    }

    /**
     * Returns Session.
     *
     * @return \OxidEsales\Eshop\Core\Session
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function getSession()
    {
        if (!$this->_oSession) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException $oException
             */
            $oException = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException::class);
            throw $oException;
        }

        return $this->_oSession;
    }

    /**
     * Builds Request.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function buildRequest()
    {
        $oRequest = $this->getPayPalRequest();
        $oRequest->setParameter('TOKEN', $this->getSession()->getVariable('oepaypal-token'));

        return $oRequest;
    }
}
