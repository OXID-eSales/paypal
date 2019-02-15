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
 * This test class contains acceptance tests, which check the things, which work with the old PayPal GUI.
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class AcceptanceOldGuiTest extends BaseAcceptanceTestCase
{
    /**
     * testing paypal express button
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalExpressForLoggedInUser()
    {
        // Testing when user is logged in
        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");

        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("paypalExpressCheckoutButton");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("paypalExpressCheckoutButton", "PayPal express button not displayed in the cart");

        // Go to PayPal express
        $this->payWithPayPalExpressCheckout();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = [
            'PAYMENTREQUEST_0_AMT'          => '0.99',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'L_PAYMENTREQUEST_0_NAME0'      => 'Test product 1',
            'PAYMENTREQUEST_0_SHIPTONAME'   => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'PAYMENTREQUEST_0_SHIPTOSTREET' => "Musterstr. Äß\\'ü 1",
            'ACK'                           => 'Success'
        ];
        $this->assertLogData($assertRequest, $assertResponse);

        // User is on the 4th page
        $this->assertElementPresent("//button[text()='Zahlungspflichtig bestellen']");
        $this->assertEquals("Gesamtbetrag: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")));
        $this->assertEquals("Zahlungsart Ändern PayPal", $this->clearString($this->getText("orderPayment")));
        $this->assertTextPresent("E-Mail: " . self::LOGIN_USERNAME);
        $this->assertEquals("Versandart Ändern Test S&H set", $this->clearString($this->getText("orderShipping")));
        $this->clickAndWait("//button[text()='Zahlungspflichtig bestellen']");
        $this->assertTextPresent("Vielen Dank für Ihre Bestellung im OXID eShop", "Order is not finished successful");

        // Checking if order is saved in Admin
        $this->loginAdminForModule("Administer Orders", "Orders");
        $this->openListItem("2");

        $this->openTab("Main");
        $this->assertEquals("Test S&H set", $this->getSelectedLabel("setDelSet"));
    }

    /**
     * testing paypal express button
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalExpressForNotLoggedInUser()
    {
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user is not logged in
        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");

        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("paypalExpressCheckoutButton", "PayPal express button not displayed in the cart");

        // Go to PayPal express
        $this->payWithPayPalExpressCheckout();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = [
            'PAYMENTREQUEST_0_AMT'          => '81.0',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'L_PAYMENTREQUEST_0_NAME0'      => 'Test product 1',
            'ACK'                           => 'Success'
        ];
        $this->assertLogData($assertRequest, $assertResponse);

        // User is on the 4th page
        $this->assertElementPresent("//button[text()='Zahlungspflichtig bestellen']");
        $this->assertEquals("Gesamtbetrag: 81,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")));
        $this->assertEquals("Zahlungsart Ändern PayPal", $this->clearString($this->getText("orderPayment")));
        $this->assertTextPresent("E-Mail: " . $this->getLoginDataByName('sBuyerLogin'));
        $this->assertEquals("Versandart Ändern Standard", $this->clearString($this->getText("orderShipping")));
        $this->clickAndWait("//button[text()='Zahlungspflichtig bestellen']");
        $this->assertTextPresent("Vielen Dank für Ihre Bestellung im OXID eShop", "Order is not finished successful");
    }

    /**
     * test if option "Calculate default Shipping costs when User is not logged in yet" is working correct in PayPal
     *
     * NOTE: User is not logged in to shop yet and no delivery set id stored in shop session at this point.
     * Means if option blCalculateDelCostIfNotLoggedIn is set in shop, pre-calculated delivery costs will be sent to PP.
     * By default the shop takes the oxidstandard delivery set (3.90 EUR but no PayPal assigned)..
     * Usually PP would use the callback to check for default PayPal delivery set (testdelset, 130 EUR) and send this
     * info back in GetExpressCheckoutDetails in field SHIPPINGOPTIONNAME. Atm PP sandbox does no give us this
     * information for causes unknown. So or now we assign PayPal to oxidstandard.
     * We will restore the test back to it's former behaviour at some later point.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalShippingCostNotLoginUser()
    {
        // Change price for PayPal payment method
        $this->importSql(__DIR__ . '/testSql/vatOptions.sql');

        // Go to admin and set on "Calculate default Shipping costs when User is not logged in yet "
        $this->loginAdminForModule("Master Settings", "Core Settings");
        $this->openTab("Settings");
        $this->click("link=Other settings");
        sleep(1);
        $this->check("//input[@name='confbools[blCalculateDelCostIfNotLoggedIn]'and @value='true']");
        $this->clickAndWait("save");

        // Go to shop and add product
        $this->clearCache();
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("1003");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");
        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Test product 3", $this->getText("//tr[@id='cartItem_1']/td[3]/div[1]"));

        // Added wrapping and card to basket
        $this->click("id=header");
        $this->click("link=add");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Apply']");

        $this->assertEquals("Total products (excl. tax): 12,61 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("plus 19% tax, amount: 2,39 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("Total products (incl. tax): 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("Shipping costs: 13,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Shipping costs is not displayed correctly");
        $this->assertEquals("2,95 €", $this->getText("basketWrappingGross"), "Wrapping price changed or didn't displayed");
        $this->assertEquals("33,95 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");

        // Go to PayPal express
        $this->payWithPayPalExpressCheckout('paypalExpressCheckoutButton');

        //Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'AMT' => '44.45',
            'ITEMAMT' => '31.45',
            'L_NAME0' => 'Test product 3',
            'L_NAME1' => 'Surcharge Type of Payment',
            'L_NAME2' => 'Giftwrapper',
            'L_NAME3' => 'Greeting Card',
            'L_NUMBER0' => '1003',
            'L_QTY0' => '1',
            'L_QTY1' => '1',
            'L_QTY2' => '1',
            'L_QTY3' => '1',
            'L_AMT0' => '15.00',
            'L_AMT1' => '10.50',
            'L_AMT2' => '2.95',
            'L_AMT3' => '3.00'];
        $this->assertLogData($assertRequest, $assertResponse);

        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Item #: 1003", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Greeting card");
        $this->assertEquals("3,00 €", $this->getText("id=orderCardTotalPrice"));

        $this->assertEquals("Total products (excl. tax): 12,61 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("plus 19% tax, amount: 2,39 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("Total products (incl. tax): 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("Shipping costs: 13,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Shipping costs is not displayed correctly");
        $this->assertEquals("Surcharge Payment method: 10,50 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")));
        $this->assertEquals("2,95 €", $this->getText("basketWrappingGross"), "Wrapping price changed or didn't displayed");
        $this->assertEquals("44,45 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");

        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->assertCaseInsensitiveText($this->getLoginDataByName('sBuyerFirstName'), $this->getText("//tr[@id='row.1']/td[contains(@class, 'first_name')][1]"));
        $this->assertCaseInsensitiveText($this->getLoginDataByName('sBuyerLastName'), $this->getText("//tr[@id='row.1']/td[contains(@class, 'last_name')][1]"));
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertEquals("15,00 EUR", $this->getText("//td[5]"));
        $this->assertCaseInsensitiveText("Billing Address: {$this->getLoginDataByName('sBuyerFirstName')} {$this->getLoginDataByName('sBuyerLastName')} ESpachstr. 1 79111 Freiburg Germany E-mail: {$this->getLoginDataByName('sBuyerLogin')}", $this->clearString($this->getText("//td[1]/table[1]/tbody/tr/td[1]")));
        $this->assertEquals("15,00", $this->getText("//table[@id='order.info']/tbody/tr[1]/td[2]"));
        $this->assertEquals("- 0,00", $this->getText("//table[@id='order.info']/tbody/tr[2]/td[2]"));
        $this->assertEquals("12,61", $this->getText("//table[@id='order.info']/tbody/tr[3]/td[2]"));
        $this->assertEquals("2,39", $this->getText("//table[@id='order.info']/tbody/tr[4]/td[2]"));
        $this->assertEquals("13,00", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("10,50", $this->getText("//table[@id='order.info']/tbody/tr[6]/td[2]"));
        $this->assertEquals("2,95", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));
        $this->assertEquals("3,00", $this->getText("//table[@id='order.info']/tbody/tr[8]/td[2]"));
        $this->assertEquals("44,45", $this->getText("//table[@id='order.info']/tbody/tr[9]/td[2]"));

        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[1]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[2]", "line with discount info is not displayed");
        $this->assertEquals("PayPal", $this->getText("//table[4]/tbody/tr[1]/td[2]"), "Payment method not displayed in admin");
        $this->assertEquals("Test S&H set", $this->getText("//table[4]/tbody/tr[2]/td[2]"), "Shipping method is not displayed in admin");
    }

    /**
     * test if PayPal works correct when last product ir purchased.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalStockOneSale()
    {
        $this->importSql(__DIR__ . '/testSql/changeStock.sql');

        $this->openShop();
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        // Login to shop and go to the basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed");
        $this->assertElementPresent("//tr[@id='cartItem_1']/td[3]/div[2]");
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("Shipping costs:", "Shipping costs is not displayed correctly");
        $this->assertTextPresent("?");
        $this->assertTrue($this->isChecked("//input[@name='displayCartInPayPal' and @value='1']"));
        $this->assertTextPresent("Display cart in PayPal", "Text:Display cart in PayPal for checkbox not displayed");
        $this->assertElementPresent("displayCartInPayPal", "Checkbox:Display cart in PayPal not displayed");

        // Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->payWithPayPalExpressCheckout();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'PAYMENTREQUEST_0_SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'AMT' => '0.99',
            'ITEMAMT' => '0.99',
            'SHIPPINGAMT' => '0.00',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 1',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1001',
            'L_PAYMENTREQUEST_0_QTY0' => '1',
            'L_PAYMENTREQUEST_0_TAXAMT0' => '0.00',
            'L_PAYMENTREQUEST_0_AMT0' => '0.99'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Check are all info in the last order step correct
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed in last order step");
        $this->assertTextPresent("Item #: 1001", "Product number not displayed in last order step");
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Shipping costs is not displayed correctly");
        // $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle() );
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("PayPal", "Payment method not displayed in last order step");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
    }

    /**
     * test if PayPal works correct when last product is purchased.
     * In transaction mode 'automatic' transaction mode 'authorization' is used when stock level drops below specified value.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalStockOneAutomatic()
    {
        $this->importSql(__DIR__ . '/testSql/changeStock.sql');

        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalTransactionMode' => [
                'type' => 'select',
                'value' => 'Automatic',
                'module' => 'module:oepaypal'
            ]]);

        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalEmptyStockLevel' => [
                'type' => 'select',
                'value' => '10',
                'module' => 'module:oepaypal'
            ]]);

        $this->openShop();
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        // Login to shop and go to the basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed");
        $this->assertElementPresent("//tr[@id='cartItem_1']/td[3]/div[2]");
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("Shipping costs:", "Shipping costs is not displayed correctly");
        $this->assertTextPresent("?");
        $this->assertTrue($this->isChecked("//input[@name='displayCartInPayPal' and @value='1']"));
        $this->assertTextPresent("Display cart in PayPal", "Text:Display cart in PayPal for checkbox not displayed");
        $this->assertElementPresent("displayCartInPayPal", "Checkbox:Display cart in PayPal not displayed");

        // Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->payWithPayPalExpressCheckout();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'PAYMENTREQUEST_0_SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'AMT' => '0.99',
            'ITEMAMT' => '0.99',
            'SHIPPINGAMT' => '0.00',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 1',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1001',
            'L_PAYMENTREQUEST_0_QTY0' => '1',
            'L_PAYMENTREQUEST_0_TAXAMT0' => '0.00',
            'L_PAYMENTREQUEST_0_AMT0' => '0.99'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Check are all info in the last order step correct
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed in last order step");
        $this->assertTextPresent("Item #: 1001", "Product number not displayed in last order step");
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Shipping costs is not displayed correctly");
        // $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle() );
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("PayPal", "Payment method not displayed in last order step");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: NOT_FINISHED"); //means capture has to be done manually with these settings
    }

    /**
     * Test if PayPal works correct when last product is purchased.
     * In transaction mode 'automatic' transaction mode 'authorization' is used when stock level drops below specified value.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalStockSufficientAutomatic()
    {
        $this->importSql(__DIR__ . '/testSql/changeStockTo100.sql');

        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalTransactionMode' => [
                'type' => 'select',
                'value' => 'Automatic',
                'module' => 'module:oepaypal'
            ]]);

        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalEmptyStockLevel' => [
                'type' => 'select',
                'value' => '1',
                'module' => 'module:oepaypal'
            ]]);

        $this->openShop();
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        // Login to shop and go to the basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed");
        $this->assertElementPresent("//tr[@id='cartItem_1']/td[3]/div[2]");
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("Shipping costs:", "Shipping costs is not displayed correctly");
        $this->assertTextPresent("?");
        $this->assertTrue($this->isChecked("//input[@name='displayCartInPayPal' and @value='1']"));
        $this->assertTextPresent("Display cart in PayPal", "Text:Display cart in PayPal for checkbox not displayed");
        $this->assertElementPresent("displayCartInPayPal", "Checkbox:Display cart in PayPal not displayed");

        // Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->payWithPayPalExpressCheckout();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'PAYMENTREQUEST_0_SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'AMT' => '0.99',
            'ITEMAMT' => '0.99',
            'SHIPPINGAMT' => '0.00',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 1',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1001',
            'L_PAYMENTREQUEST_0_QTY0' => '1',
            'L_PAYMENTREQUEST_0_TAXAMT0' => '0.00',
            'L_PAYMENTREQUEST_0_AMT0' => '0.99'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Check are all info in the last order step correct
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed in last order step");
        $this->assertTextPresent("Item #: 1001", "Product number not displayed in last order step");
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Shipping costs is not displayed correctly");
        // $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle() );
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("PayPal", "Payment method not displayed in last order step");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
    }

    /**
     * test if PayPal works in Netto mode
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalExpressNettoMode()
    {
        // Activate the necessary options Neto mode
        $this->importSql(__DIR__ . '/testSql/NettoModeTurnOn_' . SHOP_EDITION . '.sql');

        // Add articles to basket.
        $this->openShop();
        $this->searchFor("1401");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);

        // Change price for PayPal payment method
        $this->importSql(__DIR__ . '/testSql/vatOptions.sql');

        $this->openBasket("English");

        //Added wrapping and card to basket.
        $this->click("id=header");
        $this->click("link=add");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Apply']");

        // Check wrapping and card prices.
        $this->assertEquals("2,95 €", $this->getText("basketWrappingGross"), "Wrapping price changed or didn't display");
        $this->assertEquals("3,00 €", $this->getText("basketGiftCardGross"), "Card price changed or didn't display");

        // Check basket prices.
        $this->assertEquals("108,40 €", $this->getText("basketTotalProductsNetto"), "Net price changed or didn't display");
        $this->assertEquals("134,95 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't display");

        // Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->assertElementPresent("paypalExpressCheckoutButton");
        $this->selectPayPalExpressCheckout();

        // Check what was communicated with PayPal
        $assertRequest = ['PAYMENTREQUEST_0_AMT' => '145.45',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'PAYMENTREQUEST_0_ITEMAMT' => '122.22',
            'L_PAYMENTREQUEST_0_NAME0' => 'Harness SOL KITE',
            'L_PAYMENTREQUEST_0_AMT0' => '108.40',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1401',
            'L_PAYMENTREQUEST_0_AMT1' => '8.82',
            'L_PAYMENTREQUEST_0_AMT2' => '2.48',
            'L_PAYMENTREQUEST_0_AMT3' => '2.52'];
        $assertResponse = ['ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);

        $this->loginToSandbox();
        $this->clickPayPalContinue();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'L_PAYMENTREQUEST_0_NAME0' => 'Harness SOL KITE',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'PAYMENTREQUEST_0_AMT' => '158.45',
            'PAYMENTREQUEST_0_ITEMAMT' => '122.22',
            'PAYMENTREQUEST_0_SHIPPINGAMT' => '13.00',
            'PAYMENTREQUEST_0_TAXAMT' => '23.23'];
        $this->assertLogData($assertRequest, $assertResponse);

        $this->waitForText("Please check all data on this overview before submitting your order!");
    }

    /**
     * Verify that we can log in to PayPal Sandbox, cancel and return to shop and then
     * can log in to Sandbox again.
     *
     * @group paypal_buyerlogin
     */
    public function testPayPalLoginCancelLoginPay()
    {
        $loginMail = $this->getLoginDataByName('sBuyerLogin');
        $loginPassword = $this->getLoginDataByName('sBuyerPassword');

        $this->addToBasket('1001');
        $this->openBasket();
        $this->clickAndWait('paypalExpressCheckoutButton');
        $this->logMeIntoSandbox($loginMail, $loginPassword);
        $this->cancelPayPal(); //cancel logs us out of PayPal
        $this->clickAndWait('paypalExpressCheckoutButton'); //we end up in the new PayPal GUI now
        $this->logMeIntoSandbox($loginMail, $loginPassword);
        $this->clickPayPalContinue();
    }

    /**
     * testing when payment method has unassigned country Germany, user is not login to the shop, and purchase as PayPal user from Germany
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalPaymentForGermany()
    {
        // Separate Germany from PayPal payment method and assign United States
        $this->importSql(__DIR__ . '/testSql/unasignCountryFromPayPal.sql');

        // Go to make an order but do not finish it
        $this->clearCache();
        $this->openShop();

        // Check if PayPal logo in frontend is active in both languages
        $this->assertElementPresent("paypalPartnerLogo", "PayPal logo not shown in frontend page");
        $this->switchLanguage("Deutsch");
        $this->assertElementPresent("paypalPartnerLogo", "PayPal logo not shown in frontend page");
        $this->switchLanguage("English");

        // Search for the product and add to cart
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("link=Test product 1", "Product:Test product 1 is not shown in 1st order step ");
        $this->assertElementPresent("//tr[@id='cartItem_1']/td[3]/div[2]", "There product:Test product 1 is not shown in 1st order step");
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Grand Total is not displayed correctly");
        $this->assertFalse($this->isTextPresent("Shipping costs:"), "Shipping costs should not be displayed");
        $this->assertTextPresent("?");
        $this->assertTrue($this->isChecked("//input[@name='displayCartInPayPal' and @value='1']"));
        $this->assertTextPresent("Display cart in PayPal", "An option text:Display cart in PayPal is not displayed");
        $this->assertElementPresent("name=displayCartInPayPal", "An option Display cart in PayPal is not displayed");

        // Go to PayPal express to make an order
        $loginMail = $this->getLoginDataByName('sBuyerUSLogin');
        $loginPassword = $this->getLoginDataByName('sBuyerPassword');
        $this->clickAndWait('paypalExpressCheckoutButton');
        $this->doPayPalLogOut();
        $this->cancelPayPal();
        $this->clickAndWait('paypalExpressCheckoutButton');
        $this->logMeIntoSandbox($loginMail, $loginPassword);
        $this->clickPayPalContinue();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['PAYMENTREQUEST_0_AMT' => '7.89',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 1',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1001',
            'L_PAYMENTREQUEST_0_QTY0' => '1',
            'L_PAYMENTREQUEST_0_AMT0' => '0.99',
            'EMAIL' => $this->getLoginDataByName('sBuyerUSLogin'),
            'AMT' => '7.89',
            'ITEMAMT' => '0.99',
            'SHIPPINGAMT' => '6.90',
            'SHIPPINGCALCULATIONMODE' => 'Callback',
            'ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Now user is on the 1st "cart" step with an error message:
        $this->assertTextPresent("Based on your choice in PayPal Express Checkout, order total has changed. Please check your shopping cart and continue. Hint: for continuing with Express Checkout press Express Checkout button again.", "An error message is not dispayed in shop 1st order step");
        $this->assertElementPresent("id=basketRemoveAll", "an option Remove is not displayed in 1st cart step");
        $this->assertElementPresent("id=basketRemove", "an option All is not displayed in 1st cart step");
        $this->assertElementPresent("id=basketUpdate", "an option Update is not displayed in 1st cart step");
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed");
        $this->assertElementPresent("//tr[@id='cartItem_1']/td[3]/div[2]", "There product:Test product 1 is not shown in 1st order step");
        $this->assertEquals("Grand total: 7,73 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertEquals("Shipping costs: 6,90 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Shipping costs is not displayed correctly");

        $this->assertTextPresent("Display cart in PayPal", "Text:Display cart in PayPal for checkbox not displayed");
        $this->assertElementPresent("name=displayCartInPayPal", "Checkbox:Display cart in PayPal not displayed in cart");
        $this->assertElementPresent("paypalExpressCheckoutButton", "PayPal express button not displayed in the cart");

        // Go to next step and change country to Germany
        $this->clickAndWait("css=.nextStep");
        $this->click("//button[@id='userChangeAddress']");
        $this->click("id=invCountrySelect");
        $this->select("invCountrySelect", "label=Germany");
        $this->click("id=userNextStepTop");
        $this->waitForPageToLoad("30000");

        // Check if PayPal is not displayed for Germany
        $this->assertElementNotPresent("//select[@name='sShipSet']/option[text()='Paypal']", "Paypal is displayed for Germany, but must be not shown");

        $this->assertEquals("COD (Cash on Delivery) (7,50 €)", $this->getText("//form[@id='payment']/dl[5]/dt/label/b"), "Wrong payment method is shown");
        $this->assertTextPresent("COD (Cash on Delivery) (7,50 €)", "Wrong payment method is shown");
        $this->assertFalse($this->isTextPresent("PayPal (0,00 €)"), "PayPal should not be displayed as payment method");

        // Also check if PayPal not displayed in the 1st cart step
        $this->click("link=1. Cart");
        $this->waitForPageToLoad("30000");
        $this->assertTextPresent("Display cart in PayPal", "Text:Display cart in PayPal for checkbox not displayed");
        $this->assertElementPresent("displayCartInPayPal", "Checkbox:Display cart in PayPal not displayed in cart");
        $this->assertElementPresent("paypalExpressCheckoutButton", "PayPal express button not displayed in the cart");

        // Go to admin and check previous order status and check if new order didn't appear in admin.
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->selectMenu("Administer Orders", "Orders");
        $this->assertElementNotPresent("link=2");

        // Go to basket and make an order,
        $this->clearCache();
        $this->openShop();
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Grand total is not displayed correctly");
        $this->clickAndWait("//button[text()='Continue to the next step']");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertElementPresent("id=showShipAddress", "Shipping address is not displayed in 2nd order step");
        $this->click("id=userNextStepBottom");
        $this->waitForElement("paymentNextStepBottom");
        $this->assertElementPresent("name=sShipSet", "Shipping method drop down is not shown");
        $this->assertEquals("Test S&H set", $this->getSelectedLabel("sShipSet"), "Wrong shipping method is selected, should be:Test S&H set ");
        $this->click("id=paymentNextStepBottom");

        // go to last order step, check if payment method is not PayPal
        $this->waitForElement("orderAddress");
        $this->assertElementPresent("link=Test product 1", "Product name is not displayed in last order step");
        $this->assertTextPresent("Item #: 1001", "Product number not displayed in last order step");
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), "Shipping costs is not displayed correctly");
        $this->assertEquals("Surcharge Payment method: 7,50 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Payment price is not displayed in carts");
        $this->assertEquals("Grand total: 8,49 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("Test S&H set");
        $this->assertTextPresent("COD");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // After successful purchase, go to admin and check order status
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->click("link=Order No.");
        $this->waitForPageToLoad("30000");

        $this->clickandWait("link=2");
        $this->assureAdminOrderNameIsPresent();
        $this->assertEquals("0000-00-00 00:00:00", $this->getText("//tr[@id='row.1']/td[contains(@class, 'payment_date')][1]"));
        $this->openListItem("2", "setfolder");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertTextPresent("Order No.: 2", "Order number is not displayed in admin");
        $this->assertEquals("1 *", $this->getText("//table[2]/tbody/tr/td[1]"));
        $this->assertEquals("Test product 1", $this->getText("//td[3]"), "Purchased product name is not displayed in Admin");
        $this->assertEquals("8,49", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));

        $this->openTab("Products");
        $this->assertEquals("7,50", $this->getText("//table[@id='order.info']/tbody/tr[6]/td[2]"), "charges of payment method is not displayed");
        $this->assertEquals("0,16", $this->getText("//table[@id='order.info']/tbody/tr[4]/td[2]"), "VAT is not displayed");
        $this->assertEquals("0,83", $this->getText("//table[@id='order.info']/tbody/tr[3]/td[2]"), "Product Net price is not displayed");

        $this->openTab("Main");
        $this->assertEquals("Test S&H set", $this->getSelectedLabel("setDelSet"), "Shipping method is not displayed in admin");
        $this->assertEquals("COD (Cash on Delivery)", $this->getSelectedLabel("setPayment"), "Payment method is not displayed in admin");

    }

    /**
     * Testing different countries with shipping rules assigned to this countries
     * NOTE: test selects payment method on PayPal page.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalPaymentForLoginUser()
    {
        $this->addToBasket('1001');
        $this->loginToShopFrontend();

        // Created additional 3 shipping methods with Shipping costs rules for Austria
        $this->importSql(__DIR__ . '/testSql/newDeliveryMethod_' . SHOP_EDITION . '.sql');

        $this->openBasket();
        $this->clickNextStepInShopBasket();

        // Change country to Austria
        $this->changeCountryInBasketStepTwo('Austria');

        // Check all available shipping methods
        $this->assertTextPresent('PayPal');
        // Test Paypal:6 hour Price: €0.50 EUR
        $this->selectAndWait('sShipSet', 'label=Test Paypal:6 hour');

        $this->assertTextPresent('Charges: 0,50 €');
        $this->assertAllAvailableShippingMethodsAreDisplayed();

        // Go to 1st step and make an order via PayPal express
        $this->clickFirstStepInShopBasket();
        $this->selectPayPalExpressCheckout();

        $this->loginToSandbox();

        // NOTE: isn't running locally (callback is not accessible from PayPal):
        //NOTE: PayPal GUI changed for selecting shipping methods
        //$this->selectPayPalShippingMethod('Test Paypal:12 hour Price: €0,90 EUR');

        // Check, that the communication with PayPal was as expected
        $expectedRequest = ['METHOD' => 'SetExpressCheckout',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'NOSHIPPING' => '2',
            'PAYMENTREQUEST_0_AMT' => '1.49',
            'PAYMENTREQUEST_0_ITEMAMT' => '0.99',
            'PAYMENTREQUEST_0_SHIPPINGAMT' => '0.50',
            'PAYMENTREQUEST_0_SHIPDISCAMT' => '0.00',
            'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
            'L_SHIPPINGOPTIONNAME0' => 'Test Paypal:6 hour',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'AT',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 1',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1001'
        ];
        $expectedResponse = ['ACK' => 'Success'];
        $this->assertLogData($expectedRequest, $expectedResponse);

        // Go to shop
        $this->expressCheckoutWillBeUsed();
        $this->clickPayPalContinue();

        // Make sure we are back in shop
        $this->assertTrue($this->isElementPresent("id=breadCrumb"));

        // Check are all info in the last order step correct
        $this->assertElementPresent('link=Test product 1', 'Purchased product name is not displayed in last order step');
        $this->assertTextPresent('Item #: 1001', 'Product number not displayed in last order step');
        $this->assertTextPresent('PayPal', 'Payment method not displayed in last order step');
        $this->assertFalse($this->isTextPresent('COD'), 'Wrong payment method displayed in last order step');
        $this->assertEquals('OXID Surf and Kite Shop | Order | purchase online', $this->getTitle());

        $this->assertEquals('Shipping costs: 0,50 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), 'Shipping costs is not displayed correctly');
        $this->assertEquals('Grand total: 1,49 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), 'Grand total is not displayed correctly');
        $this->assertTextPresent('Test Paypal:6 hour', 'Shipping method not displayed in order ');
    }

    /**
     * Testing paypal express button.
     * When user is not logged in he should be able to accces PP express checkout
     * from second checkout step (cl=user).
     * Ensure that in case of clicking 'cancel' we end up on the page we started from.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testNoPayPalExpressInUserStepForLoggedInUser()
    {
        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->assertTextNotPresent(self::translate( "%PURCHASE_WITHOUT_REGISTRATION%"));
        $this->assertElementNotPresent("paypalExpressCheckoutButtonECS", "PayPal ECS button must not be displayd in user step for logged in user.");
    }

    /**
     * Testing paypal express button.
     * When user is not logged in he should be able to accces PP express checkout
     * from second checkout step (cl=user).
     * Ensure that in case of clicking 'cancel' we end up on the page we started from.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalExpressInUserStepForNotLoggedInUserCancel()
    {
        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->assertTextPresent(self::translate( "%PURCHASE_WITHOUT_REGISTRATION%"));

        $this->waitForElement("paypalExpressCheckoutButtonECS");
        $this->assertElementPresent("paypalExpressCheckoutButtonECS", "PayPal ECS button must be displayd in user step for not logged in user.");
        $this->clickAndWait("paypalExpressCheckoutButtonECS");
        $this->cancelPayPal();

        $this->assertTextPresent(self::translate( "%PURCHASE_WITHOUT_REGISTRATION%"));
    }

    /**
     * Testing paypal express button.
     * When user is not logged in he should be able to accces PP express checkout
     * from second checkout step (cl=user).
     * Ensure that in case of error redirecting to PayPal we end up on the page we started from.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalExpressInUserStepForNotLoggedInUserError()
    {
        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalSandboxSignature' => [
                'type' => 'str',
                'value' => 'this_is_invalid',
                'module' => 'module:oepaypal'
            ],
        ]);

        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->assertTextPresent(self::translate("%PURCHASE_WITHOUT_REGISTRATION%"));

        $this->waitForElement("paypalExpressCheckoutButtonECS");
        $this->assertElementPresent("paypalExpressCheckoutButtonECS", "PayPal ECS button must be displayed in user step for not logged in user.");
        $this->clickAndWait("paypalExpressCheckoutButtonECS");

        $this->assertTextPresent(self::translate("%OEPAYPAL_RESPONSE_FROM_PAYPAL%"));
        $this->assertTextPresent(self::translate("%PURCHASE_WITHOUT_REGISTRATION%"));
    }

    /**
     * Testing paypal express button.
     * When user is not logged in he should be able to accces PP express checkout
     * from second checkout step (cl=user).
     * Ensure that in case of clicking 'cancel' we end up on the page we started from.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalExpressInUserStepForNotLoggedInUserCannotPayWithPP()
    {
        //NOTE: test runs locally when callback is not available.
        // On publicly available shop, we see the following message on PayPal side:
        // 'PayPal Testshop versendet nicht an diesen Ort. Verwenden Sie eine andere Adresse.'
        // and we have no possibility to continue woth checkout on PP side.
        $this->markTestSkipped('Use this only manually for not publicly available shop for now.');

        //Separate Germany from PayPal payment method and assign United States
        $this->importSql(__DIR__ . '/testSql/unasignCountryFromPayPal.sql');

        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->assertTextPresent(self::translate( "%PURCHASE_WITHOUT_REGISTRATION%"));

        $this->waitForElement("paypalExpressCheckoutButtonECS");
        $this->assertElementPresent("paypalExpressCheckoutButtonECS", "PayPal ECS button must be displayed in user step for not logged in user.");
        $this->payWithPayPalExpressCheckout("paypalExpressCheckoutButtonECS");

        $this->assertTextPresent(self::translate( "%MESSAGE_PAYMENT_SELECT_ANOTHER_PAYMENT%"));
        $this->assertTextPresent(self::translate( "%PAY%"));
    }

    /**
     * Testing paypal express button.
     * When user is not logged in he should be able to accces PP express checkout
     * from second checkout step (cl=user).
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayWithPayPalExpressInUserStepForNotLoggedInUserOk()
    {
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        //Testing when user is NOT logged in
        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->assertTextPresent(self::translate( "%PURCHASE_WITHOUT_REGISTRATION%"));

        $this->waitForElement("paypalExpressCheckoutButtonECS");
        $this->assertElementPresent("paypalExpressCheckoutButtonECS", "PayPal ECS button must be displayd in user step for not logged in user.");
        $this->payWithPayPalExpressCheckout("paypalExpressCheckoutButtonECS");

        //Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['L_PAYMENTREQUEST_0_NAME0' => 'Test product 1',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'L_PAYMENTREQUEST_0_QTY0' => '1',
            'ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);

        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed in last order step");
        $this->assertTextPresent("Art. Nr.: 1001", "Product number not displayed in last order step");
        $this->assertEquals("81,00 €", $this->getText("basketGrandTotal"), "Grand total price changed  or didn't displayed");
        $this->assertTextPresent("PayPal", "Payment method not displayed in last order step");
        $this->clickAndWait("//button[text()='". self::translate("%SUBMIT_ORDER%") . "']");
        $this->assertTextPresent(self::translate("%THANK_YOU%"), "Order is not finished successful");

        $assertRequest = ['METHOD' => 'DoExpressCheckoutPayment',
                          'BUTTONSOURCE' => 'Oxid_Cart_ECS_Shortcut',
                          'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale'];
        $assertResponse = ['ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);
    }

    /**
     * testing PayPal ECS in detail page and ECS in mini basket
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testECS()
    {
        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Open shop and add product to the basket
        $this->openShop();
        $this->searchFor("1001");
        $this->clickAndWait("//ul[@id='searchList']/li/form/div/a[2]/span");
        $this->clickAndWait("id=toBasket");

        // Open mini basket
        $this->click("id=minibasketIcon");
        $this->assertElementPresent("//div[@id='paypalExpressCheckoutDetailsBox']/div/a", "No express PayPal button in mini cart");
        $this->assertElementPresent("id=paypalExpressCheckoutDetailsButton", "No express PayPal button in mini cart");
        $this->assertElementPresent("displayCartInPayPal", "No express PayPal checkbox for displaying cart in PayPal in mini cart");
        $this->assertTextPresent("Display cart in PayPal", "No express PayPal text about displaying cart in PayPal in mini cart");
        $this->assertElementPresent("id=paypalExpressCheckoutMiniBasketImage", "No express PayPal image in mini cart");
        $this->assertElementPresent("id=paypalHelpIconMiniBasket", "No express PayPal checkbox help button for displaying cart in PayPal in mini cart");

        // Open ECS in details page
        $this->clickAndWait("id=paypalExpressCheckoutDetailsButton");
        $this->assertElementPresent("//div[@id='popupECS']/p", "No Express PayPal popup appears");
        $this->assertElementPresent("id=actionNotAddToBasketAndGoToCheckout", "No button in PayPal popup");
        $this->assertElementPresent("id=actionAddToBasketAndGoToCheckout", "No button in PayPal popup");
        $this->assertElementPresent("link=open current cart", "No link open current cart in popup");
        $this->assertElementPresent("//div[@id='popupECS']/div/div/button", "No cancel button in PayPal popup");

        // Select add to basket and go to checkout
        $this->selectPayPalExpressCheckout("id=actionAddToBasketAndGoToCheckout");

        // Check what was communicated with PayPal
        $assertRequest = [
            'L_PAYMENTREQUEST_0_AMT0' => 81.00,
            'PAYMENTREQUEST_0_AMT' => 162.00,
            'L_PAYMENTREQUEST_0_QTY0' => 2,
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR'];
        $assertResponse = ['ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Cancel order
        $this->cancelPayPal();
        // Go to checkout with PayPal  with same amount in basket
        $this->clickAndWait("id=paypalExpressCheckoutDetailsButton");
        $this->clickAndWait("id=actionNotAddToBasketAndGoToCheckout");
        // Check what was communicated with PayPal
        $this->assertLogData($assertRequest, $assertResponse);

        // Cancel order
        $this->cancelPayPal();

        // Go to home page and purchase via PayPal
        $this->assertTextPresent("2 x Test product 1", "Item quantity doesn't mach ot didn't displayed");
        $this->assertTextPresent("162,00 €", "Item price doesn't mach ot didn't displayed");
        $this->assertElementPresent("id=paypalHelpIconMiniBasket");
        $this->assertElementPresent("id=paypalExpressCheckoutMiniBasketBox");
        $this->assertElementPresent("displayCartInPayPal");
        $this->clickAndWait("id=paypalExpressCheckoutMiniBasketImage");
        $this->assertLogData($assertRequest, $assertResponse);

        $this->payWithPayPal();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = [
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 1',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'L_PAYMENTREQUEST_0_QTY0' => '2',
            'ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);

        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed in last order step");
        $this->assertTextPresent("Item #: 1001", "Product number not displayed in last order step");
        $this->assertEquals("162,00 €", $this->getText("basketGrandTotal"), "Grand total price changed  or didn't displayed");
        $this->assertTextPresent("PayPal", "Payment method not displayed in last order step");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");
    }

    /**
     * Test helper
     */
    protected function assertCaseInsensitiveText($expected, $actual)
    {
        $this->assertEquals(strtolower($expected), strtolower($actual));
    }
}
