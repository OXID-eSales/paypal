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
 * PayPal order action capture class
 */
class oePayPalOrderCaptureAction extends oePayPalOrderAction
{

    /**
     * @var oePayPalOrderReauthorizeActionHandler
     */
    protected $_oReauthorizeHandler = null;

    /**
     * Sets dependencies
     *
     * @param oePayPalOrderCaptureActionHandler $oHandler
     * @param oePayPalPayPalOrder $oOrder
     * @param oePayPalOrderReauthorizeActionHandler $oReauthorizeHandler
     */
    public function __construct( $oHandler, $oOrder, $oReauthorizeHandler )
    {
        parent::__construct( $oHandler, $oOrder );

        $this->_oReauthorizeHandler = $oReauthorizeHandler;
    }

    /**
     * Returns reauthorize action handler
     *
     * @return oePayPalOrderReauthorizeActionHandler
     */
    public function getReauthorizeHandler()
    {
        return $this->_oReauthorizeHandler;
    }

    /**
     * Processes PayPal response
     */
    public function process()
    {
        $this->_reauthorize();

        $oHandler = $this->getHandler();

        $oResponse = $oHandler->getPayPalResponse();
        $oData = $oHandler->getData();

        $this->_updateOrder( $oResponse, $oData );

        $oPayment = $this->_createPayment( $oResponse );
        $oPaymentList = $this->getOrder()->getPaymentList();
        $oPayment = $oPaymentList->addPayment( $oPayment );

        $this->_addComment( $oPayment, $oData->getComment() );
    }

    /**
     * Reauthorizes payment if order was captured at least once
     */
    protected function _reauthorize()
    {
        $oOrder = $this->getOrder();

        if ( $oOrder->getCapturedAmount() > 0 ) {
            $oHandler = $this->getReauthorizeHandler();
            try {
                $oResponse = $oHandler->getPayPalResponse();

                $oPayment = oxNew( 'oePayPalOrderPayment' );
                $oPayment->setDate($this->getDate());
                $oPayment->setTransactionId( $oResponse->getAuthorizationId() );
                $oPayment->setCorrelationId( $oResponse->getCorrelationId() );
                $oPayment->setAction('re-authorization');
                $oPayment->setStatus( $oResponse->getPaymentStatus() );

                $oOrder->getPaymentList()->addPayment( $oPayment );
            } catch (oePayPalResponseException $e) {
                // Ignore PayPal response exceptions
            }
        }
    }

    /**
     * Updates order with PayPal response info
     *
     * @param $oResponse
     * @param $oData
     * @return oePayPalPayPalOrder
     */
    protected function _updateOrder( $oResponse, $oData )
    {
        $oOrder = $this->getOrder();
        $oOrder->addCapturedAmount( $oResponse->getCapturedAmount() );
        $oOrder->setPaymentStatus( $oData->getOrderStatus() );
        $oOrder->save();
    }

    /**
     * Creates Payment object with PayPal response data
     *
     * @param $oResponse
     */
    protected function _createPayment( $oResponse )
    {
        $oPayment = oxNew( 'oePayPalOrderPayment' );
        $oPayment->setDate( $this->getDate() );
        $oPayment->setTransactionId( $oResponse->getTransactionId() );
        $oPayment->setCorrelationId( $oResponse->getCorrelationId() );
        $oPayment->setAction( 'capture' );
        $oPayment->setStatus( $oResponse->getPaymentStatus() );
        $oPayment->setAmount( $oResponse->getCapturedAmount() );
        $oPayment->setCurrency( $oResponse->getCurrency() );

        return $oPayment;
    }

    /**
     * Adds comment to given Payment object
     *
     * @param $oPayment
     * @param $sComment
     */
    protected function _addComment( $oPayment, $sComment )
    {
        if ( $sComment ) {
            $oComment = oxNew( 'oePayPalOrderPaymentComment' );
            $oComment->setComment( $sComment );
            $oPayment->addComment( $oComment );
        }
    }
}