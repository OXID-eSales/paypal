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

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';

/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_responses_oePayPalResponseDoRefundTest extends OxidTestCase
{
    /**
     * Returns response data
     *
     * @return array
     */
    protected function _getResponseData()
    {
        $aData = array(
            'REFUNDTRANSACTIONID' => 'transactionId',
            'CORRELATIONID'       => 'correlationId',
            'REFUNDSTATUS'        => 'completed',
            'GROSSREFUNDAMT'      => 12.45,
            'CURRENCYCODE'        => 'LTL'
        );

        return $aData;
    }

    /**
     * Test get authorization id
     */
    public function testGeTransactionId()
    {
        $oResponse = new oePayPalResponseDoRefund();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('transactionId', $oResponse->getTransactionId());
    }

    /**
     * Test get correlation id
     */
    public function testGetCorrelationId()
    {
        $oResponse = new oePayPalResponseDoCapture();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('correlationId', $oResponse->getCorrelationId());
    }

    /**
     * Test get payment status
     */
    public function testGetPaymentStatus()
    {
        $oResponse = new oePayPalResponseDoRefund();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('completed', $oResponse->getPaymentStatus());
    }

    /**
     * Test get payment status
     */
    public function testGetRefundAmount()
    {
        $oResponse = new oePayPalResponseDoRefund();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals(12.45, $oResponse->getRefundAmount());
    }

    /**
     * Test get payment status
     */
    public function testGetCurrency()
    {
        $oResponse = new oePayPalResponseDoRefund();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('LTL', $oResponse->getCurrency());
    }
}
