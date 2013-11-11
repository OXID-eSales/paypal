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
 * PayPal IPN request payment setter class
 */
class oePayPalIPNRequestPaymentSetter
{
    /**
     * Sandbox mode parameter name.
     * @var string
     */
    const PAYPAL_SANDBOX = 'test_ipn';

    /**
     * String PayPal payment status parameter name.
     * @var string
     */
    const PAYPAL_PAYMENT_STATUS = 'payment_status';

    /**
     * String PayPal transaction id.
     * @var string
     */
    const PAYPAL_TRANSACTION_ID = 'txn_id';

    /**
     * String PayPal whole price including payment and shipment.
     * @var string
     */
    const MC_GROSS = 'mc_gross';

    /**
     * String PayPal payment currency.
     * @var string
     */
    const MC_CURRENCY = 'mc_currency';

    /**
     * @var oePayPalRequest
     */
    protected $_oRequest = null;

    /**
     * @var oePayPalOrderPayment
     */
    protected $_oRequestOrderPayment = null;

    /**
     * Sets request object to get params for IPN request.
     * @param oePayPalRequest $oRequest
     */
    public function setRequest( $oRequest )
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Gets request object to get params for IPN request.
     * @return oePayPalRequest
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * @param oePayPalOrderPayment $oOrderPayment
     */
    public function setRequestOrderPayment( $oOrderPayment )
    {
        $this->_oRequestOrderPayment = $oOrderPayment;
    }

    /**
     * @return oePayPalOrderPayment
     */
    public function getRequestOrderPayment()
    {
        $this->_prepareOrderPayment( $this->_oRequestOrderPayment );
        return $this->_oRequestOrderPayment;
    }

    /**
     * Prepare PayPal payment. Fill up with request values.
     *
     * @param oePayPalOrderPayment $oRequestOrderPayment order to set params.
     */
    protected function _prepareOrderPayment( $oRequestOrderPayment )
    {
        $oRequest = $this->getRequest();

        $oRequestOrderPayment->setStatus( $oRequest->getRequestParameter( self::PAYPAL_PAYMENT_STATUS ) );
        $oRequestOrderPayment->setTransactionId( $oRequest->getRequestParameter( self::PAYPAL_TRANSACTION_ID ) );
        $oRequestOrderPayment->setCurrency( $oRequest->getRequestParameter( self::MC_CURRENCY ) );
        $oRequestOrderPayment->setAmount( $oRequest->getRequestParameter( self::MC_GROSS ) );
    }
}