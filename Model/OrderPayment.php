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

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal order payment class.
 */
class OrderPayment extends \OxidEsales\PayPalModule\Core\Model
{
    /**
     * Set PayPal order comment Id.
     *
     * @param string $paymentId
     */
    public function setId($paymentId)
    {
        $this->setPaymentId($paymentId);
    }

    /**
     * Set PayPal comment Id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getPaymentId();
    }

    /**
     * If Payment is valid.
     *
     * @var bool
     */
    protected $isValid = true;

    /**
     * Payment comments
     *
     * @var array
     */
    protected $commentList = null;


    /**
     * Set PayPal order comment Id.
     *
     * @param string $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->setValue('oepaypal_paymentid', $paymentId);
    }

    /**
     * Set PayPal comment Id.
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->getValue('oepaypal_paymentid');
    }

    /**
     * Sets PayPal payment actions.
     *
     * @param string $value
     */
    public function setAction($value)
    {
        $this->setValue('oepaypal_action', $value);
    }

    /**
     * Returns PayPal payment action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getValue('oepaypal_action');
    }

    /**
     * Sets PayPal payment OrderId.
     *
     * @param string $value
     */
    public function setOrderId($value)
    {
        $this->setValue('oepaypal_orderid', $value);
    }

    /**
     * Returns PayPal payment OrderId.
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getValue('oepaypal_orderid');
    }

    /**
     * Sets PayPal payment amount
     *
     * @param float $value
     */
    public function setAmount($value)
    {
        $this->setValue('oepaypal_amount', $value);
    }

    /**
     * Returns PayPal payment amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getValue('oepaypal_amount');
    }

    /**
     * Set PayPal refunded amount.
     *
     * @param double $amount
     */
    public function setRefundedAmount($amount)
    {
        $this->setValue('oepaypal_refundedamount', $amount);
    }

    /**
     * Adds given amount to PayPal refunded amount.
     *
     * @param double $amount
     */
    public function addRefundedAmount($amount)
    {
        $this->setRefundedAmount($amount + $this->getRefundedAmount());
    }

    /**
     * Get PayPal refunded amount
     *
     * @return double
     */
    public function getRefundedAmount()
    {
        return (double) $this->getValue('oepaypal_refundedamount');
    }

    /**
     * Returns not yet captured (remaining) order sum
     *
     * @return string
     */
    public function getRemainingRefundAmount()
    {
        $amount = $this->getAmount() - $this->getRefundedAmount();

        return sprintf("%.2f", round($amount, 2));
    }

    /**
     * Returns PayPal payment status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getValue('oepaypal_status');
    }

    /**
     * Returns PayPal payment status.
     *
     * @param string $value status
     */
    public function setStatus($value)
    {
        $this->setValue('oepaypal_status', $value);
    }

    /**
     * Sets PayPal payment date.
     *
     * @param string $value
     */
    public function setDate($value)
    {
        $this->setValue('oepaypal_date', $value);
    }

    /**
     * Returns PayPal payment date.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->getValue('oepaypal_date');
    }

    /**
     * Returns PayPal payment currency.
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->setValue('oepaypal_currency', $currency);
    }

    /**
     * Sets PayPal payment currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getValue('oepaypal_currency');
    }

    /**
     * Set PayPal payment transaction id.
     *
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->setValue('oepaypal_transactionid', $transactionId);
    }

    /**
     *  Returns PayPal payment transaction id
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getValue('oepaypal_transactionid');
    }

    /**
     *  Set PayPal payment correlation id
     *
     * @param string $correlationId
     */
    public function setCorrelationId($correlationId)
    {
        $this->setValue('oepaypal_correlationid', $correlationId);
    }

    /**
     *  Returns PayPal payment correlation id
     *
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->getValue('oepaypal_correlationid');
    }

    /**
     *  Load payment data by given transaction id
     *
     * @param string $transactionId transaction id
     *
     * @return bool
     */
    public function loadByTransactionId($transactionId)
    {
        $result = false;
        $data = $this->getDbGateway()->loadByTransactionId($transactionId);
        if ($data) {
            $this->setData($data);
            $result = true;
        }

        return $result;
    }

    /**
     * Sets if payment is valid.
     *
     * @param boolean $isValid payment is valid.
     */
    public function setIsValid($isValid)
    {
        $this->isValid = (bool) $isValid;
    }

    /**
     * Gets if payment pass validation.
     *
     * @return boolean
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * Get comments
     *
     * @return array
     */
    public function getCommentList()
    {
        if (is_null($this->commentList)) {
            $comments = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentCommentList::class);
            $comments->load($this->getPaymentId());
            $this->setCommentList($comments);
        }

        return $this->commentList;
    }

    /**
     * Set comments.
     *
     * @param array $comments
     */
    public function setCommentList($comments)
    {
        $this->commentList = $comments;
    }

    /**
     * Add comment.
     *
     * @param \oePaypalOrderPaymentComment $comment comment
     */
    public function addComment($comment)
    {
        $this->getCommentList()->addComment($comment);
    }

    /**
     * Return database gateway.
     *
     * @return \OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentDbGateway|\OxidEsales\PayPalModule\Core\ModelDbGateway
     */
    protected function getDbGateway()
    {
        if (is_null($this->dbGateway)) {
            $this->setDbGateway(oxNew(\OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentDbGateway::class));
        }

        return $this->dbGateway;
    }
}
