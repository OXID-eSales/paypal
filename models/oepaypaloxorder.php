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
 * PayPal oxOrder class
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class oePayPalOxOrder extends oePayPalOxOrder_parent
{
    /** Transaction was finished successfully. */
    const OEPAYPAL_TRANSACTION_STATUS_OK = 'OK';

    /** Transaction is not finished or failed. */
    const OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED = 'NOT_FINISHED';

    /**
     * PayPal order information
     *
     * @var oePayPalPayPalOrder
     */
    protected $_oPayPalOrder = null;

    /**
     * Loads order associated with current PayPal order
     *
     * @return bool
     */
    public function loadPayPalOrder()
    {
        $sOrderId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable("sess_challenge");

        // if order is not created yet - generating it
        if ($sOrderId === null) {
            $sOrderId = \OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUID();
            $this->setId($sOrderId);
            $this->save();
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable("sess_challenge", $sOrderId);
        }

        return $this->load($sOrderId);
    }

    /**
     * Updates order number.
     *
     * @return bool
     */
    public function oePayPalUpdateOrderNumber()
    {
        if ($this->oxorder__oxordernr->value) {
            $blUpdated = (bool) oxNew(\OxidEsales\Eshop\Core\Counter::class)->update($this->_getCounterIdent(), $this->oxorder__oxordernr->value);
        } else {
            $blUpdated = $this->_setNumber();
        }

        return $blUpdated;
    }

    /**
     * Delete order created by current PayPal ordering process
     *
     * @return bool
     */
    public function deletePayPalOrder()
    {
        if ($this->loadPayPalOrder()) {
            $this->getPayPalOrder()->delete();

            return $this->delete();
        }
    }

    /**
     * Delete order together with PayPal order data.
     *
     * @param string $sOxId
     */
    public function delete($sOxId = null)
    {
        $this->getPayPalOrder($sOxId)->delete();
        return parent::delete($sOxId);
    }

    /**
     * Updates order transaction status, ID and date.
     *
     * @param string $sTransactionId Order transaction ID
     */
    protected function _setPaymentInfoPayPalOrder($sTransactionId)
    {
        // set transaction ID and payment date to order
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

        $sQ = 'update oxorder set oxtransid=' . $oDb->quote($sTransactionId) . ' where oxid=' . $oDb->quote($this->getId());
        $oDb->execute($sQ);

        //updating order object
        $this->oxorder__oxtransid = new \OxidEsales\Eshop\Core\Field($sTransactionId);
    }

    /**
     * Finalizes PayPal order.
     *
     * @param oePayPalResponseDoExpressCheckoutPayment   $oResult          PayPal results array.
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket          Basket object.
     * @param string                                     $sTransactionMode Transaction mode Sale|Authorization.
     */
    public function finalizePayPalOrder($oResult, $oBasket, $sTransactionMode)
    {
        $utilsDate = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsDate::class);
        $sDate = date('Y-m-d H:i:s', $utilsDate->getTime());

        // set order status, transaction ID and payment date to order
        $this->_setPaymentInfoPayPalOrder($oResult->getTransactionId());

        $sCurrency = $oResult->getCurrencyCode();
        if (!$sCurrency) {
            $sCurrency = $this->getOrderCurrency()->name;
        }

        //PayPal order info
        $oPayPalOrder = $this->getPayPalOrder();
        $oPayPalOrder->setOrderId($this->getId());
        $oPayPalOrder->setPaymentStatus('pending');
        $oPayPalOrder->setTransactionMode($sTransactionMode);
        $oPayPalOrder->setCurrency($sCurrency);
        $oPayPalOrder->setTotalOrderSum($oBasket->getPrice()->getBruttoPrice());
        if ($sTransactionMode == 'Sale') {
            $oPayPalOrder->setCapturedAmount($oBasket->getPrice()->getBruttoPrice());
        }
        $oPayPalOrder->save();

        $oOrderPayment = oxNew('oePayPalOrderPayment');
        $oOrderPayment->setTransactionId($oResult->getTransactionId());
        $oOrderPayment->setCorrelationId($oResult->getCorrelationId());
        $oOrderPayment->setDate($sDate);
        $oOrderPayment->setAction(($sTransactionMode == 'Sale') ? 'capture' : 'authorization');
        $oOrderPayment->setStatus($oResult->getPaymentStatus());
        $oOrderPayment->setAmount($oResult->getAmount());
        $oOrderPayment->setCurrency($oResult->getCurrencyCode());

        //Adding payment information
        $oPaymentList = $this->getPayPalOrder()->getPaymentList();
        $oPaymentList->addPayment($oOrderPayment);

        //setting order payment status after
        $oPaymentStatusCalculator = oxNew('oePayPalOrderPaymentStatusCalculator');
        $oPaymentStatusCalculator->setOrder($this->getPayPalOrder());
        $this->getPayPalOrder()->setPaymentStatus($oPaymentStatusCalculator->getStatus());
        $this->getPayPalOrder()->save();

        //clear PayPal identification
        $this->getSession()->deleteVariable('oepaypal');
        $this->getSession()->deleteVariable("oepaypal-payerId");
        $this->getSession()->deleteVariable("oepaypal-userId");
        $this->getSession()->deleteVariable('oepaypal-basketAmount');
    }

    /**
     * Paypal specific status checking.
     *
     * If status comes as OK, lets check real paypal payment state,
     * and if really ok, so lets set it, otherwise dont change status.
     *
     * @param string $sStatus order transaction status
     */
    protected function _setOrderStatus($sStatus)
    {
        $paymentTypeObject = $this->getPaymentType();
        $paymentType = $paymentTypeObject ? $paymentTypeObject->getFieldData('oxpaymentsid') : null;
        if ($paymentType != 'oxidpaypal' || $sStatus != self::OEPAYPAL_TRANSACTION_STATUS_OK) {
            parent::_setOrderStatus($sStatus);
        }
    }

    /**
     * Update order oxpaid to current time.
     */
    public function markOrderPaid()
    {
        parent::_setOrderStatus(self::OEPAYPAL_TRANSACTION_STATUS_OK);

        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $utilsDate = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsDate::class);
        $sDate = date('Y-m-d H:i:s', $utilsDate->getTime());

        $sQ = 'update oxorder set oxpaid=? where oxid=?';
        $oDb->execute($sQ, array($sDate, $this->getId()));

        //updating order object
        $this->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field($sDate);
    }

    /**
     * Checks if delivery set used for current order is available and active.
     * Throws exception if not available
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket basket object
     *
     * @return int
     */
    public function validateDelivery($oBasket)
    {
        if ($oBasket->getPaymentId() == 'oxidpaypal') {
            $sShippingId = $oBasket->getShippingId();
            $dBasketPrice = $oBasket->getPrice()->getBruttoPrice();
            $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            if (!$oUser->loadUserPayPalUser()) {
                $oUser = $this->getUser();
            }

            $iValidState = null;
            if (!$this->_isPayPalPaymentValid($oUser, $dBasketPrice, $sShippingId)) {
                $iValidState = self::ORDER_STATE_INVALIDDELIVERY;
            }
        } else {
            $iValidState = parent::validateDelivery($oBasket);
        }

        return $iValidState;
    }

    /**
     * Returns PayPal order object.
     *
     * @param string $sOxId
     *
     * @return oePayPalPayPalOrder|null
     */
    public function getPayPalOrder($sOxId = null)
    {
        if (is_null($this->_oPayPalOrder)) {
            $sOrderId = is_null($sOxId) ? $this->getId() : $sOxId;
            $oOrder = oxNew('oePayPalPayPalOrder');
            $oOrder->load($sOrderId);
            $this->_oPayPalOrder = $oOrder;
        }

        return $this->_oPayPalOrder;
    }

    /**
     * Get payment status
     *
     * @return string
     */
    public function getPayPalPaymentStatus()
    {
        return $this->getPayPalOrder()->getPaymentStatus();
    }

    /**
     * Returns PayPal Authorization id.
     *
     * @return string
     */
    public function getAuthorizationId()
    {
        return $this->oxorder__oxtransid->value;
    }

    /**
     * Checks whether PayPal payment is available.
     *
     * @param object $oUser
     * @param double $dBasketPrice
     * @param string $sShippingId
     *
     * @return bool
     */
    protected function _isPayPalPaymentValid($oUser, $dBasketPrice, $sShippingId)
    {
        $blValid = true;

        $oPayPalPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oPayPalPayment->load('oxidpaypal');
        if (!$oPayPalPayment->isValidPayment(null, null, $oUser, $dBasketPrice, $sShippingId)) {
            $blValid = $this->_isEmptyPaymentValid($oUser, $dBasketPrice, $sShippingId);
        }

        return $blValid;
    }

    /**
     * Checks whether Empty payment is available.
     *
     * @param object $oUser
     * @param double $dBasketPrice
     * @param string $sShippingId
     *
     * @return bool
     */
    protected function _isEmptyPaymentValid($oUser, $dBasketPrice, $sShippingId)
    {
        $blValid = true;

        $oEmptyPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oEmptyPayment->load('oxempty');
        if (!$oEmptyPayment->isValidPayment(null, null, $oUser, $dBasketPrice, $sShippingId)) {
            $blValid = false;
        }

        return $blValid;
    }
}
