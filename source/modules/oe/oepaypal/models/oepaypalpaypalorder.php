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
 * PayPal PayPalOrder class
 */
class oePayPalPayPalOrder extends oePayPalModel
{
    /**
     * List of order payments
     *
     * @var oePayPalOrderPaymentList
     */
    protected $_oPaymentList = null;

    public function setId( $sOrderId )
    {
        $this->setOrderId( $sOrderId );
    }

    public function getId()
    {
        return $this->getOrderId();
    }

    /**
     * Set PayPal order Id
     *
     * @param string $sOrderId
     */
    public function setOrderId( $sOrderId )
    {
        $this->_setValue( 'oepaypal_orderid', $sOrderId );
    }

    /**
     * Set PayPal order Id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->_getValue( 'oepaypal_orderid' );
    }

    /**
     * Set PayPal captured amount
     *
     * @param double $dAmount
     */
    public function setCapturedAmount( $dAmount )
    {
        $this->_setValue( 'oepaypal_capturedamount', $dAmount );
    }

    /**
     * Adds given amount to PayPal captured amount
     *
     * @param double $dAmount
     */
    public function addCapturedAmount( $dAmount )
    {
        $this->setCapturedAmount( $dAmount + $this->getCapturedAmount() );
    }

    /**
     * Get PayPal captured amount
     *
     * @return string
     */
    public function getCapturedAmount()
    {
        return $this->_getValue( 'oepaypal_capturedamount' );
    }

    /**
     * Set PayPal refunded amount
     *
     * @param double $dAmount
     */
    public function setRefundedAmount( $dAmount )
    {
        $this->_setValue( 'oepaypal_refundedamount', $dAmount );
    }

    /**
     * Adds given amount to PayPal refunded amount
     *
     * @param double $dAmount
     */
    public function addRefundedAmount( $dAmount )
    {
        $this->setRefundedAmount( $dAmount + $this->getRefundedAmount() );
    }

    /**
     * Get PayPal refunded amount
     *
     * @return string
     */
    public function getRefundedAmount()
    {
        return $this->_getValue( 'oepaypal_refundedamount' );
    }

    /**
     * Returns not yet captured (remaining) order sum
     *
     * @return string
     */
    public function getRemainingRefundAmount()
    {
        return round( $this->getCapturedAmount() - $this->getRefundedAmount(), 2 );
    }

    /**
     * Set PayPal refunded amount
     *
     * @param double $dAmount
     */
    public function setVoidedAmount( $dAmount )
    {
        $this->_setValue( 'oepaypal_voidedamount', $dAmount );
    }

    /**
     * Get PayPal refunded amount
     *
     * @return string
     */
    public function getVoidedAmount()
    {
        return $this->_getValue( 'oepaypal_voidedamount' );
    }

    /**
     * Set transaction mode
     *
     * @param string $sMode
     */
    public function setTransactionMode( $sMode )
    {
        $this->_setValue( 'oepaypal_transactionmode', $sMode );
    }

    /**
     * Get transaction mode
     *
     * @return string
     */
    public function getTransactionMode()
    {
        return $this->_getValue( 'oepaypal_transactionmode' );
    }

    /**
     * Set payment status
     *
     * @param string $sStatus
     */
    public function setPaymentStatus( $sStatus )
    {
        $this->_setValue( 'oepaypal_paymentstatus', $sStatus );
    }

    /**
     * Get payment status
     *
     * @return string
     */
    public function getPaymentStatus()
    {
        $sState = $this->_getValue( 'oepaypal_paymentstatus' );
        if ( empty( $sState ) ) {
            $sState = "completed";
        }
        return $sState;
    }

    /**
     * Sets total order sum
     *
     * @param $dAmount
     */
    public function setTotalOrderSum( $dAmount )
    {
        $this->_setValue( 'oepaypal_totalordersum', $dAmount );
    }

    /**
     * Returns total order sum
     *
     * @return string
     */
    public function getTotalOrderSum()
    {
        return $this->_getValue( 'oepaypal_totalordersum' );
    }

    /**
     * Returns not yet captured (remaining) order sum
     *
     * @return string
     */
    public function getRemainingOrderSum()
    {
        return $this->getTotalOrderSum() - $this->getCapturedAmount();
    }

    /**
     * Set order currency
     *
     * @param string $sStatus
     */
    public function setCurrency( $sStatus )
    {
        $this->_setValue( 'oepaypal_currency', $sStatus );
    }

    /**
     * Returns order currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_getValue( 'oepaypal_currency' );
    }

    /**
     * Adds new payment
     *
     * @param oePayPalOrderPayment $oPayment order payment
     *
     * @return bool
     */
    public function addPayment( oePayPalOrderPayment $oPayment )
    {
        $oPaymentList = $this->getPaymentList();
        $oPaymentList->addPayment( $oPayment );
        $this->setPaymentList( null );
    }

    /**
     * Return database gateway
     *
     * @return oePayPalPayPalOrderDbGateway
     */
    protected function _getDbGateway()
    {
        if ( is_null( $this->_oDbGateway ) ) {
            $this->_setDbGateway(oxNew( 'oePayPalPayPalOrderDbGateway' ) );
        }

        return $this->_oDbGateway;
    }

    /**
     * Return order payment list
     *
     * @return oePayPalOrderPaymentList
     */
    public function getPaymentList()
    {
        if ( is_null( $this->_oPaymentList ) ) {
            $oPaymentList = oxNew( 'oePayPalOrderPaymentList' );
            $oPaymentList->load( $this->getOrderId() );
            $this->setPaymentList( $oPaymentList );
        }

        return $this->_oPaymentList;
    }

    /**
     * Return order payment list
     *
     * @param oePayPal $oPaymentList payment list
     *
     * @return oePayPalOrderPaymentList
     */
    public function setPaymentList( $oPaymentList )
    {
        $this->_oPaymentList = $oPaymentList;
    }

}