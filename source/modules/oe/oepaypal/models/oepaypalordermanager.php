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

class oePayPalOrderManager
{
    /**
     * @var oePayPalOrderPayment
     */
    protected $_oOrderPayment = null;

    /**
     * @var oePayPalPayPalOrder
     */
    protected $_oOrder = null;

    /**
     * @var oePayPalOrderPaymentStatusCalculator
     */
    protected $_oOrderPaymentStatusCalculator = null;

    /**
     * @param oePayPalOrderPayment $oOrderPayment
     */
    public function setOrderPayment( $oOrderPayment )
    {
        $this->_oOrderPayment = $oOrderPayment;
    }

    /**
     * @return oePayPalOrderPayment
     */
    public function getOrderPayment()
    {
        return $this->_oOrderPayment;
    }

    /**
     * @param oePayPalPayPalOrder $oOrder
     */
    public function setOrder( $oOrder )
    {
        $this->_oOrder = $oOrder;
    }

    /**
     * Create object oePayPalPayPalOrder.
     * If Order is not set, create order from Order Payment.
     * @return object
     */
    public function getOrder()
    {
        if ( $this->_oOrder === null ) {
            $oOrderPayment = $this->getOrderPayment();
            $oOrder = $this->_getOrderFromPayment( $oOrderPayment );
            $this->setOrder( $oOrder );
        }
        return $this->_oOrder;
    }

    /**
     * @param oePayPalOrderPaymentStatusCalculator $oOrderPaymentStatusCalculator
     */
    public function setOrderPaymentStatusCalculator( $oOrderPaymentStatusCalculator )
    {
        $this->_oOrderPaymentStatusCalculator = $oOrderPaymentStatusCalculator;
    }

    /**
     * @return oePayPalOrderPaymentStatusCalculator
     */
    public function getOrderPaymentStatusCalculator()
    {
        if ( is_null( $this->_oOrderPaymentStatusCalculator ) ) {
            $oOrderPaymentStatusCalculator = oxNew( 'oePayPalOrderPaymentStatusCalculator' );
            $this->setOrderPaymentStatusCalculator( $oOrderPaymentStatusCalculator );
        }
        return $this->_oOrderPaymentStatusCalculator;
    }

    /**
     * Update order manager to status get from order status calculator.
     *
     * @return bool
     */
    public function updateOrderStatus()
    {
        $blOrderUpdated = false;
        $oOrder = $this->getOrder();
        if ( !is_null( $oOrder ) ) {
            $oOrderPayment = $this->getOrderPayment();
            $sNewOrderStatus = $this->_calculateOrderStatus( $oOrderPayment, $oOrder );
            $this->_updateOrderStatus( $oOrder, $sNewOrderStatus );
            $blOrderUpdated = true;
        }
        return $blOrderUpdated;
    }

    /**
     * Wrapper for order payment calculator.
     *
     * @param oePayPalOrderPayment $oOrderPayment order payment to set to calculator.
     * @param oePayPalPayPalOrder $oOrder order to be set to validator.
     *
     * @return null|string
     */
    protected function _calculateOrderStatus( $oOrderPayment, $oOrder )
    {
        $oOrderPaymentStatusCalculator = $this->getOrderPaymentStatusCalculator();
        $oOrderPaymentStatusCalculator->setOrderPayment( $oOrderPayment );
        $oOrderPaymentStatusCalculator->setOrder( $oOrder );

        $sNewOrderStatus = $oOrderPaymentStatusCalculator->getStatus();
        return $sNewOrderStatus;
    }

    /**
     * Update order to given status.
     *
     * @param oePayPalPayPalOrder $oOrder order to be updated.
     * @param string $sNewOrderStatus new order status.
     */
    protected function _updateOrderStatus( $oOrder, $sNewOrderStatus )
    {
        $oOrder->setPaymentStatus( $sNewOrderStatus );
        $oOrder->save();
    }

    /**
     * Load order by order id from order payment.
     *
     * @param oePayPalOrderPayment $oOrderPayment order payment to get order id.
     *
     * @return oePayPalPayPalOrder|null
     */
    protected function _getOrderFromPayment( $oOrderPayment )
    {
        $sOrderId = null;
        $oOrder = null;
        if ( !is_null( $oOrderPayment ) ) {
            $sOrderId = $oOrderPayment->getOrderId();
        }
        if ( !is_null( $sOrderId ) ) {
            $oOrder = oxNew( 'oePayPalPayPalOrder' );
            $oOrder->setOrderId( $sOrderId );
            $oOrder->load();
        }
        return $oOrder;
    }
}