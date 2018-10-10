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
 * Testing \OxidEsales\PayPalModule\Model\IPNPaymentBuilder class.
 */
class IPNPaymentBuilderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function setUp()
    {
        parent::setUp();

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
    }

    public function testSetGetRequest()
    {
        $request = $this->prepareRequest();

        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();
        $paymentBuilder->setRequest($request);

        $this->assertEquals($request, $paymentBuilder->getRequest(), 'Getter should return what is set in setter.');
    }

    public function testSetGetOrderPaymentSetter()
    {
        $payPalIPNPaymentSetter = new \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter();

        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();
        $paymentBuilder->setOrderPaymentSetter($payPalIPNPaymentSetter);

        $this->assertEquals($payPalIPNPaymentSetter, $paymentBuilder->getOrderPaymentSetter(), 'Getter should return what is set in setter.');
    }

    public function testGetOrderPaymentSetter()
    {
        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();
        $payPalIPNPaymentSetter = $paymentBuilder->getOrderPaymentSetter();

        $this->assertTrue(
            $payPalIPNPaymentSetter instanceof \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter,
            'Getter should create IPNRequestPaymentSetter.'
        );
    }

    public function testSetOrderPaymentValidator()
    {
        $payPalIPNPaymentValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();

        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();
        $paymentBuilder->setOrderPaymentValidator($payPalIPNPaymentValidator);

        $this->assertEquals($payPalIPNPaymentValidator, $paymentBuilder->getOrderPaymentValidator(), 'Getter should return what is set in Validator.');
    }

    public function testGetOrderPaymentValidator()
    {
        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();

        $this->assertTrue(
            $paymentBuilder->getOrderPaymentValidator() instanceof \OxidEsales\PayPalModule\Model\IPNPaymentValidator,
            'Getter should create \OxidEsales\PayPalModule\Model\IPNRequestValidator.'
        );
    }

    public function testSetGetPaymentCreator()
    {
        $payPalIPNPaymentCreator = new \OxidEsales\PayPalModule\Model\IPNPaymentCreator();

        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();
        $paymentBuilder->setOrderPaymentSetter($payPalIPNPaymentCreator);

        $this->assertEquals($payPalIPNPaymentCreator, $paymentBuilder->getPaymentCreator(), 'Getter should return what is set in setter.');
    }

    public function testGetPaymentCreator()
    {
        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();
        $payPalIPNPaymentCreator = $paymentBuilder->getPaymentCreator();

        $this->assertTrue(
            $payPalIPNPaymentCreator instanceof \OxidEsales\PayPalModule\Model\IPNPaymentCreator,
            'Getter should create \OxidEsales\PayPalModule\Model\IPNPaymentCreator.'
        );
    }


    public function testSetGetLang()
    {
        $lang = oxNew(\OxidEsales\Eshop\Core\Language::class);
        $paymentBuilder = new \OxidEsales\PayPalModule\Model\IPNPaymentBuilder();
        $paymentBuilder->setLang($lang);

        $this->assertEquals($lang, $paymentBuilder->getLang(), 'Getter should return what is set in Validator.');
    }

    /**
     * Data provider for function testGetPayment.
     */
    public function provideGetPayment()
    {
        return array(
            array(false, 'some validation message'),
            //            array( true, '' ),
        );
    }

    /**
     * Check if payment is formed from request.
     * Check if payment is formed with validator results.
     *
     * @param bool   $paymentValid     if payment is valid.
     * @param string $validationMessage validation message.
     *
     * @dataProvider provideGetPayment
     */
    public function testGetPayment($paymentValid, $validationMessage)
    {
        $transactionIdRequestPayment = '_someId';
        $transactionIdCreatedOrder = '_someOtherid';
        $request = $this->prepareRequest();
        $requestOrderPayment = $this->prepareOrderPayment($transactionIdRequestPayment);
        $orderPayment = $this->prepareOrderPayment($transactionIdCreatedOrder);
        $lang = $this->prepareLang();

        // Request Payment should be called with request object.
        $payPalIPNPaymentSetter = $this->prepareRequestPaymentSetter($request, $requestOrderPayment);
        $payPalIPNPaymentValidator = $this->prepareIPNPaymentValidator($requestOrderPayment, $orderPayment, $lang, $paymentValid, $validationMessage);

        // Mock order loading, so we do not touch database.
        /** @var \OxidEsales\PayPalModule\Model\IPNPaymentBuilder $paymentBuilder */
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\IPNPaymentBuilder::class);
        $mockBuilder->setMethods(['loadOrderPayment']);
        $paymentBuilder = $mockBuilder->getMock();
        $paymentBuilder->expects($this->atLeastOnce())->method('loadOrderPayment')->with($transactionIdRequestPayment)->will($this->returnValue($orderPayment));
        $paymentBuilder->setRequest($request);
        $paymentBuilder->setLang($lang);
        $paymentBuilder->setOrderPaymentSetter($payPalIPNPaymentSetter);
        $paymentBuilder->setOrderPaymentValidator($payPalIPNPaymentValidator);

        $buildOrderPayment = $paymentBuilder->buildPayment();

        // Get first comment as there should be only one.
        $comments = $buildOrderPayment->getCommentList();
        $comments = $comments->getArray();
        $comment = $comments[0]->getComment();
        // Payment should be built with validator results from setter request.
        // Save on order payment as it is already loaded from database.
        $this->assertEquals($paymentValid, $buildOrderPayment->getIsValid(), 'Payment should be valid or not as it is mocked in validator.');
        $this->assertEquals(1, count($comments), 'There should be only one comment - failure message.');
        $this->assertEquals($validationMessage, $comment, 'Validation message should be same as it is mocked in validator.');
        $this->assertEquals($transactionIdCreatedOrder, $buildOrderPayment->getTransactionId(), 'Payment should have same id as get from payment setter.');
    }

    /**
     * Wrapper to create request object.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    protected function prepareRequest()
    {
        $_POST['zzz'] = 'yyy';
        $request = new \OxidEsales\PayPalModule\Core\Request();

        return $request;
    }

    /**
     * Wrapper to create oxLang.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    protected function prepareLang()
    {
        $lang = oxNew(\OxidEsales\Eshop\Core\Language::class);

        return $lang;
    }

    /**
     * @param \OxidEsales\PayPalModule\Core\Request       $request      request.
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment order payment.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter
     */
    protected function prepareRequestPaymentSetter($request, $orderPayment)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::class);
        $mockBuilder->setMethods(['setRequest', 'getRequestOrderPayment']);
        $payPalIPNPaymentSetter = $mockBuilder->getMock();
        $payPalIPNPaymentSetter->expects($this->atLeastOnce())->method('setRequest')->with($request);
        $payPalIPNPaymentSetter->expects($this->atLeastOnce())->method('getRequestOrderPayment')->will($this->returnValue($orderPayment));

        return $payPalIPNPaymentSetter;
    }

    /**
     * Wrapper to create order payment.
     *
     * @param string $transactionId     transaction id.
     * @param bool   $valid            if payment should be marked as not valid.
     * @param string $validationMessage validation message
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function prepareOrderPayment($transactionId, $valid = true, $validationMessage = '')
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setPaymentId('__a24das5das45');
        $orderPayment->setOrderId('_sOrderId');
        $orderPayment->setTransactionId($transactionId);
        if (!$valid) {
            $orderPayment->setIsValid(false);
        }
        if ($validationMessage) {
            $utilsDate = \OxidEsales\Eshop\Core\Registry::getUtilsDate();
            $date = date('Y-m-d H:i:s', $utilsDate->getTime());
            $orderPayment->addComment($date . ' ' . $validationMessage);
        }

        return $orderPayment;
    }

    /**
     * Wrapper to create payment validator.
     * Check if called with correct parameters. Always return mocked validation information.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $requestOrderPayment object validator will be called with.
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment        object validator will return.
     * @param \OxidEsales\Eshop\Core\Language             $lang                set to validator to translate validation failure message.
     * @param bool                                        $valid              set if order is valid.
     * @param string                                      $validationMessage   validation message.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNPaymentValidator
     */
    protected function prepareIPNPaymentValidator($requestOrderPayment, $orderPayment, $lang, $valid, $validationMessage)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\IPNPaymentValidator::class);
        $mockBuilder->setMethods(['setRequestOrderPayment', 'setOrderPayment', 'setLang', 'isValid', 'getValidationFailureMessage']);
        $payPalIPNPaymentValidator = $mockBuilder->getMock();
        $payPalIPNPaymentValidator->expects($this->atLeastOnce())->method('setRequestOrderPayment')->with($requestOrderPayment);
        $payPalIPNPaymentValidator->expects($this->atLeastOnce())->method('setOrderPayment')->with($orderPayment);
        $payPalIPNPaymentValidator->expects($this->atLeastOnce())->method('setLang')->with($lang);
        $payPalIPNPaymentValidator->expects($this->atLeastOnce())->method('isValid')->will($this->returnValue($valid));
        // Validation message will be checked only when validation fail.
        $payPalIPNPaymentValidator->expects($this->any())->method('getValidationFailureMessage')->will($this->returnValue($validationMessage));

        return $payPalIPNPaymentValidator;
    }
}
