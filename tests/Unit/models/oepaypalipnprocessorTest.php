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
 * Testing oePayPalIPNRequestVerifier class.
 */
class Unit_oePayPal_Models_oePayPalIPNProcessorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSetGetRequest()
    {
        $oRequestSet = new oePayPalRequest();

        $oProcessor = new oePayPalIPNProcessor();
        $oProcessor->setRequest($oRequestSet);

        $this->assertEquals($oRequestSet, $oProcessor->getRequest(), 'Getter should return what is set in setter.');
    }

    public function testSetGetLang()
    {
        $oLang = new \OxidEsales\Eshop\Core\Language();
        $oProcessor = new oePayPalIPNProcessor();
        $oProcessor->setLang($oLang);

        $this->assertEquals($oLang, $oProcessor->getLang(), 'Getter should return what is set in setter.');
    }

    public function testSetGetPaymentBuilder()
    {
        $oPaymentBuilder = new oePayPalIPNPaymentBuilder();

        $oProcessor = new oePayPalIPNProcessor();
        $oProcessor->setPaymentBuilder($oPaymentBuilder);

        $this->assertEquals($oPaymentBuilder, $oProcessor->getPaymentBuilder(), 'Getter should return what is set in setter.');
    }

    public function testGetPaymentBuilder()
    {
        $oProcessor = new oePayPalIPNProcessor();

        $this->assertTrue($oProcessor->getPaymentBuilder() instanceof oePayPalIPNPaymentBuilder, 'Getter should create payment builder if nothing is set.');
    }

    public function testSetGetOrderManager()
    {
        $oOrderManager = new oePayPalOrderManager();

        $oProcessor = new oePayPalIPNProcessor();
        $oProcessor->setOrderManager($oOrderManager);

        $this->assertEquals($oOrderManager, $oProcessor->getOrderManager(), 'Getter should return what is set in setter.');
    }

    public function testGetOrderManager()
    {
        $oProcessor = new oePayPalIPNProcessor();

        $this->assertTrue($oProcessor->getOrderManager() instanceof oePayPalOrderManager, 'Getter should create order manager if nothing is set.');
    }

    public function testProcess()
    {
        $blOrderUpdated = true;
        $oLang = new \OxidEsales\Eshop\Core\Language();
        $oRequest = new oePayPalRequest();
        $oPayment = new oePayPalOrderPayment();
        // Call Payment Builder with defined lang and defined request. Will return mocked payment.
        $oPaymentBuilder = $this->_preparePaymentBuilder($oLang, $oRequest, $oPayment);
        // Call Order Manager with payment from payment builder. Will return if order updated == PayPal call processed.
        $oPayPalOrderManager = $this->_prepareOrderManager($oPayment, $blOrderUpdated);

        $oPayPalIPNProcessor = new oePayPalIPNProcessor();
        $oPayPalIPNProcessor->setLang($oLang);
        $oPayPalIPNProcessor->setRequest($oRequest);
        $oPayPalIPNProcessor->setPaymentBuilder($oPaymentBuilder);
        $oPayPalIPNProcessor->setOrderManager($oPayPalOrderManager);

        $this->assertEquals($blOrderUpdated, $oPayPalIPNProcessor->process(), 'Order manager decide if order updated - processed successfully.');
    }

    protected function _preparePaymentBuilder($oLang, $oRequest, $oPayment)
    {
        $oPaymentBuilder = $this->getMock('oePayPalIPNPaymentBuilder', array('setLang', 'setRequest', 'buildPayment'));
        $oPaymentBuilder->expects($this->atLeastOnce())->method('setLang')->with($oLang);
        $oPaymentBuilder->expects($this->atLeastOnce())->method('setRequest')->with($oRequest);
        $oPaymentBuilder->expects($this->atLeastOnce())->method('buildPayment')->will($this->returnValue($oPayment));

        return $oPaymentBuilder;
    }

    protected function _prepareOrderManager($oPayment, $blOrderUpdated)
    {
        $oOrderManager = $this->getMock('oePayPalOrderManager', array('setOrderPayment', 'updateOrderStatus'));
        $oOrderManager->expects($this->atLeastOnce())->method('setOrderPayment')->with($oPayment);
        $oOrderManager->expects($this->atLeastOnce())->method('updateOrderStatus')->will($this->returnValue($blOrderUpdated));

        return $oOrderManager;
    }
}