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
 * PayPal request builder class
 */
class PayPalRequestBuilder
{
    /**
     * Request object
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    protected $request = null;

    /**
     * Sets Authorization id
     *
     * @param string $authorizationId
     */
    public function setAuthorizationId($authorizationId)
    {
        $this->getRequest()->setParameter('AUTHORIZATIONID', $authorizationId);
    }

    /**
     * Sets Transaction id
     *
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->getRequest()->setParameter('TRANSACTIONID', $transactionId);
    }

    /**
     * Set amount
     *
     * @param double $amount
     * @param string $currencyCode
     */
    public function setAmount($amount, $currencyCode = null)
    {
        $this->getRequest()->setParameter('AMT', $amount);
        if (!$currencyCode) {
            $currencyCode = \OxidEsales\Eshop\Core\Registry::getConfig()->getActShopCurrencyObject()->name;
        }
        $this->getRequest()->setParameter('CURRENCYCODE', $currencyCode);
    }

    /**
     * Set Capture type
     *
     * @param string $type
     */
    public function setCompleteType($type)
    {
        $this->getRequest()->setParameter('COMPLETETYPE', $type);
    }

    /**
     * Set Refund type
     *
     * @param string $type
     */
    public function setRefundType($type)
    {
        $this->getRequest()->setParameter('REFUNDTYPE', $type);
    }

    /**
     * Set Refund type
     *
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->getRequest()->setParameter('NOTE', $comment);
    }


    /**
     * Return request object.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getRequest()
    {
        if ($this->request === null) {
            $this->request = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->request;
    }

    /**
     * Sets Request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }
}
