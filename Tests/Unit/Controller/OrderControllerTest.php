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

use OxidEsales\Eshop\Application\Controller\OrderController;

if (!class_exists('oePayPalOrder_parent')) {
    class oePayPalOrder_parent extends \OxidEsales\Eshop\Application\Controller\OrderController
    {
    }
}

/**
 * Testing oePayPaleOrder class.
 */
class OrderControllerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("delete from oxpayments where OXID = 'oxidpaypal' ");

        $this->cleanUpTable('oxuser', 'oxid');

        parent::tearDown();
    }

    /**
     * Test case for OrderController::isPayPal()
     */
    public function testIsPayPal()
    {
        $view = oxNew(OrderController::class);

        $this->getSession()->setVariable("paymentid", "oxidpaypal");
        $this->assertTrue($view->isPayPal());

        $this->getSession()->setVariable("paymentid", "testPayment");
        $this->assertFalse($view->isPayPal());
    }

    /**
     * Data provider for getUser test
     *
     * @return array
     */
    public function providerGetUser()
    {
        return array(
            array('oxidpaypal', '_testPayPalUser', 'oxdefaultadmin', '_testPayPalUser'),
            array('oxidpaypal', null, 'oxdefaultadmin', 'oxdefaultadmin'),
            array('nonpaypalpayment', '_testPayPalUser', 'oxdefaultadmin', 'oxdefaultadmin'),
        );
    }

    /**
     * PayPal active, PayPal user is set, PayPal user loaded
     *
     * @dataProvider providerGetUser
     */
    public function testGetUser($paymentId, $payPalUserId, $defaultUserId, $expectedUserId)
    {
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->setId($payPalUserId);
        $user->save();

        $this->getSession()->setVariable("paymentid", $paymentId);
        $this->getSession()->setVariable("oepaypal-userId", $payPalUserId);
        $this->getSession()->setVariable('usr', $defaultUserId);

        $order = oxNew(\OxidEsales\Eshop\Application\Controller\OrderController::class);
        $order->setUser(null);
        $user = $order->getUser();

        $this->assertEquals($expectedUserId, $user->oxuser__oxid->value);
    }

    /**
     * PayPal active, PayPal user is set, PayPal user loaded
     *
     * @dataProvider providerGetUser
     */
    public function testGetUser_NonExistingPayPalUser_DefaultUserReturned()
    {
        $this->getSession()->setVariable("paymentid", 'oxidpaypal');
        $this->getSession()->setVariable("oepaypal-userId", 'nonExistingUser');
        $this->getSession()->setVariable('usr', 'oxdefaultadmin');

        $order = oxNew(\OxidEsales\Eshop\Application\Controller\OrderController::class);
        $user = $order->getUser();

        $this->assertEquals('oxdefaultadmin', $user->oxuser__oxid->value);
    }

    /**
     * Test case for OrderController::getPayment()
     */
    public function testGetPayment()
    {
        $this->getSession()->setVariable("oepaypal", "0");

        $view = oxNew(\OxidEsales\Eshop\Application\Controller\OrderController::class);
        $payment = $view->getPayment();
        $this->assertFalse($payment);

        $this->getSession()->setVariable("paymentid", "oxidpaypal");

        $query = "INSERT INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`) VALUES ('oxidpaypal', 1, 'PayPal')";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

        $view = oxNew(\OxidEsales\Eshop\Application\Controller\OrderController::class);
        $payment = $view->getPayment();

        $this->assertNotNull($payment);
        $this->assertEquals("oxidpaypal", $payment->getId());
    }
}
