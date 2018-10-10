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

namespace OxidEsales\PayPalModule\Tests\Unit\Controller;

/**
 * Testing WrappingController class.
 */
class WrappingControllerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Test case for WrappingController::isPayPal()
     */
    public function testIsPayPal()
    {
        $view = oxNew(\OxidEsales\Eshop\Application\Controller\WrappingController::class);

        $this->getSession()->setVariable("paymentid", "oxidpaypal");
        $this->assertTrue($view->isPayPal());

        $this->getSession()->setVariable("paymentid", "notoxidpaypal");
        $this->assertFalse($view->isPayPal());
    }

    /**
     * Test case for WrappingController::changeWrapping() - express chekcout
     */
    public function testChangeWrapping_expressCheckout()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\WrappingController::class);
        $mockBuilder->setMethods(['isPayPal']);
        $view = $mockBuilder->getMock();
        $view->expects($this->once())->method("isPayPal")->will($this->returnValue(true));

        $this->getSession()->setVariable("oepaypal", "2");
        $this->assertEquals("basket", $view->changeWrapping());
    }

    /**
     * Test case for WrappingController::changeWrapping() - standart chekout
     */
    public function testChangeWrapping_standardCheckout()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\WrappingController::class);
        $mockBuilder->setMethods(['isPayPal']);
        $view = $mockBuilder->getMock();
        $view->expects($this->once())->method("isPayPal")->will($this->returnValue(true));

        $this->getSession()->setVariable("oepaypal", "1");
        $this->assertEquals("payment", $view->changeWrapping());
    }

    /**
     * Test case for WrappingController::changeWrapping() - PayPal not active
     */
    public function testChangeWrapping_PayPalNotActive()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\WrappingController::class);
        $mockBuilder->setMethods(['isPayPal']);
        $view = $mockBuilder->getMock();
        $view->expects($this->once())->method("isPayPal")->will($this->returnValue(false));

        $this->assertEquals("order", $view->changeWrapping());
    }
}