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
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Model\Response;

/**
 * Testing \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment class.
 */
class ResponseDoExpressCheckoutPaymentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Returns response data
     *
     * @return array
     */
    protected function getResponseData()
    {
        $data = array(
            'PAYMENTINFO_0_TRANSACTIONID' => 'transactionID',
            'CORRELATIONID'               => 'correlationID',
            'PAYMENTINFO_0_PAYMENTSTATUS' => 'confirmed',
            'PAYMENTINFO_0_AMT'           => 1200,
            'PAYMENTINFO_0_CURRENCYCODE'  => 'LTL'
        );

        return $data;
    }

    /**
     * Test get transaction id
     */
    public function testGetTransactionId()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment();
        $response->setData($this->getResponseData());
        $this->assertEquals('transactionID', $response->getTransactionId());
    }

    /**
     * Test get transaction id
     */
    public function testGetCorrelationId()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment();
        $response->setData($this->getResponseData());
        $this->assertEquals('correlationID', $response->getCorrelationId());
    }

    /**
     * Test get payment status
     */
    public function testGetPaymentStatus()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment();
        $response->setData($this->getResponseData());
        $this->assertEquals('confirmed', $response->getPaymentStatus());
    }

    /**
     * Test get price amount
     */
    public function testGetAmount()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment();
        $response->setData($this->getResponseData());
        $this->assertEquals(1200, $response->getAmount());
    }

    /**
     * Test get currency code
     */
    public function testGetCurrencyCode()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment();
        $response->setData($this->getResponseData());
        $this->assertEquals('LTL', $response->getCurrencyCode());
    }
}
