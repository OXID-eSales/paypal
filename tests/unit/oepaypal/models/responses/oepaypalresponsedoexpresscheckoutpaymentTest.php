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

require_once realpath( '.' ).'/unit/OxidTestCase.php';
require_once realpath( '.' ).'/unit/test_config.inc.php';

/**
 * Testing oePayPalResponseDoExpressCheckoutPayment class.
 */
class Unit_oePayPal_models_responses_oePayPalResponseDoExpressCheckoutPaymentTest extends OxidTestCase
{
    /**
     * Returns response data
     *
     * @return array
     */
    protected function _getResponseData()
    {
        $aData = array(
            'PAYMENTINFO_0_TRANSACTIONID' => 'transactionID',
            'CORRELATIONID' => 'correlationID',
            'PAYMENTINFO_0_PAYMENTSTATUS' => 'confirmed',
            'PAYMENTINFO_0_AMT' => 1200,
            'PAYMENTINFO_0_CURRENCYCODE' => 'LTL'
        );

        return $aData;
    }

    /**
     * Test get transaction id
     */
    public function testGetTransactionId()
    {
        $oResponse = new oePayPalResponseDoExpressCheckoutPayment();
        $oResponse->setData( $this->_getResponseData() );
        $this->assertEquals( 'transactionID', $oResponse->getTransactionId() );
    }

    /**
     * Test get transaction id
     */
    public function testGetCorrelationId()
    {
        $oResponse = new oePayPalResponseDoExpressCheckoutPayment();
        $oResponse->setData( $this->_getResponseData() );
        $this->assertEquals( 'correlationID', $oResponse->getCorrelationId() );
    }

    /**
     * Test get payment status
     */
    public function testGetPaymentStatus()
    {
        $oResponse = new oePayPalResponseDoExpressCheckoutPayment();
        $oResponse->setData( $this->_getResponseData() );
        $this->assertEquals( 'confirmed', $oResponse->getPaymentStatus() );
    }

    /**
     * Test get price amount
     */
    public function testGetAmount()
    {
        $oResponse = new oePayPalResponseDoExpressCheckoutPayment();
        $oResponse->setData( $this->_getResponseData() );
        $this->assertEquals( 1200, $oResponse->getAmount() );
    }

    /**
     * Test get currency code
     */
    public function testGetCurrencyCode()
    {
        $oResponse = new oePayPalResponseDoExpressCheckoutPayment();
        $oResponse->setData( $this->_getResponseData() );
        $this->assertEquals( 'LTL', $oResponse->getCurrencyCode() );
    }
}
