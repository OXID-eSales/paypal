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
 * PayPal Order payment action manager class
 */
class OrderPaymentActionManager
{
    /**
     * Array of available actions for payment action.
     *
     * @var array
     */
    protected $availableActions = array(
        "capture" => array(
            "Completed" => array(
                'refund'
            )
        )
    );

    /**
     * Order object.
     *
     * @var \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected $payment = null;

    /**
     * Sets order.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Returns order.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Returns available actions for given payment action
     *
     * @param string $paymentAction
     * @param string $paymentStatus
     *
     * @return array
     */
    protected function getAvailableActions($paymentAction, $paymentStatus)
    {
        $actions = $this->availableActions[$paymentAction][$paymentStatus];

        return $actions ? $actions : array();
    }


    /**
     * Checks whether action is available for given order
     *
     * @param string                                      $action
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $payment
     *
     * @return bool
     */
    public function isActionAvailable($action, $payment = null)
    {
        if ($payment) {
            $this->setPayment($payment);
        }

        $payment = $this->getPayment();

        $isAvailable = in_array($action, $this->getAvailableActions($payment->getAction(), $payment->getStatus()));

        if ($isAvailable) {
            $isAvailable = false;

            switch ($action) {
                case 'refund':
                    $isAvailable = ($payment->getAmount() > $payment->getRefundedAmount());
                    break;
            }
        }

        return $isAvailable;
    }
}
