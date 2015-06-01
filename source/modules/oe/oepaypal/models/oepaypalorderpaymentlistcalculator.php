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
 * Class oePayPalOrderPaymentListCalculator
 */
class oePayPalOrderPaymentListCalculator
{
    /** @var oePayPalPaymentList */
    protected $paymentList = null;

    /** @var float Amount of void action. */
    protected $voidedAmount = 0.0;

    /** @var float Amount of voided authorization action. */
    protected $voidedAuthAmount = 0.0;

    /**@var float Amount of capture action. */
    protected $capturedAmount = 0.0;

    /** @var float Amount of refund action. */
    protected $refundedAmount = 0.0;

    /** @var array Payment and status match. */
    protected $paymentMatch = array(
        'capturedAmount' => array(
            'action' => array('capture'),
            'status' => array('Completed')
        ),
        'refundedAmount' => array(
            'action' => array('refund'),
            'status' => array('Refunded')
        ),
        'voidedAuthAmount'   => array(
            'action' => array('authorization'),
            'status' => array('Voided')
        ),
        'voidedAmount'   => array(
            'action' => array('void'),
            'status' => array('Voided')
        )
    );

    /**
     * Sets order.
     *
     * @param oePayPalOrderPaymentList $paymentList
     */
    public function setPaymentList($paymentList)
    {
        $this->paymentList = $paymentList;
    }

    /**
     * Sum up payment amounts for capture, void, refund.
     * Take into account successful transactions only.
     *
     * @return null
     */
    public function calculate()
    {
        if (!is_a($this->paymentList, 'oePayPalOrderPaymentList')) {
            return;
        }
        foreach (array_keys($this->paymentMatch) as $target) {
            $this->$target = 0.0;
        }
        foreach($this->paymentList as $payment) {
            $status = $payment->getStatus();
            $action = $payment->getAction();
            $amount = $payment->getAmount();

            foreach ($this->paymentMatch as $target => $check) {
                if ( in_array( $action, $check['action']) && in_array($status, $check['status']) ) {
                    $this->$target += $amount;
                }
            }
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
            $return =  $this->voidedAmount;
        } elseif (0 < $this->voidedAuthAmount) {
            //no data from void actions means we might have a voided Authorization
            $return = $this->voidedAuthAmount - $this->capturedAmount;
        }
        return $return;
    }

}
