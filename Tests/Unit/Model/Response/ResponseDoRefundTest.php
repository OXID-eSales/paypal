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
 * Testing oxAccessRightException class.
 */
class ResponseDoRefundTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Returns response data
     *
     * @return array
     */
    protected function getResponseData()
    {
        $data = array(
            'REFUNDTRANSACTIONID' => 'transactionId',
            'CORRELATIONID'       => 'correlationId',
            'REFUNDSTATUS'        => 'completed',
            'GROSSREFUNDAMT'      => 12.45,
            'CURRENCYCODE'        => 'LTL'
        );

        return $data;
    }

    /**
     * Test get authorization id
     */
    public function testGeTransactionId()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund();
        $response->setData($this->getResponseData());
        $this->assertEquals('transactionId', $response->getTransactionId());
    }

    /**
     * Test get correlation id
     */
    public function testGetCorrelationId()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoCapture();
        $response->setData($this->getResponseData());
        $this->assertEquals('correlationId', $response->getCorrelationId());
    }

    /**
     * Test get payment status
     */
    public function testGetPaymentStatus()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund();
        $response->setData($this->getResponseData());
        $this->assertEquals('completed', $response->getPaymentStatus());
    }

    /**
     * Test get payment status
     */
    public function testGetRefundAmount()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund();
        $response->setData($this->getResponseData());
        $this->assertEquals(12.45, $response->getRefundAmount());
    }

    /**
     * Test get payment status
     */
    public function testGetCurrency()
    {
        $response = new \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund();
        $response->setData($this->getResponseData());
        $this->assertEquals('LTL', $response->getCurrency());
    }
}
