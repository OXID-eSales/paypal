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


/**
 * Testing oePayPalIPNPaymentBuilder class.
 */
class Unit_oePayPal_Models_oePayPalIPNPaymentBuilderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function setUp()
    {
        parent::setUp();

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
    }

    public function testSetGetRequest()
    {
        $oRequest = $this->_prepareRequest();

        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();
        $oPaymentBuilder->setRequest($oRequest);

        $this->assertEquals($oRequest, $oPaymentBuilder->getRequest(), 'Getter should return what is set in setter.');
    }

    public function testSetGetOrderPaymentSetter()
    {
        $oPayPalIPNPaymentSetter = new oePayPalIPNRequestPaymentSetter();

        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();
        $oPaymentBuilder->setOrderPaymentSetter($oPayPalIPNPaymentSetter);

        $this->assertEquals($oPayPalIPNPaymentSetter, $oPaymentBuilder->getOrderPaymentSetter(), 'Getter should return what is set in setter.');
    }

    public function testGetOrderPaymentSetter()
    {
        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();
        $oPayPalIPNPaymentSetter = $oPaymentBuilder->getOrderPaymentSetter();

        $this->assertTrue($oPayPalIPNPaymentSetter instanceof oePayPalIPNRequestPaymentSetter, 'Getter should create oePayPalIPNRequestPaymentSetter.');
    }

    public function testSetOrderPaymentValidator()
    {
        $oPayPalIPNPaymentValidator = new oePayPalIPNPaymentValidator();

        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();
        $oPaymentBuilder->setOrderPaymentValidator($oPayPalIPNPaymentValidator);

        $this->assertEquals($oPayPalIPNPaymentValidator, $oPaymentBuilder->getOrderPaymentValidator(), 'Getter should return what is set in Validator.');
    }

    public function testGetOrderPaymentValidator()
    {
        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();

        $this->assertTrue($oPaymentBuilder->getOrderPaymentValidator() instanceof oePayPalIPNPaymentValidator, 'Getter should create oePayPalIPNRequestValidator.');
    }

    public function testSetGetPaymentCreator()
    {
        $oPayPalIPNPaymentCreator = new oePayPalIPNPaymentCreator();

        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();
        $oPaymentBuilder->setOrderPaymentSetter($oPayPalIPNPaymentCreator);

        $this->assertEquals($oPayPalIPNPaymentCreator, $oPaymentBuilder->getPaymentCreator(), 'Getter should return what is set in setter.');
    }

    public function testGetPaymentCreator()
    {
        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();
        $oPayPalIPNPaymentCreator = $oPaymentBuilder->getPaymentCreator();

        $this->assertTrue($oPayPalIPNPaymentCreator instanceof oePayPalIPNPaymentCreator, 'Getter should create oePayPalIPNPaymentCreator.');
    }


    public function testSetGetLang()
    {
        $oLang = new \OxidEsales\Eshop\Core\Language();
        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();
        $oPaymentBuilder->setLang($oLang);

        $this->assertEquals($oLang, $oPaymentBuilder->getLang(), 'Getter should return what is set in Validator.');
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
     * @param bool   $blPaymentValid     if payment is valid.
     * @param string $sValidationMessage validation message.
     *
     * @dataProvider provideGetPayment
     */
    public function testGetPayment($blPaymentValid, $sValidationMessage)
    {
        $sTransactionIdRequestPayment = '_someId';
        $sTransactionIdCreatedOrder = '_someOtherid';
        $oRequest = $this->_prepareRequest();
        $oRequestOrderPayment = $this->_prepareOrderPayment($sTransactionIdRequestPayment);
        $oOrderPayment = $this->_prepareOrderPayment($sTransactionIdCreatedOrder);
        $oLang = $this->_prepareLang();

        // Request Payment should be called with request object.
        $oPayPalIPNPaymentSetter = $this->_prepareRequestPaymentSetter($oRequest, $oRequestOrderPayment);
        $oPayPalIPNPaymentValidator = $this->_prepareIPNPaymentValidator($oRequestOrderPayment, $oOrderPayment, $oLang, $blPaymentValid, $sValidationMessage);

        // Mock order loading, so we do not touch database.
        /** @var oePayPalIPNPaymentBuilder $oPaymentBuilder */
        $oPaymentBuilder = $this->getMock('oePayPalIPNPaymentBuilder', array('_loadOrderPayment'));
        $oPaymentBuilder->expects($this->atLeastOnce())->method('_loadOrderPayment')->with($sTransactionIdRequestPayment)->will($this->returnValue($oOrderPayment));
        $oPaymentBuilder->setRequest($oRequest);
        $oPaymentBuilder->setLang($oLang);
        $oPaymentBuilder->setOrderPaymentSetter($oPayPalIPNPaymentSetter);
        $oPaymentBuilder->setOrderPaymentValidator($oPayPalIPNPaymentValidator);

        $oBuildOrderPayment = $oPaymentBuilder->buildPayment();

        // Get first comment as there should be only one.
        $aComments = $oBuildOrderPayment->getCommentList();
        $aComments = $aComments->getArray();
        $sComment = $aComments[0]->getComment();
        // Payment should be built with validator results from setter request.
        // Save on order payment as it is already loaded from database.
        $this->assertEquals($blPaymentValid, $oBuildOrderPayment->getIsValid(), 'Payment should be valid or not as it is mocked in validator.');
        $this->assertEquals(1, count($aComments), 'There should be only one comment - failure message.');
        $this->assertEquals($sValidationMessage, $sComment, 'Validation message should be same as it is mocked in validator.');
        $this->assertEquals($sTransactionIdCreatedOrder, $oBuildOrderPayment->getTransactionId(), 'Payment should have same id as get from payment setter.');
    }

    /**
     * Wrapper to create request object.
     *
     * @return oePayPalRequest
     */
    protected function _prepareRequest()
    {
        $_POST['zzz'] = 'yyy';
        $oRequest = new oePayPalRequest();

        return $oRequest;
    }

    /**
     * Wrapper to create oxLang.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    protected function _prepareLang()
    {
        $oLang = new \OxidEsales\Eshop\Core\Language();

        return $oLang;
    }

    /**
     * @param oePayPalRequest      $oRequest      request.
     * @param oePayPalOrderPayment $oOrderPayment order payment.
     *
     * @return oePayPalIPNRequestPaymentSetter
     */
    protected function _prepareRequestPaymentSetter($oRequest, $oOrderPayment)
    {
        $oPayPalIPNPaymentSetter = $this->getMock('oePayPalIPNRequestPaymentSetter', array('setRequest', 'getRequestOrderPayment'));
        $oPayPalIPNPaymentSetter->expects($this->atLeastOnce())->method('setRequest')->with($oRequest);
        $oPayPalIPNPaymentSetter->expects($this->atLeastOnce())->method('getRequestOrderPayment')->will($this->returnValue($oOrderPayment));

        return $oPayPalIPNPaymentSetter;
    }

    /**
     * Wrapper to create order payment.
     *
     * @param string $sTransactionId     transaction id.
     * @param bool   $blValid            if payment should be marked as not valid.
     * @param string $sValidationMessage validation message
     *
     * @return oePayPalOrderPayment
     */
    protected function _prepareOrderPayment($sTransactionId, $blValid = true, $sValidationMessage = '')
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setPaymentId('__a24das5das45');
        $oOrderPayment->setOrderId('_sOrderId');
        $oOrderPayment->setTransactionId($sTransactionId);
        if (!$blValid) {
            $oOrderPayment->setIsValid(false);
        }
        if ($sValidationMessage) {
            $utilsDate = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsDate::class);
            $sDate = date('Y-m-d H:i:s', $utilsDate->getTime());
            $oOrderPayment->addComment($sDate, $sValidationMessage);
        }

        return $oOrderPayment;
    }

    /**
     * Wrapper to create payment validator.
     * Check if called with correct parameters. Always return mocked validation information.
     *
     * @param oePayPalOrderPayment            $oRequestOrderPayment object validator will be called with.
     * @param oePayPalOrderPayment            $oOrderPayment        object validator will return.
     * @param \OxidEsales\Eshop\Core\Language $oLang                set to validator to translate validation failure message.
     * @param bool                            $blValid              set if order is valid.
     * @param string                          $sValidationMessage   validation message.
     *
     * @return oePayPalIPNPaymentValidator
     */
    protected function _prepareIPNPaymentValidator($oRequestOrderPayment, $oOrderPayment, $oLang, $blValid, $sValidationMessage)
    {
        $oPayPalIPNPaymentValidator = $this->getMock(
            'oePayPalIPNPaymentValidator'
            , array('setRequestOrderPayment', 'setOrderPayment', 'setLang', 'isValid', 'getValidationFailureMessage')
        );
        $oPayPalIPNPaymentValidator->expects($this->atLeastOnce())->method('setRequestOrderPayment')->with($oRequestOrderPayment);
        $oPayPalIPNPaymentValidator->expects($this->atLeastOnce())->method('setOrderPayment')->with($oOrderPayment);
        $oPayPalIPNPaymentValidator->expects($this->atLeastOnce())->method('setLang')->with($oLang);
        $oPayPalIPNPaymentValidator->expects($this->atLeastOnce())->method('isValid')->will($this->returnValue($blValid));
        // Validation message will be checked only when validation fail.
        $oPayPalIPNPaymentValidator->expects($this->any())->method('getValidationFailureMessage')->will($this->returnValue($sValidationMessage));

        return $oPayPalIPNPaymentValidator;
    }
}
