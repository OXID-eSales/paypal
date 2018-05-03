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
 * Class \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator
 */
class OrderPaymentListCalculator
{
    /** @var \OxidEsales\PayPalModule\Model\OrderPaymentList */
    private $paymentList = array();

    /** @var float Amount of void action. */
    private $voidedAmount = 0.0;

    /** @var float Amount of voided authorization action. */
    private $voidedAuthAmount = 0.0;

    /**@var float Amount of capture action. */
    private $capturedAmount = 0.0;

    /** @var float Amount of refund action. */
    private $refundedAmount = 0.0;

    /** @var array Payment and status match. */
    private $paymentMatch = array(
        'capturedAmount'   => array(
            'action' => array('capture'),
            'status' => array('Completed')
        ),
        'refundedAmount'   => array(
            'action' => array('refund'),
            'status' => array('Refunded')
        ),
        'voidedAuthAmount' => array(
            'action' => array('authorization'),
            'status' => array('Voided')
        ),
        'voidedAmount'     => array(
            'action' => array('void'),
            'status' => array('Voided')
        )
    );

    /**
     * Sets paypal order payment list.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPaymentList $paymentList
     */
    public function setPaymentList($paymentList)
    {
        $this->paymentList = $paymentList;
    }

    /**
     * Getter for paypal order payment list.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentList
     */
    public function getPaymentList()
    {
        return $this->paymentList;
    }

    /**
     * Sum up payment amounts for capture, void, refund.
     * Take into account successful transactions only.
     */
    public function calculate()
    {
        $this->init();

        foreach ($this->getPaymentList() as $payment) {
            $status = $payment->getStatus();
            $action = $payment->getAction();
            $amount = $payment->getAmount();

            $this->aggregateAmounts($action, $status, $amount);
        }
    }

    /**
     * Getter for captured amount
     *
     * @return float
     */
    public function getCapturedAmount()
    {
        return $this->capturedAmount;
    }

    /**
     * Getter for refunded amount
     *
     * @return float
     */
    public function getRefundedAmount()
    {
        return $this->refundedAmount;
    }

    /**
     * Getter for voided amount.
     *
     * @return float
     */
    public function getVoidedAmount()
    {
        $return = 0.0;

        if (0 < $this->voidedAmount) {
            //void action is only logged when executed via shop admin
            $return = $this->voidedAmount;
        } elseif (0 < $this->voidedAuthAmount) {
            //no data from void actions means we might have a voided Authorization
            $return = $this->voidedAuthAmount - $this->capturedAmount;
        }

        return $return;
    }

    /**
     * Initialize results.
     * Needs to be done e.g. before calling calculate.
     */
    private function init()
    {
        foreach (array_keys($this->paymentMatch) as $target) {
            $this->$target = 0.0;
        }
    }

    /**
     * @param string $action PayPal order payment action type (e.g. cpture, refund, void)
     * @param string $status PayPal order payment status.
     * @param double $amount PayPal order payment amount.
     */
    private function aggregateAmounts($action, $status, $amount)
    {
        foreach ($this->paymentMatch as $target => $check) {
            if (in_array($action, $check['action']) && in_array($status, $check['status'])) {
                $this->$target += $amount;
            }
        }
    }
}
