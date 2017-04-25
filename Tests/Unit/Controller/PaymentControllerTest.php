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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Controller;

/**
 * Testing PaymentController class.
 */
class PaymentControllerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Test case for PaymentController::validatePayment()
     * Test validatePayment
     */
    public function testValidatePayment()
    {
        // forcing payment id
        $this->setRequestParameter("paymentid", null);

        $oView = new \OxidEsales\PayPalModule\Controller\PaymentController();
        $this->assertNull($oView->validatePayment());

        // forcing payment id
        $this->setRequestParameter("paymentid", "oxidpaypal");
        $this->setRequestParameter("displayCartInPayPal", 1);

        $oView = new \OxidEsales\PayPalModule\Controller\PaymentController();
        $this->assertEquals("oepaypalstandarddispatcher?fnc=setExpressCheckout&displayCartInPayPal=1", $oView->validatePayment());
        $this->assertEquals("oxidpaypal", $this->getSession()->getVariable("paymentid"));
    }

    /**
     * Test, that the method validatePayment if the order was already checked by paypal.
     */
    public function testValidatePaymentIfCheckedByPayPal()
    {
        // forcing payment id
        $this->setRequestParameter("paymentid", "oxidpaypal");
        $this->setRequestParameter("displayCartInPayPal", 1);

        $oView = $this->getMock(\OxidEsales\PayPalModule\Controller\PaymentController::class, array("isConfirmedByPayPal"));
        $oView->expects($this->once())->method("isConfirmedByPayPal")->will($this->returnValue(true));

        $this->assertNull($oView->validatePayment());
        $this->assertNull($this->getSession()->getVariable("paymentid"));
    }

    /**
     * Test case for PaymentController::isConfirmedByPayPal()
     * Test isConfirmedByPayPal
     */
    public function testIsConfirmedByPayPal()
    {
        // forcing payment id
        $this->setRequestParameter("paymentid", "oxidpaypal");
        $this->getSession()->setVariable("oepaypal-basketAmount", 129.00);

        $oPrice = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array("getBruttoPrice"));
        $oPrice->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(129.00));

        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array("getPrice"));
        $oBasket->expects($this->once())->method("getPrice")->will($this->returnValue($oPrice));

        $oView = new \OxidEsales\PayPalModule\Controller\PaymentController();

        $this->assertTrue($oView->isConfirmedByPayPal($oBasket));
    }
}
