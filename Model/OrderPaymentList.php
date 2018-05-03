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
 * PayPal order payment list class
 */
class OrderPaymentList extends \OxidEsales\PayPalModule\Core\PayPalList
{
    /**
     * Data base gateway
     *
     * @var oePayPalPayPalDbGateway
     */
    protected $dbGateway = null;

    /**
     * @var string|null
     */
    protected $orderId = null;

    /**
     * Sets order id.
     *
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Returns order id.
     *
     * @return null|string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Returns oePayPalPayPalDbGateway or creates and sets it if it was not set.
     *
     * @return oePayPalPayPalDbGateway
     */
    protected function getDbGateway()
    {
        if (is_null($this->dbGateway)) {
            $this->setDbGateway(oxNew(\OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentDbGateway::class));
        }

        return $this->dbGateway;
    }

    /**
     * Set model database gateway.
     *
     * @param object $dbGateway
     */
    protected function setDbGateway($dbGateway)
    {
        $this->dbGateway = $dbGateway;
    }

    /**
     * Selects and loads order payment history.
     *
     * @param string $orderId Order id.
     */
    public function load($orderId)
    {
        $this->setOrderId($orderId);

        $payments = array();
        $paymentsData = $this->getDbGateway()->getList($this->getOrderId());
        if (is_array($paymentsData) && count($paymentsData)) {
            $payments = array();
            foreach ($paymentsData as $data) {
                $payment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
                $payment->setData($data);
                $payments[] = $payment;
            }
        }

        $this->setArray($payments);
    }

    /**
     * Check if list has payment with defined status.
     *
     * @param string $status Payment status.
     *
     * @return bool
     */
    protected function hasPaymentWithStatus($status)
    {
        $hasStatus = false;
        $payments = $this->getArray();

        foreach ($payments as $payment) {
            if ($status == $payment->getStatus()) {
                $hasStatus = true;
                break;
            }
        }

        return $hasStatus;
    }

    /**
     * Check if list has pending payment.
     *
     * @return bool
     */
    public function hasPendingPayment()
    {
        return $this->hasPaymentWithStatus('Pending');
    }

    /**
     * Check if list has failed payment.
     *
     * @return bool
     */
    public function hasFailedPayment()
    {
        return $this->hasPaymentWithStatus('Failed');
    }

    /**
     * Returns not yet captured (remaining) order sum.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $payment order payment
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    public function addPayment(\OxidEsales\PayPalModule\Model\OrderPayment $payment)
    {
        //order payment info
        if ($this->getOrderId()) {
            $payment->setOrderId($this->getOrderId());
            $payment->save();
        }

        $this->load($this->getOrderId());

        return $payment;
    }
}
