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
 * PayPal response class for do verify with PayPal
 */
class oePayPalResponseDoVerifyWithPayPal extends oePayPalResponse
{
    /**
     * String PayPal sends if all is ok.
     * @var string
     */
    const PAYPAL_ACK = 'VERIFIED';

    /**
     * String PayPal receiver email. It should be same as shop owner credential for PayPal.
     * @var string
     */
    const RECEIVER_EMAIL = 'receiver_email';

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
     * Return if response verified as ACK from PayPal.
     *
     * @return boolean
     */
    public function isPayPalAck()
    {
        $aResponse = $this->getData();
        return isset( $aResponse[ self::PAYPAL_ACK ] );
    }

    /**
     * Return if response verified as ACK from PayPal.
     *
     * @return string
     */
    public function getReceiverEmail()
    {
        return $this->_getValue( self::RECEIVER_EMAIL );
    }

    /**
     * Return payment status.
     *
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->_getValue( self::PAYPAL_PAYMENT_STATUS );
    }

    /**
     * Return payment transaction id.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_getValue( self::PAYPAL_TRANSACTION_ID );
    }

    /**
     * Return payment currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_getValue( self::MC_CURRENCY );
    }

    /**
     * Return payment amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->_getValue( self::MC_GROSS );
    }

}