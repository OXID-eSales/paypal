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

require_once realpath(".") . '/unit/OxidTestCase.php';
require_once realpath(".") . '/unit/test_config.inc.php';

if (!class_exists('oePayPalWrapping_parent')) {
    class oePayPalWrapping_parent extends wrapping
    {
    }
}

/**
 * Testing oePayPalWrapping class.
 */
class Unit_oePayPal_Controllers_oePayPalWrappingTest extends OxidTestCase
{
    /**
     * Test case for oePayPalWrapping::isPayPal()
     *
     * @return null
     */
    public function testIsPayPal()
    {
        $oView = new oePayPalWrapping();

        $this->getSession()->setVariable("paymentid", "oxidpaypal");
        $this->assertTrue($oView->isPayPal());

        $this->getSession()->setVariable("paymentid", "notoxidpaypal");
        $this->assertFalse($oView->isPayPal());
    }

    /**
     * Test case for oePayPalWrapping::changeWrapping() - express chekcout
     *
     * @return null
     */
    public function testChangeWrapping_expressCheckout()
    {
        $oView = $this->getMock("oePayPalWrapping", array("isPayPal"));
        $oView->expects($this->once())->method("isPayPal")->will($this->returnValue(true));

        $this->getSession()->setVariable("oepaypal", "2");
        $this->assertEquals("basket", $oView->changeWrapping());
    }

    /**
     * Test case for oePayPalWrapping::changeWrapping() - standart chekout
     *
     * @return null
     */
    public function testChangeWrapping_standardCheckout()
    {
        $oView = $this->getMock("oePayPalWrapping", array("isPayPal"));
        $oView->expects($this->once())->method("isPayPal")->will($this->returnValue(true));

        $this->getSession()->setVariable("oepaypal", "1");
        $this->assertEquals("payment", $oView->changeWrapping());
    }

    /**
     * Test case for oePayPalWrapping::changeWrapping() - PayPal not active
     *
     * @return null
     */
    public function testChangeWrapping_PayPalNotActive()
    {
        $oView = $this->getMock("oePayPalWrapping", array("isPayPal"));
        $oView->expects($this->once())->method("isPayPal")->will($this->returnValue(false));

        $this->assertEquals("order", $oView->changeWrapping());
    }

}