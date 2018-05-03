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

namespace OxidEsales\PayPalModule\Model\Action;

/**
 * PayPal order action void class
 */
class OrderVoidAction extends \OxidEsales\PayPalModule\Model\Action\OrderAction
{
    /**
     * Processes PayPal response
     */
    public function process()
    {
        $handler = $this->getHandler();
        $response = $handler->getPayPalResponse();
        $data = $handler->getData();

        $order = $this->getOrder();
        $amount = $order->getRemainingOrderSum();
        $order->setVoidedAmount($amount);
        $order->setPaymentStatus($data->getOrderStatus());
        $order->save();

        $payment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $payment->setDate($this->getDate());
        $payment->setTransactionId($response->getAuthorizationId());
        $payment->setCorrelationId($response->getCorrelationId());
        $payment->setAction('void');
        $payment->setStatus('Voided');
        $payment->setAmount($amount);

        $payment = $order->getPaymentList()->addPayment($payment);

        if ($data->getComment()) {
            $comment = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentComment::class);
            $comment->setComment($data->getComment());
            $payment->addComment($comment);
        }
    }
}
