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
 * PayPal oxOrder class
 */
class oePayPalOxOrder extends oePayPalOxOrder_parent
{
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
        $sOrderId = oxRegistry::getSession()->getVariable( "sess_challenge" );

        // if order is not created yet - generating it
        if ( $sOrderId === null ) {
            $sOrderId = oxUtilsObject::getInstance()->generateUID();
            $this->setId( $sOrderId );
            $this->save();
            oxRegistry::getSession()->setVariable( "sess_challenge", $sOrderId );
        }

        return $this->load( $sOrderId );
    }

    /**
     *
     *
     * @return bool
     */
    public function oePayPalUpdateOrderNumber()
    {
        if ( $this->oxorder__oxordernr->value ) {
            $blUpdated = oxNew( 'oxCounter' )->update( $this->_getCounterIdent(), $this->oxorder__oxordernr->value );
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
        if ( $this->loadPayPalOrder() ) {
            $this->getPayPalOrder()->delete();
            return $this->delete();
        }
    }

    /**
     * Delete order together with PayPal order data
     *
     * @param null $sOxId
     * @return bool|void
     */
    public function delete( $sOxId = null )
    {
        $this->getPayPalOrder( $sOxId )->delete();
        parent::delete($sOxId);
    }

    /**
     * Updates order transaction status, ID and date.
     *
     * @param string $sTransactionId order transaction ID
     * @param string $sDate         order transaction date
     *
     */
    protected function _setPaymentInfoPayPalOrder( $sTransactionId, $sDate )
    {
        // set transaction ID and payment date to order
        $oDb = oxDb::getDb();

        $sQ = 'update oxorder set oxtransid='.$oDb->quote( $sTransactionId ).', oxpaid='.$oDb->quote( $sDate ).'  where oxid='.$oDb->quote( $this->getId() );
        $oDb->execute( $sQ );

        //updating order object
        $this->oxorder__oxtransid = new oxField( $sTransactionId );
        $this->oxorder__oxpaid    = new oxField( $sDate );
    }

    /**
     * Finalizes PayPal order
     *
     * @param  oePayPalResponseDoExpressCheckoutPayment $oResult       PayPal results array
     * @param oxUser    $oUser         User object
     * @param oxBasket  $oBasket	   Basket object
     * @param oxPayment $oPayment      Payment object
     * @param string    $sTransactionMode transaction mode Sale|Authorization
     *
     * @return null
     */
    public function finalizePayPalOrder( $oResult, $oBasket, $sTransactionMode )
    {
        $sDate   = date( 'Y-m-d H:i:s', oxRegistry::get("oxUtilsDate")->getTime() );

        // set order status, transaction ID and payment date to order
        $this->_setPaymentInfoPayPalOrder( $oResult->getTransactionId(), $sDate );

        $sCurrency = $oResult->getCurrencyCode();
        if ( !$sCurrency ) {
            $sCurrency = $this->getOrderCurrency()->name;
        }

        //PayPal order info
        $oPayPalOrder = $this->getPayPalOrder();
        $oPayPalOrder->setOrderId($this->getId());
        $oPayPalOrder->setPaymentStatus( 'pending' );
        $oPayPalOrder->setTransactionMode( $sTransactionMode );
        $oPayPalOrder->setCurrency( $sCurrency );
        $oPayPalOrder->setTotalOrderSum( $oBasket->getPrice()->getBruttoPrice() );
        if ( $sTransactionMode == 'Sale' ) {
            $oPayPalOrder->setCapturedAmount( $oBasket->getPrice()->getBruttoPrice() );
        }
        $oPayPalOrder->save();

        $oOrderPayment = oxNew( 'oePayPalOrderPayment' );
        $oOrderPayment->setTransactionId( $oResult->getTransactionId() );
        $oOrderPayment->setCorrelationId( $oResult->getCorrelationId() );
        $oOrderPayment->setDate( $sDate );
        $oOrderPayment->setAction( ($sTransactionMode == 'Sale') ? 'capture' : 'authorization' );
        $oOrderPayment->setStatus( $oResult->getPaymentStatus() );
        $oOrderPayment->setAmount( $oResult->getAmount() );
        $oOrderPayment->setCurrency( $oResult->getCurrencyCode() );

        //Adding payment information
        $oPaymentList = $this->getPayPalOrder()->getPaymentList();
        $oPaymentList->addPayment( $oOrderPayment );

        //setting order payment status after
        $oPaymentStatusCalculator = oxNew('oePayPalOrderPaymentStatusCalculator');
        $oPaymentStatusCalculator->setOrder( $this->getPayPalOrder() );
        $this->getPayPalOrder()->setPaymentStatus( $oPaymentStatusCalculator->getStatus() );
        $this->getPayPalOrder()->save();

        //clear PayPal identification
        $this->getSession()->deleteVariable( 'oepaypal' );
        $this->getSession()->deleteVariable( "oepaypal-payerId" );
        $this->getSession()->deleteVariable( "oepaypal-userId" );
        $this->getSession()->deleteVariable( 'oepaypal-basketAmount' );

    }

    /**
     * Checks if delivery set used for current order is available and active.
     * Throws exception if not available
     *
     * @param oxbasket $oBasket basket object
     *
     * @return null
     */
    public function validateDelivery( $oBasket )
    {
        if ( $oBasket->getPaymentId() == 'oxidpaypal' ) {
            $sShippingId = $oBasket->getShippingId();
            $dBasketPrice = $oBasket->getPrice()->getBruttoPrice();
            $oUser = oxNew( 'oxUser' );
            if (! $oUser->loadUserPayPalUser() ) {
                $oUser = $this->getUser();
            }
            if ( !$this->_isPayPalPaymentValid( $oUser, $dBasketPrice, $sShippingId ) ) {
                $iValidState = self::ORDER_STATE_INVALIDDELIVERY;
            }
        } else {
            $iValidState = parent::validateDelivery( $oBasket );
        }

        return $iValidState;
    }

    /**
     * Returns PayPal order object
     *
     * @param null $sOxId
     *
     * @return oePayPalPayPalOrder|null
     */
    public function getPayPalOrder( $sOxId = null )
    {
        if ( is_null( $this->_oPayPalOrder ) ) {
            $sOrderId = is_null($sOxId) ? $this->getId() : $sOxId;
            $oOrder = oxNew( 'oePayPalPayPalOrder' );
            $oOrder->load( $sOrderId );
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
        return $this->getPayPalOrder()->getPaymentStatus() ;
    }

    /**
     * Returns PayPal Authorization id
     */
    public function getAuthorizationId()
    {
        return $this->oxorder__oxtransid->value;
    }

    /**
     * Checks whether PayPal payment is available
     * @param $oUser
     * @param $dBasketPrice
     * @param $sShippingId
     *
     * @return bool
     */
    protected function _isPayPalPaymentValid( $oUser, $dBasketPrice, $sShippingId )
    {
        $blValid = true;

        $oPayPalPayment = oxNew( 'oxPayment');
        $oPayPalPayment->load( 'oxidpaypal' );
        if ( !$oPayPalPayment->isValidPayment( null, null, $oUser, $dBasketPrice, $sShippingId ) ) {
            $blValid = $this->_isEmptyPaymentValid( $oUser, $dBasketPrice, $sShippingId );
        }

        return $blValid;
    }

    /**
     * Checks whether Empty payment is available.
     * @param $sShippingId
     * @param $dBasketPrice
     * @param $oUser
     *
     * @return bool
     */
    protected function _isEmptyPaymentValid( $oUser, $dBasketPrice, $sShippingId )
    {
        $blValid = true;

        $oEmptyPayment = oxNew( 'oxPayment' );
        $oEmptyPayment->load( 'oxempty' );
        if ( !$oEmptyPayment->isValidPayment( null, null, $oUser, $dBasketPrice, $sShippingId ) ) {
            $blValid = false;
        }

        return $blValid;
    }

}