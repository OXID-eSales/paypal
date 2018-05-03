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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing \OxidEsales\PayPalModule\Model\IPNPaymentCreator class.
 */
class IPNPaymentCreatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /** @var string test order dummy oxid */
    const TEST_ORDER_ID = '_sometestoxorderoxid';

    /** @var string PayPal transaction id */
    const PAYPAL_TRANSACTION_ID = '5H706430HM112602B';

    /** @var string PayPal id of authorization transaction */
    const PAYPAL_AUTHID = '5H706430HM1126666';

    /** @var string PayPal parent transaction id*/
    const PAYPAL_PARENT_TRANSACTION_ID = '8HF77866N86936335';

    /** @var string Amount paid*/
    const PAYMENT_AMOUNT = 30.66;

    /** @var string currency specifier*/
    const PAYMENT_CURRENCY = 'EUR';

    /** @var string PayPal correlation id for transaction*/
    const PAYMENT_CORRELATION_ID = '361b9ebf97777';

    /** @var string PayPal correlation id for authorization transaction*/
    const AUTH_CORRELATION_ID = '361b9ebf9bcee';

    /**
     * Fixture set up.
     */
    protected function setUp()
    {
        parent::setUp();

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
    }

    /**
     * Test setter and getter.
     */
    public function testSetGetRequest()
    {
        $data = $this->getIPNDataCapture();
        $request = $this->prepareRequest($data);

        $paymentBuilder = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
        $paymentBuilder->setRequest($request);

        $this->assertEquals($request, $paymentBuilder->getRequest(), 'Getter should return what is set in setter.');
    }

    /**
     * Test handling new paypal order payment from IPN.
     * Case that we do not have a parent transaction and nothing needs to be done.
     */
    public function testPayPalIPNPaymentCreationNoParentFound()
    {
        $ipnData = $this->getIPNDataCapture();
        $request = $this->prepareRequest($ipnData);
        $paymentCreator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
        $paymentCreator->setRequest($request);

        $requestOrderPayment = $this->createPayPalOrderPaymentFromRequest($ipnData, 'capture');
        $requestOrderPayment = $paymentCreator->handleOrderPayment($requestOrderPayment);

        $this->assertNull($requestOrderPayment);
    }

    /**
     * Test handling new paypal order payment from IPN.
     * Case that parent transaction exists and is of kind authorization
     */
    public function testPayPalIPNPaymentCreationForNewCapture()
    {
        $this->createPayPalOrderPaymentAuthorization();

        $ipnData = $this->getIPNDataCapture();
        $request = $this->prepareRequest($ipnData);
        $paymentCreator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
        $paymentCreator->setRequest($request);

        $requestOrderPayment = $this->createPayPalOrderPaymentFromRequest($ipnData,'capture');
        $requestOrderPayment = $paymentCreator->handleOrderPayment($requestOrderPayment);

        $this->assertTrue( 0 < $requestOrderPayment->getId());
    }

    /**
     * Test handling new paypal order payment from IPN.
     * Case that parent transaction exists and is of kind authorization.
     * Test if payment memo is added correctly.
     */
    public function testPayPalIPNPaymentCreationForNewCaptureWithMemo()
    {
        $this->createPayPalOrderPaymentAuthorization();

        $ipnData = $this->getIPNDataCapture();
        $request = $this->prepareRequest($ipnData);
        $paymentCreator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
        $paymentCreator->setRequest($request);

        $requestOrderPayment = $this->createPayPalOrderPaymentFromRequest($ipnData,'capture');
        $requestOrderPayment = $paymentCreator->handleOrderPayment($requestOrderPayment);

        $memoList = $requestOrderPayment->getCommentList();
        $this->assertEquals($memoList->getPaymentId(), $requestOrderPayment->getId());
        $this->assertEquals('capture_new', $memoList->current()->getComment());
    }

    /**
     * Test handling new paypal order payment from IPN.
     * Case that parent transaction exists and is of kind authorization.
     * Test if authorization status is changed from Pending to Completed.
     */
    public function testPayPalIPNPaymentAuthorizationStatusUpdate()
    {
        $authorization = $this->createPayPalOrderPaymentAuthorization();
        $this->assertEquals('Pending', $authorization->getStatus());

        $ipnData = $this->getIPNDataCapture();
        $request = $this->prepareRequest($ipnData);
        $paymentCreator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
        $paymentCreator->setRequest($request);

        $requestOrderPayment = $this->createPayPalOrderPaymentFromRequest($ipnData,'capture');
        $paymentCreator->handleOrderPayment($requestOrderPayment);

        $authorization->load();
        $this->assertEquals('Completed', $authorization->getStatus());
    }

    /**
     * Test handling new paypal order refund payment from IPN.
     * Case that parent transaction exists and is of kind capture.
     */
    public function testPayPalIPNPaymentRefund()
    {
        $this->createPayPalOrderPaymentAuthorization();
        $parentTransaction = $this->createPayPalOrderPaymentParent();

        $ipnData = $this->getIPNDataRefund();
        $request = $this->prepareRequest($ipnData);
        $paymentCreator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
        $paymentCreator->setRequest($request);

        $requestOrderPayment = $this->createPayPalOrderPaymentFromRequest($ipnData,'refund');
        $requestOrderPayment = $paymentCreator->handleOrderPayment($requestOrderPayment);
        $this->assertTrue( 0 < $requestOrderPayment->getId());

        $parentTransaction->load();
        $this->assertEquals(self::PAYMENT_AMOUNT * 0.5, $parentTransaction->getRefundedAmount());
    }

    /**
     * Wrapper to create request object.
     *
     * @param array $data
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    private function prepareRequest($data)
    {
        $_POST = $data;
        $request = new \OxidEsales\PayPalModule\Core\Request();

        return $request;
    }

    /**
     * Get IPN test data for capture transaction.
     *
     * @return array
     */
    private function getIPNDataCapture()
    {
        $data = array(
            'payment_type'         => 'instant',
            'payment_date'         => '00:54:36 Jun 03, 2015 PDT',
            'payment_status'       => 'Completed',
            'payer_status'         => 'verified',
            'first_name'           => 'Max',
            'last_name'            => 'Muster',
            'payer_email'          => 'buyer@paypalsandbox_com',
            'payer_id'             => 'TESTBUYERID01',
            'address_name'         => 'Max_Muster',
            'address_city'         => 'Freiburg',
            'address_street'       => 'Blafööstraße_123',
            'charset'              => 'UTF-8',
            'transaction_entity'   => 'payment',
            'address_country_code' => 'DE',
            'notify_version'       => '3_8',
            'custom'               => 'Bestellnummer_8',
            'parent_txn_id'        => self::PAYPAL_AUTHID,
            'txn_id'               => self::PAYPAL_TRANSACTION_ID,
            'auth_id'              => self::PAYPAL_AUTHID,
            'receiver_email'       => 'devbiz@oxid-esales_com',
            'item_name'            => 'Bestellnummer_8',
            'mc_currency'          => 'EUR',
            'test_ipn'             => '1',
            'auth_amount'          => self::PAYMENT_AMOUNT,
            'mc_gross'             => self::PAYMENT_AMOUNT,
            'correlation_id'       => self::PAYMENT_CORRELATION_ID,
            'auth_status'          => 'Completed',
            'memo'                 => 'capture_new'
        );

        return $data;
    }

    /**
     * Get IPN test data for refund transaction.
     *
     * @return array
     */
    private function getIPNDataRefund()
    {
        $data = array(
            'payment_type'         => 'instant',
            'payment_date'         => '00:54:36 Jun 03, 2015 PDT',
            'payment_status'       => 'Refunded',
            'payer_status'         => 'verified',
            'first_name'           => 'Max',
            'last_name'            => 'Muster',
            'payer_email'          => 'buyer@paypalsandbox_com',
            'payer_id'             => 'TESTBUYERID01',
            'address_name'         => 'Max_Muster',
            'address_city'         => 'Freiburg',
            'address_street'       => 'Blafööstraße_123',
            'charset'              => 'UTF-8',
            'transaction_entity'   => 'payment',
            'address_country_code' => 'DE',
            'notify_version'       => '3_8',
            'custom'               => 'Bestellnummer_8',
            'parent_txn_id'        => self::PAYPAL_PARENT_TRANSACTION_ID,
            'txn_id'               => self::PAYPAL_TRANSACTION_ID,
            'auth_id'              => self::PAYPAL_AUTHID,
            'receiver_email'       => 'devbiz@oxid-esales_com',
            'item_name'            => 'Bestellnummer_8',
            'mc_currency'          => 'EUR',
            'test_ipn'             => '1',
            'auth_amount'          => self::PAYMENT_AMOUNT,
            'mc_gross'             => -self::PAYMENT_AMOUNT * 0.5,
            'correlation_id'       => self::AUTH_CORRELATION_ID,
            'memo'                 => 'refund_new'
        );

        return $data;
    }

    /**
     * Create order payment authorization or capture.
     * Object is NOT saved into db.
     *
     * @param string $mode Chose type of orderpayment (authorization or capture)
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    private function createPayPalOrderPaymentFromRequest($fromIPN, $action = 'capture')
    {
        $paypalOrderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $paypalOrderPayment->setOrderid(self::TEST_ORDER_ID);
        $paypalOrderPayment->setAction($action);
        $paypalOrderPayment->setTransactionId($fromIPN['txn_id']);
        $paypalOrderPayment->setAmount(abs($fromIPN['mc_gross']));
        $paypalOrderPayment->setCurrency($fromIPN['mc_currency']);
        $paypalOrderPayment->setStatus($fromIPN['payment_status']);
        $paypalOrderPayment->setCorrelationId($fromIPN['correlation_id']);
        $paypalOrderPayment->setDate('2015-04-01 13:13:13');

        return $paypalOrderPayment;
    }

    /**
     * Create order payment authorization or capture.
     * Object is NOT saved into db.
     *
     * @param string $mode Chose type of orderpayment (authorization or capture)
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    private function createPayPalOrderPayment($mode = 'authorization')
    {
        $data                  = array();
        $data['authorization'] = array(
            'setAction'        => 'authorization',
            'setTransactionId' => self::PAYPAL_AUTHID,
            'setAmount'        => self::PAYMENT_AMOUNT,
            'setCurrency'      => self::PAYMENT_CURRENCY,
            'setStatus'        => 'Pending',
            'setCorrelationId' => self::AUTH_CORRELATION_ID,
            'setDate'          => '2015-04-01 12:12:12'
        );
        $data['capture']       = array(
            'setAction'        => 'capture',
            'setTransactionId' => self::PAYPAL_PARENT_TRANSACTION_ID,
            'setAmount'        => self::PAYMENT_AMOUNT,
            'setCurrency'      => self::PAYMENT_CURRENCY,
            'setStatus'        => 'Completed',
            'setCorrelationId' => self::PAYMENT_CORRELATION_ID,
            'setDate'          => '2015-04-01 13:13:13'
        );

        $paypalOrderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $paypalOrderPayment->setOrderid(self::TEST_ORDER_ID);

        foreach ($data[$mode] as $function => $argument) {
            $paypalOrderPayment->$function($argument);
        }
        $paypalOrderPayment->save();

        return $paypalOrderPayment;
    }

    /**
     * Test helper for creating order payment parent transaction with PayPal.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    private function createPayPalOrderPaymentAuthorization()
    {
        $orderPayment = $this->createPayPalOrderPayment('authorization');
        return $orderPayment;
    }

    /**
     * Test helper for creating order payment parent transaction with PayPal.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    private function createPayPalOrderPaymentParent()
    {
        $orderPaymentParent = $this->createPayPalOrderPayment('capture');
        return $orderPaymentParent;
    }
}
