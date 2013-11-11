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
 * @copyright (C) OXID eSales AG 2003-2013
 */

/**
 * Class for calculation PayPal order statuses after IPN and order creations.
 * Also calculates statuses for suggestion on void, refund, capture operation on PayPal order.
 */
class oePayPalOrderPaymentStatusCalculator
{
    /**
     * PayPal Order.
     *
     * @var oePayPalPayPalOrder
     */
    protected $_oOrder = null;

    /**
     * @var oePayPalOrderPayment
     */
    protected $_oOrderPayment = null;

    /**
     * Set PayPal Order.
     *
     * @param oePayPalPayPalOrder $oOrder PayPal order
     */
    public function setOrder( $oOrder )
    {
        $this->_oOrder = $oOrder;
    }

    /**
     * Return PayPal Order.
     *
     * @return oePayPalPayPalOrder
     */
    public function getOrder()
    {
        return $this->_oOrder;
    }

    /**
     * Sets PayPal OrderPayment.
     *
     * @param oePayPalOrderPayment $oOrderPayment
     */
    public function setOrderPayment( $oOrderPayment )
    {
        $this->_oOrderPayment = $oOrderPayment;
    }

    /**
     * Return PayPal OrderPayment.
     *
     * @return oePayPalOrderPayment
     */
    public function getOrderPayment()
    {
        return $this->_oOrderPayment;
    }

    /**
     * Return status for suggestion on void operation.
     *
     * @return bool
     */
    protected function _getSuggestStatusOnVoid()
    {
        $sStatus = 'canceled';

        if ( $this->getOrder()->getCapturedAmount() > 0 ) {
            $sStatus = 'completed';
        }

        return $sStatus;
    }

    /**
     * Return true if order statuses can be changed automatically.
     *
     * @return bool
     */
    protected function _isOrderPaymentStatusFinal()
    {
        $sOrderPaymentStatus = $this->getOrder()->getPaymentStatus();
        return $sOrderPaymentStatus == 'failed' || $sOrderPaymentStatus == 'canceled';
    }

    /**
     * Returns order payment status which should be set after order creation or IPN.
     *
     * @return string|null
     */
    public function getStatus()
    {
        if ( is_null( $this->getOrder() ) ) {
            return;
        }

        $sStatus = $this->_getOrderPaymentStatusFinal();

        if ( is_null( $sStatus ) ) {
            $sStatus = $this->_getOrderPaymentStatusPaymentValid();
        }
        if ( is_null( $sStatus ) ) {
            $sStatus = $this->_getOrderPaymentStatusPayments();
        }

        return $sStatus;
    }

    /**
     * Returns order suggestion for payment status on given action and on given payment.
     *
     * @param string $sAction - action with order payment: void, refund, capture, refund_partial, capture_partial
     *
     * @return string|null
     */
    public function getSuggestStatus( $sAction )
    {
        if ( is_null( $this->getOrder() ) ) {
            return;
        }

        $sStatus = $this->_getOrderPaymentStatusPaymentValid();
        if ( is_null( $sStatus ) ) {
            $sStatus = $this->_getStatusByAction( $sAction );
        }

        return $sStatus;
    }

    /**
     * Returns order payment status if order has final status.
     *
     * @return string|null
     */
    protected function _getOrderPaymentStatusFinal()
    {
        $sStatus = null;
        if ( $this->_isOrderPaymentStatusFinal() ) {
            $sStatus = $this->getOrder()->getPaymentStatus();
        }
        return $sStatus;
    }

    /**
     * Returns order payment status by checking if set payment is valid.
     *
     * @return string|null
     */
    protected function _getOrderPaymentStatusPaymentValid()
    {
        $sStatus = null;
        $oOrderPayment = $this->getOrderPayment();
        if ( isset( $oOrderPayment ) && !$oOrderPayment->getIsValid() ) {
            $sStatus = 'failed';
        }
        return $sStatus;
    }

    /**
     * Returns order payment status calculated from existing payments.
     *
     * @return string|null
     */
    protected function _getOrderPaymentStatusPayments()
    {
        $sStatus = 'completed';
        $oPaymentList = $this->getOrder()->getPaymentList();

        if ( $oPaymentList->hasPendingPayment() ) {
            $sStatus = 'pending';
        } elseif ( $oPaymentList->hasFailedPayment() ) {
            $sStatus = 'failed';
        }

        return $sStatus;
    }

    /**
     * Returns order suggestion for payment status on given action.
     *
     * @param string $sAction performed action.
     *
     * @return string
     */
    protected function _getStatusByAction( $sAction )
    {
        $sStatus = null;
        switch ( $sAction ) {
            case 'void':
                $sStatus = $this->_getSuggestStatusOnVoid();
                break;
            case 'refund_partial':
            case 'reauthorize':
                $sStatus = $this->getOrder()->getPaymentStatus();
                break;
            case 'refund':
            case 'capture':
            case 'capture_partial':
                $sStatus = 'completed';
                break;
            default:
                $sStatus = 'completed';
        }
        return $sStatus;
    }

}