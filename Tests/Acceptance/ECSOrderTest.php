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

namespace OxidEsales\PayPalModule\Tests\Acceptance;

/**
 * This test class contains acceptance tests for the PayPal Module, which are not checking any of the PayPal GUI parts and
 * only OXID eShop GUI parts.
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class ECSOrderTest extends BaseAcceptanceTestCase
{
    /**
     * Test PayPal express checkout.
     * Case that the customer is not logged in to shop but logs in to PayPal.
     * Customer goes back to shop without paying on paypal side.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testECSOrderForNotLoggedInUser()
    {
        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user is not logged in
        $this->openShop();
        $this->addToBasketProceedToPayPal();
        $this->logIntoPayPal();

        $this->open(shopURL . '/index.php?cl=order');
        $this->assertTextPresent("%CART%");
        $this->assertElementNotPresent("//button[text()='Order now']");
    }

    /**
     * Test PayPal express checkout.
     * Case that the customer is logged in to shop but not to PayPal.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_nobuyerlogin
     */
    public function testECSOrderForLoggedInUserNotLoggedInToPP()
    {
        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user is logged in
        $this->openShop();
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->addToBasketProceedToPayPal();

        $this->open(shopURL . '/index.php?cl=order');
        $this->assertElementPresent("//button[text()='Order now']");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextNotPresent(self::THANK_YOU_PAGE_IDENTIFIER);
        $this->assertTextPresent('%MESSAGE_PAYMENT_AUTHORIZATION_FAILED%');
    }

    /**
     * Test PayPal express checkout.
     * Case that the customer is logged in to shop and to PayPal.
     * Customer goes back to shop without paying on paypal side.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testECSOrderForLoggedInUserLoggedInToPP()
    {
        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user is logged in
        $this->openShop();
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->addToBasketProceedToPayPal();
        $this->logIntoPayPal();

        $this->open(shopURL . '/index.php?cl=order');
        $this->assertElementPresent("//button[text()='Order now']");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextNotPresent(self::THANK_YOU_PAGE_IDENTIFIER);
        $this->assertTextPresent('%MESSAGE_PAYMENT_AUTHORIZATION_FAILED%');
    }

    /**
     * Test helper to search for product, ut to basket, click on PP ECS button.
     */
    protected function addToBasketProceedToPayPal()
    {
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);

        $this->click("id=minibasketIcon");
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("paypalExpressCheckoutButton", "PayPal express button not displayed in the cart");
        $this->clickAndWait("id=paypalExpressCheckoutButton");

        // Check what was communicated with PayPal
        $assertRequest = [
            'L_PAYMENTREQUEST_0_AMT0' => 81.00,
            'PAYMENTREQUEST_0_AMT' => 81.00,
            'L_PAYMENTREQUEST_0_QTY0' => 1,
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR'];
        $assertResponse = ['ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);

        //just in case ensure we are logged out of PayPal
        $this->doPayPalLogOut();
    }

    /**
     * Tets helper.
     * Log customer in to paypal but do not click pay.
     */
    protected function logIntoPayPal()
    {
        //Log in to paypal but do not click pay
        $this->expressCheckoutWillBeUsed();
        $loginMail = $this->getLoginDataByName('sBuyerLogin');
        $loginPassword = $this->getLoginDataByName('sBuyerPassword');
        $this->logMeIntoSandbox($loginMail, $loginPassword);
    }
}