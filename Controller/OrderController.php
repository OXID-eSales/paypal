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

namespace OxidEsales\PayPalModule\Controller;

/**
 * Order class wrapper for PayPal module
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 */
class OrderController extends OrderController_parent
{
    /**
     * Checks if payment action is processed by PayPal
     *
     * @return bool
     */
    public function isPayPal()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        return ($session->getVariable("paymentid") == "oxidpaypal");
    }

    /**
     * Returns PayPal user
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    public function getUser()
    {
        $user = parent::getUser();
        $session = \OxidEsales\Eshop\Core\Registry::getSession();

        $userId = $session->getVariable("oepaypal-userId");
        if ($this->isPayPal() && $userId) {
            $payPalUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            if ($payPalUser->load($userId)) {
                $user = $payPalUser;
            }
        }

        return $user;
    }

    /**
     * Returns PayPal payment object if PayPal is on, or returns parent::getPayment()
     *
     * @return \OxidEsales\Eshop\Application\Model\Payment
     */
    public function getPayment()
    {
        if (!$this->isPayPal()) {
            // removing PayPal payment type from session
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            $session->deleteVariable('oepaypal');
            $session->deleteVariable('oepaypal-basketAmount');

            return parent::getPayment();
        }

        if ($this->payment === null) {
            // payment is set ?
            $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            if ($payment->load('oxidpaypal')) {
                $this->payment = $payment;
            }
        }

        return $this->payment;
    }

    /**
     * Returns current order object
     *
     * @return \OxidEsales\Eshop\Application\Model\Order
     */
    protected function getOrder()
    {
        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $session = \OxidEsales\Eshop\Core\Registry::getSession();

        $order->load($session->getVariable('sess_challenge'));

        return $order;
    }

    /**
     * Checks if order payment is PayPal and redirects to payment processing part.
     *
     * @param int $success order state
     *
     * @return string
     */
    protected function getNextStep($success)
    {
        $nextStep = parent::_getNextStep($success);

        // Detecting PayPal & loading order & execute payment only if go wrong
        if ($this->isPayPal() && ($success == \OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_PAYMENTERROR)) {
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            $payPalType = (int) $session->getVariable("oepaypal");
            $nextStep = ($payPalType == 2) ? "basket" : "order";
        }

        return $nextStep;
    }
}
