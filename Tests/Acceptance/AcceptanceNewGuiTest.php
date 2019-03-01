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
 * This test class contains acceptance tests, which check the things, which work with the new PayPal GUI.
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class AcceptanceNewGuiTest extends BaseAcceptanceTestCase
{
    /**
     * testing PayPal payment selection
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalRegularCheckoutPayment()
    {
        // Set transaction mode to Authorization because we want to capture manually via shop admin
        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalTransactionMode' => [
                'type' => 'select',
                'value' => 'Authorization',
                'module' => 'module:oepaypal'
            ]
        ]);

        $this->moveTemplateBlockToEnd();

        $this->addToBasket('1001');
        $this->switchLanguage("Deutsch");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);

        // advance to next step (choose address/Adresse wählen)
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->click("userChangeAddress");
        // add remark/comment
        $this->waitForItemAppear("order_remark");
        $this->type("order_remark", "Testing paypal");

        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->selectPaymentPayPal();

        // go to PayPal page
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->checkForFailedToOpenPayPalPageError();
        $this->standardCheckoutWillBeUsed();
        $this->payWithPayPal();

        // returned to basket step 4 (verify)
        $this->assertElementPresent("//button[text()='Zahlungspflichtig bestellen']");
        $this->assertEquals("0,99 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");
        $this->assertEquals("Zahlungsart Ändern PayPal", $this->clearString($this->getText("orderPayment")));
        $this->assertEquals("Versandart Ändern Test S&H set", $this->clearString($this->getText("orderShipping")));
        $this->assertEquals("Adressen Ändern Rechnungsadresse E-Mail: testing_account@oxid-esales.dev SeleniumTestCase Äß'ü Testing acc for Selenium Herr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Deutschland Ihre Mitteilung an uns Testing paypal", $this->clearString($this->getText("orderAddress")));
        $this->clickAndWait("//button[text()='Zahlungspflichtig bestellen']", 90);
        $this->assertTextPresent("Vielen Dank für Ihre Bestellung im OXID eShop", "Order is not finished successful");

        $assertRequest = ['METHOD' => 'DoExpressCheckoutPayment',
                          'BUTTONSOURCE' => $this->getButtonSource(),
                          'PAYMENTREQUEST_0_PAYMENTACTION' => 'Authorization'];
        $assertResponse = ['ACK' => 'Success'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Admin
        // Checking if order is saved in Admin
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->assureAdminOrderNameIsPresent();
        $this->openListItem("2");

        // Go to PayPal tab to check all order info
        $this->frame("list");
        $this->clickAndWaitFrame("//a[contains(@href, '#oepaypalorder_paypal')]", 'edit');
        $this->frame("edit");
        $this->assertTextPresent("Shop payment status:", "record 'Shop payment status:' is not displayed in admin PayPal tab");
        $this->assertTextPresent("Full order price:", "record 'Full order price:': is not displayed in admin PayPal tab");
        $this->assertTextPresent("Captured amount:", "record 'Captured amount:': is not displayed in admin PayPal tab");
        $this->assertTextPresent("Refunded amount:", "Refunded amount:': is not displayed in admin PayPal tab");
        $this->assertTextPresent("Resulting payment amount:", "Resulting payment amount:': is not displayed in admin PayPal tab");
        $this->assertTextPresent("Voided amount:", "record 'Voided amount:': is not displayed in admin PayPal tab");
        $this->assertTextPresent("Money capture:", "Money capture:': is not displayed in admin PayPal tab");
        $this->assertTextPresent("Pending", "status 'Pending': is not displayed in admin PayPal tab");

        $basketPrice = "0,99";
        $capturedPrice = "0,00";
        $this->checkOrderPayPalTabPricesCorrect($basketPrice, $capturedPrice);

        $this->assertElementPresent("id=captureButton");
        $this->assertElementPresent("id=voidButton");

        $actionName = "authorization";
        $amount = "0.99";
        $paypalStatus = "Pending";
        $this->checkOrderPayPalTabHistoryCorrect($actionName, $amount, $paypalStatus);

        $quantity = "1";
        $productNumber = "1001";
        $productTitle = "Test product 1";
        $productGrossPrice = "0,99";
        $productTotalPrice = "0,99";
        $productVat = "19";
        $this->checkOrderPayPalTabProductsCorrect($quantity, $productNumber, $productTitle, $productGrossPrice, $productTotalPrice, $productVat);

        // Perform capturing
        $this->click("id=captureButton");
        $this->frame("edit");
        $this->clickAndWait("id=captureSubmit", 90);
        $this->waitForItemDisappear("id=captureSubmit");

        $basketPrice = "0,99";
        $capturedPrice = "0,99";
        $this->checkOrderPayPalTabPricesCorrect($basketPrice, $capturedPrice);

        $this->assertElementPresent("id=refundButton0", "Refunding is not available");
        $this->assertEquals("Completed", $this->getText("//b"), "Money status is not displayed in admin PayPal tab");

        $actionName = "capture";
        $amount = "0.99";
        $paypalStatus = "Completed";
        $this->checkOrderPayPalTabHistoryCorrect($actionName, $amount, $paypalStatus);

        $this->assertEquals("authorization", $this->getText("//table[@id='historyTable']/tbody/tr[3]/td[2]"), "Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//table[@id='historyTable']/tbody/tr[3]/td[3]"));
        $this->assertEquals("Pending", $this->getText("//table[@id='historyTable']/tbody/tr[3]/td[4]"), "Money status is not displayed in admin PayPal tab");

        // Perform Refund and check all info
        $this->click("id=refundButton0");
        $this->clickAndWaitFrame("id=refundSubmit", 'edit');
        $this->waitForItemDisappear("id=refundSubmit");
        $this->assertEquals("refund", $this->getText("//table[@id='historyTable']/tbody/tr[2]/td[2]"), "Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//table[@id='historyTable']/tbody/tr[2]/td[3]"));
        $this->assertEquals("Instant", $this->getText("//table[@id='historyTable']/tbody/tr[2]/td[4]"), "Money status is not displayed in admin PayPal tab");
        $this->assertEquals("capture", $this->getText("//table[@id='historyTable']/tbody/tr[3]/td[2]"), "Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//table[@id='historyTable']/tbody/tr[3]/td[3]"));
        $this->assertEquals("Completed", $this->getText("//table[@id='historyTable']/tbody/tr[3]/td[4]"), "Money status is not displayed in admin PayPal tab");
        $this->assertEquals("authorization", $this->getText("//table[@id='historyTable']/tbody/tr[4]/td[2]"), "Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//table[@id='historyTable']/tbody/tr[4]/td[3]"));
        $this->assertEquals("Pending", $this->getText("//table[@id='historyTable']/tbody/tr[4]/td[4]"), "Money status is not displayed in admin PayPal tab");
    }

    /**
     * Checkout a single product and change the quantity of the product to 5 afterards.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalRegularCheckoutAndChangeQuantityAfterwardsViaAdmin()
    {
        // Make an order with PayPal
        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);

        $this->click("userChangeAddress");
        $this->waitForItemAppear("order_remark");
        $this->type("order_remark", "Testing paypal");
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);

        $this->click("name=sShipSet");
        $this->selectAndWait("sShipSet", "label=Test S&H set");
        $this->waitForItemAppear("payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);

        $this->standardCheckoutWillBeUsed();
        $this->payWithPayPal();

        $this->assertElementPresent("//button[text()='Zahlungspflichtig bestellen']");
        $this->clickAndWait("//button[text()='Zahlungspflichtig bestellen']");
        $this->assertTextPresent("Vielen Dank für Ihre Bestellung im OXID eShop", "The order not finished successful");
        sleep(5);

        // Go to an admin and check this order nr
        $this->loginAdminForModule("Administer Orders", "Orders");
        $this->assureAdminOrderNameIsPresent();
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertTextPresent("Order No.: 2", "Order number is not displayed in admin");

        // Check user's order information in admin
        $this->assertEquals("1 *", $this->getText("//table[2]/tbody/tr/td[1]"), "Quantity of product is not correct in admin");
        $this->assertEquals("Test product 1", $this->getText("//td[3]"), "Purchased product name is not displayed in admin");
        $this->assertEquals("0,99 EUR", $this->getText("//td[5]"), "Unit price is not displayed in admin");
        $this->assertEquals("0,99", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));

        $this->openTab("Products");
        $this->assertEquals("1", $this->getValue("//tr[@id='art.1']/td[1]/input"), "Quantity of product is not correct in admin");
        $this->assertEquals("0,99 EUR", $this->getText("//tr[@id='art.1']/td[7]"), "Unit price is not displayed in admin");
        $this->assertEquals("0,99 EUR", $this->getText("//tr[@id='art.1']/td[8]"), "Total price is not displayed in admin");

        // Update product quantities to 5
        $this->type("//tr[@id='art.1']/td[1]/input", "5");
        $this->clickAndWait("//input[@value='Update']");
        $this->assertEquals("0,99 EUR", $this->getText("//tr[@id='art.1']/td[7]"), "Unit price is not displayed in admin");
        $this->assertEquals("4,95 EUR", $this->getText("//tr[@id='art.1']/td[8]"), "Total price is incorrect after update");
        $this->assertEquals("4,95", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));

        $this->openTab("Main");
        $this->assertEquals("Test S&H set", $this->getSelectedLabel("setDelSet"), "Shipping method is not displayed in admin");
        $this->assertEquals("PayPal", $this->getSelectedLabel("setPayment"));
    }


    /**
     * test if discounts working correct with PayPal.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalDiscountsCategory()
    {
        // Add vouchers to shop
        $this->importSql(__DIR__ . '/testSql/newDiscounts_' . SHOP_EDITION . '.sql');

        // Go to shop and add product
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("1000");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        // Login to shop and go to basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertTextPresent("Test product 0");
        $this->assertTextPresent("Test product 1", "Purchased product name is not displayed");
        $this->assertTextPresent("+1");
        $this->assertEquals("5,00 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");
        $this->assertEquals("5,00 € \n10,00 €", $this->getText("//tr[@id='cartItem_1']/td[6]"), "price with discount not shown in basket");
        // Go to 2nd step
        $this->clickNextStepInShopBasket();

        // Go to 3rd step and select PayPal as payment method
        $this->clickNextStepInShopBasket();
        $this->waitForItemAppear("id=payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        $this->payWithPayPal();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'AMT' => '5.00',
            'L_NAME0' => 'Test product 0',
            'L_NAME1' => 'Test product 1',
            'L_NUMBER0' => '1000',
            'L_NUMBER1' => '1001',
            'L_QTY0' => '1',
            'L_QTY1' => '1',
            'L_AMT0' => '5.00',
            'L_AMT1' => '0.00'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Go to shop to finish the order
        $this->assertTextPresent("Test product 0", "Purchased product name is not displayed in last order step");
        $this->assertTextPresent("Test product 1", "Purchased product name is not displayed in last order step");
        $this->assertEquals("Item #: 1001", $this->getText("//tr[@id='cartItem_2']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertEquals("Item #: 1000", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("1 +1");
        $this->assertEquals("4,20 €", $this->getText("basketTotalProductsNetto"), "Neto price changed or didn't displayed");
        $this->assertEquals("plus 19% tax, amount: 0,80 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("5,00 €", $this->getText("basketTotalProductsGross"), "Bruto price changed  or didn't displayed");
        $this->assertEquals("0,00 €", $this->getText("basketDeliveryGross"), "Shipping price changed  or didn't displayed");
        $this->assertEquals("5,00 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");

        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->assureAdminOrderNameIsPresent();
        $this->openListItem("2");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertEquals("5,00 EUR", $this->getText("//td[5]"));
        $this->assertEquals("Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.dev", $this->clearString($this->getText("//td[1]/table[1]/tbody/tr/td[1]")));
        $this->assertEquals("5,00", $this->getText("//table[@id='order.info']/tbody/tr[1]/td[2]"));
        $this->assertEquals("- 0,00", $this->getText("//table[@id='order.info']/tbody/tr[2]/td[2]"));
        $this->assertEquals("4,20", $this->getText("//table[@id='order.info']/tbody/tr[3]/td[2]"));
        $this->assertEquals("0,80", $this->getText("//table[@id='order.info']/tbody/tr[4]/td[2]"));
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[6]/td[2]"));
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[1]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[2]", "line with discount info is not displayed");
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("PayPal", $this->getText("//table[4]/tbody/tr[1]/td[2]"), "Payment method not displayed in admin");
        $this->assertEquals("Test S&H set", $this->getText("//table[4]/tbody/tr[2]/td[2]"), "Shipping method is not displayed in admin");
    }

    /**
     * test if few different discounts working correct with PayPal.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalDiscountsFromTill()
    {
        $this->markTestIncomplete('This test is very unstable, when running in the compilation. Sometimes it passes and sometimes it fails at four different places!');

        // Add vouchers to shop
        $this->importSql(__DIR__ . '/testSql/newDiscounts_' . SHOP_EDITION . '.sql');

        // Go to shop and add product
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("1004");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        // Login to shop and go to basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertTextPresent("Test product 4");

        $this->assertEquals("Discount discount from 10 till 20", $this->getText("//div[@id='basketSummary']/table/tbody/tr[2]/th"));
        $this->assertEquals("-0,30 €", $this->getText("//div[@id='basketSummary']/table/tbody/tr[2]/td"));
        $this->assertEquals("Grand total: 14,70 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");

        // Go to 2nd step
        $this->clickNextStepInShopBasket();

        // Go to 3rd step and select PayPal as payment method
        $this->clickNextStepInShopBasket();
        $this->waitForItemAppear("id=payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        //Go to PayPal
        $this->payWithPayPal();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'AMT' => '14.70',
            'ITEMAMT' => '15.00',
            'TAXAMT' => '0.00',
            'SHIPDISCAMT' => '-0.30',
            'L_NAME0' => 'Test product 4',
            'L_NAME1' => 'Test product 1',
            'L_NUMBER0' => '1004',
            'L_NUMBER1' => '1001',
            'L_QTY0' => '1',
            'L_QTY1' => '1',
            'L_AMT0' => '15.00',
            'L_AMT1' => '0.00'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Go to last step to check the order
        $this->assertTextPresent("Test product 4", "Purchased product name is not displayed");
        $this->assertTextPresent("Test product 1", "Purchased product name is not displayed");
        $this->assertEquals("Item #: 1004", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertEquals("Item #: 1001", $this->getText("//tr[@id='cartItem_2']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("1 +1");
        $this->assertEquals("-0,30 €", $this->getText("//div[@id='basketSummary']/table/tbody/tr[2]/td"));

        $this->assertEquals("Total products (incl. tax): 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("Discount discount from 10 till 20 -0,30 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("Total products (excl. tax): 12,35 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("plus 19% tax, amount: 2,35 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Shipping costs is not displayed correctly");
        $this->assertEquals("Grand total: 14,70 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");

        // Go back to 1st order step and change product quantities to 3
        $this->clickFirstStepInShopBasket();
        $this->type("id=am_1", "3");
        $this->click("id=basketUpdate");
        sleep(5);
        $this->assertEquals("Grand total: 42,75 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");
        $this->assertEquals("Discount discount from 20 till 50", $this->getText("//div[@id='basketSummary']/table/tbody/tr[2]/th"));
        $this->assertEquals("-2,25 €", $this->getText("//div[@id='basketSummary']/table/tbody/tr[2]/td"));
        // Go to 2nd step
        $this->clickNextStepInShopBasket();

        // Go to 3rd step and select PayPal as payment method
        $this->clickNextStepInShopBasket();
        $this->waitForItemAppear("id=payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        $this->standardCheckoutWillBeUsed();
        sleep(5);
        $this->payWithPayPal();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'PAYMENTREQUEST_0_AMT' => '42.75',
            'PAYMENTREQUEST_0_ITEMAMT' => '45.00',
            'PAYMENTREQUEST_0_SHIPDISCAMT' => '-2.25',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 4',
            'L_PAYMENTREQUEST_0_NAME1' => 'Test product 1',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1004',
            'L_PAYMENTREQUEST_0_NUMBER1' => '1001',
            'L_PAYMENTREQUEST_0_QTY0' => '3',
            'L_PAYMENTREQUEST_0_QTY1' => '1',
            'L_PAYMENTREQUEST_0_AMT0' => '15.00',
            'L_PAYMENTREQUEST_0_AMT1' => '0.00',];
        $this->assertLogData($assertRequest, $assertResponse);

        // Go to shop to finish the order
        $this->assertTextPresent("Test product 4", "Purchased product name is not displayed");
        $this->assertTextPresent("Test product 1", "Purchased product name is not displayed");
        $this->assertEquals("Item #: 1004", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertEquals("Item #: 1001", $this->getText("//tr[@id='cartItem_2']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("1 +1");
        $this->assertEquals("-2,25 €", $this->getText("//div[@id='basketSummary']/table/tbody/tr[2]/td"));

        $this->assertEquals("Total products (incl. tax): 45,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("Discount discount from 20 till 50 -2,25 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("Total products (excl. tax): 35,92 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("plus 19% tax, amount: 6,83 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Shipping costs is not displayed correctly");
        $this->assertEquals("Grand total: 42,75 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders");
        $this->waitForElement("//[@id='row.1']", 5, true);
        $this->assureAdminOrderNameIsPresent();
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertEquals("0,00 EUR", $this->getText("//td[5]"));

        $this->assertEquals("Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.dev", $this->clearString($this->getText("//td[1]/table[1]/tbody/tr/td[1]")));
        $this->assertEquals("45,00", $this->getText("//table[@id='order.info']/tbody/tr[1]/td[2]"));
        $this->assertEquals("- 2,25", $this->getText("//table[@id='order.info']/tbody/tr[2]/td[2]"));
        $this->assertEquals("35,92", $this->getText("//table[@id='order.info']/tbody/tr[3]/td[2]"));
        $this->assertEquals("6,83", $this->getText("//table[@id='order.info']/tbody/tr[4]/td[2]"));
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("42,75", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[1]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[2]", "line with discount info is not displayed");
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[6]/td[2]"));
        $this->assertEquals("PayPal", $this->getText("//table[4]/tbody/tr[1]/td[2]"), "Payment method not displayed in admin");
        $this->assertEquals("Test S&H set", $this->getText("//table[4]/tbody/tr[2]/td[2]"), "Shipping method is not displayed in admin");
    }

    /**
     * test if vouchers working correct with PayPal
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalVouchers()
    {
        $this->importSql(__DIR__ . '/testSql/newVouchers_' . SHOP_EDITION . '.sql');

        // Go to shop and add product
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("1003");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        // Login to shop and go to basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Grand total: 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->type("voucherNr", "111111");
        $this->clickAndWait("//button[text()='Submit coupon']");
        $this->assertTextPresent("Remove");
        $this->assertTextPresent("Coupon (No. 111111)");
        $this->assertEquals("Coupon (No. 111111) Remove -10,00 €", $this->getText("//div[@id='basketSummary']//tr[2]"));
        $this->assertEquals("Grand total: 5,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");

        // Go to 2nd step
        $this->clickNextStepInShopBasket();

        // Go to 3rd step and select paypla as payment method
        $this->clickNextStepInShopBasket();
        $this->waitForItemAppear("id=payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        $this->payWithPayPal();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'AMT' => '5.00',
            'ITEMAMT' => '15.00',
            'SHIPPINGAMT' => '0.00',
            'SHIPDISCAMT' => '-10.00',
            'L_NAME0' => 'Test product 3',
            'L_NUMBER0' => '1003',
            'L_QTY0' => '1',
            'L_TAXAMT0' => '0.00',
            'L_AMT0' => '15.00',];
        $this->assertLogData($assertRequest, $assertResponse);

        // Go to shop to finish the order
        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Item #: 1003", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");

        $this->assertEquals("Total products (incl. tax): 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("Total products (excl. tax): 4,20 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("plus 19% tax, amount: 0,80 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Shipping costs: is not displayed correctly");
        $this->assertEquals("Grand total: 5,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->assureAdminOrderNameIsPresent();
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertEquals("15,00 EUR", $this->getText("//td[5]"));
        $this->assertEquals("Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.dev", $this->clearString($this->getText("//td[1]/table[1]/tbody/tr/td[1]")));
        $this->assertEquals("15,00", $this->getText("//table[@id='order.info']/tbody/tr[1]/td[2]"));
        $this->assertEquals("- 0,00", $this->getText("//table[@id='order.info']/tbody/tr[2]/td[2]"));
        $this->assertEquals("4,20", $this->getText("//table[@id='order.info']/tbody/tr[3]/td[2]"));
        $this->assertEquals("0,80", $this->getText("//table[@id='order.info']/tbody/tr[4]/td[2]"));
        $this->assertEquals("- 10,00", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[6]/td[2]"));
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));
        $this->assertEquals("5,00", $this->getText("//table[@id='order.info']/tbody/tr[8]/td[2]"));

        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[1]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[2]", "line with discount info is not displayed");
        $this->assertEquals("- 10,00", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("PayPal", $this->getText("//table[4]/tbody/tr[1]/td[2]"), "Payment method not displayed in admin");
        $this->assertEquals("Test S&H set", $this->getText("//table[4]/tbody/tr[2]/td[2]"), "Shipping method is not displayed in admin");
    }

    /**
     * test if VAT is calculated in PayPal correct with different VAT options set in admins
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalVAT()
    {
        // Change price for PayPal payment methode
        $this->importSql(__DIR__ . '/testSql/vatOptions.sql');
        $this->importSql(__DIR__ . '/testSql/testPaypaVAT_' . SHOP_EDITION . '.sql');

        // Go to shop and add product
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("1003");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");

        // Login to shop and go to basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Test product 3", $this->getText("//tr[@id='cartItem_1']/td[3]/div[1]"));

        // Added wrapping and card to basket
        $this->click("id=header");
        $this->click("link=add");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Apply']");

        $this->assertEquals("Total products (excl. tax): 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("plus 19% tax, amount: 2,85 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("Total products (incl. tax): 17,85 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("Shipping (excl. tax): 13,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("plus 19% tax, amount: 2,47 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")));
        $this->assertEquals("3,51 €", $this->getText("basketWrappingGross"), "Wrapping price changed or didn't displayed");
        $this->assertEquals("3,57 €", $this->getText("basketGiftCardGross"), "Card price changed or didn't displayed");
        $this->assertEquals("40,40 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");

        // Go to 2nd step
        $this->clickNextStepInShopBasket();

        // Go to 3rd step and select PayPal as payment method
        $this->clickNextStepInShopBasket();
        $this->waitForItemAppear("id=payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        $this->payWithPayPal();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'AMT' => '52.90',
            'ITEMAMT' => '37.43',
            'SHIPPINGAMT' => '15.47',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 3',
            'L_PAYMENTREQUEST_0_NAME1' => 'Surcharge Type of Payment',
            'L_PAYMENTREQUEST_0_NAME2' => 'Giftwrapper',
            'L_PAYMENTREQUEST_0_NAME3' => 'Greeting Card',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1003',
            'L_PAYMENTREQUEST_0_QTY0' => '1',
            'L_PAYMENTREQUEST_0_QTY1' => '1',
            'L_PAYMENTREQUEST_0_QTY2' => '1',
            'L_PAYMENTREQUEST_0_QTY3' => '1',
            'L_PAYMENTREQUEST_0_AMT0' => '17.85',
            'L_PAYMENTREQUEST_0_AMT1' => '12.50',
            'L_PAYMENTREQUEST_0_AMT2' => '3.51',
            'L_PAYMENTREQUEST_0_AMT3' => '3.57',];
        $this->assertLogData($assertRequest, $assertResponse);

        // Go to shop to finish the order
        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Item #: 1003", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Greeting card");
        $this->assertEquals("3,57 €", $this->getText("id=orderCardTotalPrice"));
        $this->assertEquals("3,51 €", $this->getText("//div[@id='basketSummary']/table/tbody/tr[8]/td"));

        $this->assertEquals("Total products (excl. tax): 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("plus 19% tax, amount: 2,85 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("Total products (incl. tax): 17,85 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("Shipping (excl. tax): 13,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("plus 19% tax, amount: 2,47 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")));
        $this->assertEquals("Surcharge Payment method: 10,50 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")));
        $this->assertEquals("Surcharge 19% tax, amount: 2,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[7]")));
        $this->assertEquals("3,51 €", $this->getText("basketWrappingGross"), "Wrapping price changed or didn't displayed");
        $this->assertEquals("3,57 €", $this->getText("basketGiftCardGross"), "Card price changed or didn't displayed");
        $this->assertEquals("52,90 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->assureAdminOrderNameIsPresent();
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertEquals("17,85 EUR", $this->getText("//td[5]"));
        $this->assertEquals("Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.dev", $this->clearString($this->getText("//td[1]/table[1]/tbody/tr/td[1]")));
        $this->assertEquals("17,85", $this->getText("//table[@id='order.info']/tbody/tr[1]/td[2]"));
        $this->assertEquals("- 0,00", $this->getText("//table[@id='order.info']/tbody/tr[2]/td[2]"));
        $this->assertEquals("15,00", $this->getText("//table[@id='order.info']/tbody/tr[3]/td[2]"));
        $this->assertEquals("2,85", $this->getText("//table[@id='order.info']/tbody/tr[4]/td[2]"));
        $this->assertEquals("15,47", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("12,50", $this->getText("//table[@id='order.info']/tbody/tr[6]/td[2]"));
        $this->assertEquals("3,51", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));
        $this->assertEquals("3,57", $this->getText("//table[@id='order.info']/tbody/tr[8]/td[2]"));
        $this->assertEquals("52,90", $this->getText("//table[@id='order.info']/tbody/tr[9]/td[2]"));

        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[1]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[2]", "line with discount info is not displayed");
        $this->assertEquals("PayPal", $this->getText("//table[4]/tbody/tr[1]/td[2]"), "Payment method not displayed in admin");
        $this->assertEquals("Test S&H set", $this->getText("//table[4]/tbody/tr[2]/td[2]"), "Shipping method is not displayed in admin");
    }

    /**
     * test if PayPal works when proportional calculation is used for additional products.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalProportional()
    {
        $this->markTestIncomplete('This test is very unstable, when running in the compilation. Sometimes it passes and sometimes it fails at at least three different places!');

        // Change price for PayPal payment method
        $this->importSql(__DIR__ . '/testSql/newVAT.sql');

        // Go to admin and set on all VAT options
        $this->loginAdminForModule("Master Settings", "Core Settings");
        $this->openTab("Settings");
        $this->click("link=VAT");
        sleep(1);
        $this->check("//input[@name='confbools[blShowVATForWrapping]'and @value='true']");
        $this->check("//input[@name='confbools[blShowVATForDelivery]'and @value='true']");
        $this->check("//input[@name='confbools[blShowVATForPayCharge]'and @value='true']");
        $this->clickAndWait("save");

        // Go to shop and add product
        $this->clearCache();
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("100");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->clickAndWait("//form[@name='tobasketsearchList_2']//button");
        $this->clickAndWait("//form[@name='tobasketsearchList_3']//button");
        $this->clickAndWait("//form[@name='tobasketsearchList_4']//button");

        $this->openBasket("English");

        // Login to shop and go to basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertTextPresent("Test product 0");
        $this->assertTextPresent("Test product 1");
        $this->assertTextPresent("Test product 3");
        $this->assertTextPresent("Test product 4");

        // Added wrapping and card to basket
        $this->click("id=header");
        $this->click("link=add");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Apply']");
        $this->assertEquals("Total products (excl. tax): 36,33 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("plus 2% tax, amount: 0,20 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("plus 13% tax, amount: 0,11 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("plus 15% tax, amount: 1,96 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("plus 19% tax, amount: 2,39 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")));

        $this->assertEquals("Total products (incl. tax): 40,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")));
        $this->assertEquals("Shipping (excl. tax): 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[7]")));
        $this->assertEquals("Gift wrapping (excl. tax): 2,89 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[8]")));
        $this->assertEquals("2,89 €", $this->getText("basketWrappingNetto"), "Wrapping price changed or didn't displayed");
        $this->assertEquals("0,06 €", $this->getText("basketWrappingVat"), "Wrapping vat changed or didn't displayed");

        $this->assertEquals("2,52 €", $this->getText("basketGiftCardNetto"), "Card price changed or didn't displayed");
        $this->assertEquals("0,48 €", $this->getText("basketGiftCardVat"), "Card VAT price changed or didn't displayed");
        $this->assertEquals("46,94 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");

        // Go to 2nd step
        $this->clickNextStepInShopBasket();

        // Go to 3rd step and select PayPal as payment method
        $this->clickNextStepInShopBasket();
        $this->waitForItemAppear("id=payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        // Go to PayPal
        $this->payWithPayPal();

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'PAYMENTREQUEST_0_SHIPTONAME' => "Testing user acc Äß\\'ü PayPal Äß\\'ü",
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'PAYMENTREQUEST_0_AMT' => '46.94',
            'PAYMENTREQUEST_0_ITEMAMT' => '46.94',
            'PAYMENTREQUEST_0_SHIPPINGAMT' => '0.00',
            'PAYMENTREQUEST_0_HANDLINGAMT' => '0.00',
            'L_PAYMENTREQUEST_0_NAME0' => 'Test product 0',
            'L_PAYMENTREQUEST_0_NAME1' => 'Test product 1',
            'L_PAYMENTREQUEST_0_NAME2' => 'Test product 3',
            'L_PAYMENTREQUEST_0_NAME3' => 'Test product 4',
            'L_PAYMENTREQUEST_0_NAME4' => 'Giftwrapper',
            'L_PAYMENTREQUEST_0_NAME5' => 'Greeting Card',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1000',
            'L_PAYMENTREQUEST_0_NUMBER1' => '1001',
            'L_PAYMENTREQUEST_0_NUMBER2' => '1003',
            'L_PAYMENTREQUEST_0_NUMBER3' => '1004',
            'L_PAYMENTREQUEST_0_QTY0' => '1',
            'L_PAYMENTREQUEST_0_QTY1' => '1',
            'L_PAYMENTREQUEST_0_QTY2' => '1',
            'L_PAYMENTREQUEST_0_QTY3' => '1',
            'L_PAYMENTREQUEST_0_QTY4' => '1',
            'L_PAYMENTREQUEST_0_QTY5' => '1',
            'L_PAYMENTREQUEST_0_AMT0' => '10.00',
            'L_PAYMENTREQUEST_0_AMT1' => '0.99',
            'L_PAYMENTREQUEST_0_AMT2' => '15.00',
            'L_PAYMENTREQUEST_0_AMT3' => '15.00',
            'L_PAYMENTREQUEST_0_AMT4' => '2.95',
            'L_PAYMENTREQUEST_0_AMT5' => '3.00'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Go to shop to finish the order
        $this->assertTextPresent("Test product 0");
        $this->assertEquals("Item #: 1000", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Test product 1");
        $this->assertEquals("Item #: 1001", $this->getText("//tr[@id='cartItem_2']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Item #: 1003", $this->getText("//tr[@id='cartItem_3']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Test product 4");
        $this->assertEquals("Item #: 1004", $this->getText("//tr[@id='cartItem_4']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Greeting card");

        $this->assertEquals("36,33 €", $this->getText("basketTotalProductsNetto"), "Net price changed or didn't displayed");
        $this->assertEquals("0,20 €", $this->getText("//div[@id='basketSummary']//tr[2]/td"), "VAT 2% changed ");
        $this->assertEquals("0,11 €", $this->getText("//div[@id='basketSummary']//tr[3]/td"), "VAT 13% changed ");
        $this->assertEquals("1,96 €", $this->getText("//div[@id='basketSummary']//tr[4]/td"), "VAT 15% changed ");
        $this->assertEquals("2,39 €", $this->getText("//div[@id='basketSummary']//tr[5]/td"), "VAT 19% changed ");
        $this->assertEquals("40,99 €", $this->getText("basketTotalProductsGross"), "Brut price changed  or didn't displayed");
        $this->assertEquals("0,00 €", $this->getText("basketDeliveryNetto"), "Shipping price changed  or didn't displayed");
        $this->assertEquals("2,89 €", $this->getText("basketWrappingNetto"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("0,06 €", $this->getText("basketWrappingVat"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("2,52 €", $this->getText("basketGiftCardNetto"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("0,48 €", $this->getText("basketGiftCardVat"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("46,94 €", $this->getText("basketGrandTotal"), "Grand total price changed  or didn't displayed");

        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin to activate proportional calculation
        $this->loginAdminForModule("Master Settings", "Core Settings");
        $this->openTab("Settings");
        $this->click("link=VAT");
        usleep(50000);
        $this->check("//input[@name='confstrs[sAdditionalServVATCalcMethod]'and @value='proportional']");
        $this->clickAndWait("save");

        // Go to shop and add product
        $this->clearCache();
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("100");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->clickAndWait("//form[@name='tobasketsearchList_2']//button");
        $this->clickAndWait("//form[@name='tobasketsearchList_3']//button");
        $this->clickAndWait("//form[@name='tobasketsearchList_4']//button");

        $this->openBasket("English");

        // Login to shop and go to basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertTextPresent("Test product 0");
        $this->assertTextPresent("Test product 1");
        $this->assertTextPresent("Test product 3");
        $this->assertTextPresent("Test product 4");

        // Added wrapping and card to basket
        $this->click("id=header");
        $this->click("link=add");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Apply']");

        $this->assertEquals("Total products (excl. tax): 36,33 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("plus 2% tax, amount: 0,20 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[2]")));
        $this->assertEquals("plus 13% tax, amount: 0,11 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("plus 15% tax, amount: 1,96 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("plus 19% tax, amount: 2,39 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")));

        $this->assertEquals("Total products (incl. tax): 40,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")));
        $this->assertEquals("Shipping (excl. tax): 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[7]")));
        $this->assertEquals("Gift wrapping (excl. tax): 2,89 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[8]")));
        $this->assertEquals("2,89 €", $this->getText("basketWrappingNetto"), "Wrapping price changed or didn't displayed");
        $this->assertEquals("0,06 €", $this->getText("basketWrappingVat"), "Wrapping vat changed or didn't displayed");
        $this->assertEquals("2,66 €", $this->getText("basketGiftCardNetto"), "Card price changed or didn't displayed");
        $this->assertEquals("0,34 €", $this->getText("basketGiftCardVat"), "Card VAT price changed or didn't displayed");
        $this->assertEquals("46,94 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't displayed");

        // Go to 2nd step
        $this->clickNextStepInShopBasket();

        // Go to 3rd step and select PayPal as payment method
        $this->clickNextStepInShopBasket();
        $this->waitForItemAppear("id=payment_oxidpaypal");
        $this->click("id=payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        // Going to PayPal
        $this->standardCheckoutWillBeUsed();
        sleep(5);
        $this->payWithPayPal();

        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK'                           => 'Success',
            'PAYMENTREQUEST_0_AMT'          => '46.94',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'L_PAYMENTREQUEST_0_NAME0'      => 'Test product 0',
            'L_PAYMENTREQUEST_0_AMT0'       => '10.00',
            'L_PAYMENTREQUEST_0_QTY0'       => '1',
            'L_PAYMENTREQUEST_0_NUMBER0'    => '1000',
            'L_PAYMENTREQUEST_0_NAME1'      => 'Test product 1',
            'L_PAYMENTREQUEST_0_AMT1'       => '0.99',
            'L_PAYMENTREQUEST_0_QTY1'       => '1',
            'L_PAYMENTREQUEST_0_NUMBER1'    => '1001',
            'L_PAYMENTREQUEST_0_NAME2'      => 'Test product 3',
            'L_PAYMENTREQUEST_0_AMT2'       => '15.00',
            'L_PAYMENTREQUEST_0_QTY2'       => '1',
            'L_PAYMENTREQUEST_0_NUMBER2'    => '1003',
            'L_PAYMENTREQUEST_0_NAME3'      => 'Test product 4',
            'L_PAYMENTREQUEST_0_AMT3'       => '15.00',
            'L_PAYMENTREQUEST_0_QTY3'       => '1',
            'L_PAYMENTREQUEST_0_NUMBER3'    => '1004',
            'L_PAYMENTREQUEST_0_NAME4'      => 'Giftwrapper',
            'L_PAYMENTREQUEST_0_AMT4'       => '2.95',
            'L_PAYMENTREQUEST_0_QTY4'       => '1',
            'L_PAYMENTREQUEST_0_NAME5'      => 'Greeting Card',
            'L_PAYMENTREQUEST_0_AMT5'       => '3.00',
            'L_PAYMENTREQUEST_0_QTY5'       => '1'];
        $this->assertLogData($assertRequest, $assertResponse);

        // Go to shop to finish the order
        $this->assertTextPresent("Test product 0");
        $this->assertEquals("Item #: 1000", $this->getText("//tr[@id='cartItem_1']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Test product 1");
        $this->assertEquals("Item #: 1001", $this->getText("//tr[@id='cartItem_2']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Test product 3");
        $this->assertEquals("Item #: 1003", $this->getText("//tr[@id='cartItem_3']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Test product 4");
        $this->assertEquals("Item #: 1004", $this->getText("//tr[@id='cartItem_4']/td[2]/div[2]"), "Product number not displayed in last order step");
        $this->assertTextPresent("Greeting card");

        $this->assertEquals("36,33 €", $this->getText("basketTotalProductsNetto"), "Net price changed or didn't displayed");
        $this->assertEquals("0,20 €", $this->getText("//div[@id='basketSummary']//tr[2]/td"), "VAT 2% changed ");
        $this->assertEquals("0,11 €", $this->getText("//div[@id='basketSummary']//tr[3]/td"), "VAT 13% changed ");
        $this->assertEquals("1,96 €", $this->getText("//div[@id='basketSummary']//tr[4]/td"), "VAT 15% changed ");
        $this->assertEquals("2,39 €", $this->getText("//div[@id='basketSummary']//tr[5]/td"), "VAT 19% changed ");
        $this->assertEquals("40,99 €", $this->getText("basketTotalProductsGross"), "Brut price changed  or didn't displayed");
        $this->assertEquals("0,00 €", $this->getText("basketDeliveryNetto"), "Shipping price changed  or didn't displayed");
        $this->assertEquals("2,89 €", $this->getText("basketWrappingNetto"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("0,06 €", $this->getText("basketWrappingVat"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("2,66 €", $this->getText("basketGiftCardNetto"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("0,34 €", $this->getText("basketGiftCardVat"), "Wrapping price changed  or didn't displayed");
        $this->assertEquals("46,94 €", $this->getText("basketGrandTotal"), "Grand total price changed  or didn't displayed");

        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");

        // Go to admin and check the order
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->assureAdminOrderNameIsPresent();
        $this->openListItem("link=2");
        $this->assertTextPresent("Internal Status: OK");
        $this->assertEquals("10,00 EUR", $this->getText("//td[5]"));

        $this->assertEquals("Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.dev", $this->clearString($this->getText("//td[1]/table[1]/tbody/tr/td[1]")));
        $this->assertEquals("40,99", $this->getText("//table[@id='order.info']/tbody/tr[1]/td[2]"));
        $this->assertEquals("- 0,00", $this->getText("//table[@id='order.info']/tbody/tr[2]/td[2]"));
        $this->assertEquals("36,33", $this->getText("//table[@id='order.info']/tbody/tr[3]/td[2]"));
        $this->assertEquals("0,20", $this->getText("//table[@id='order.info']/tbody/tr[4]/td[2]"));
        $this->assertEquals("0,11", $this->getText("//table[@id='order.info']/tbody/tr[5]/td[2]"));
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[6]/td[2]"));
        $this->assertEquals("0,00", $this->getText("//table[@id='order.info']/tbody/tr[7]/td[2]"));
        $this->assertEquals("2,95", $this->getText("//table[@id='order.info']/tbody/tr[8]/td[2]"));
        $this->assertEquals("3,00", $this->getText("//table[@id='order.info']/tbody/tr[9]/td[2]"));
        $this->assertEquals("46,94", $this->getText("//table[@id='order.info']/tbody/tr[10]/td[2]"));

        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[1]", "line with discount info is not displayed");
        $this->assertElementPresent("//table[@id='order.info']/tbody/tr[2]/td[2]", "line with discount info is not displayed");
        $this->assertEquals("PayPal", $this->getText("//table[4]/tbody/tr[1]/td[2]"), "Payment method not displayed in admin");
        $this->assertEquals("Test S&H set", $this->getText("//table[4]/tbody/tr[2]/td[2]"), "Shipping method is not displayed in admin");
    }

    /**
     * test if PayPal works in Net mode
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalStandardNettoMode()
    {
        // Activate the necessary options netto mode
        $this->importSql(__DIR__ . '/testSql/NettoModeTurnOn_' . SHOP_EDITION . '.sql');

        // Add articles to basket.
        $this->openShop();
        $this->searchFor("1401");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);

        // Change price for PayPal payment method
        $this->importSql(__DIR__ . '/testSql/vatOptions.sql');

        // Need to wait after switching language as basket layout might not appear if JavaScript is not loaded.
        $this->switchLanguage("Deutsch");
        sleep(1);
        $this->openBasket("Deutsch");

        // Added wrapping and card to basket.
        $this->click("id=header");
        $this->click("link=hinzufügen");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Übernehmen']");

        // Check wrapping and card prices.
        $this->assertEquals("2,95 €", $this->getText("basketWrappingGross"), "Wrapping price changed or didn't display");
        $this->assertEquals("3,00 €", $this->getText("basketGiftCardGross"), "Card price changed or didn't display");

        // Check basket prices.
        $this->assertEquals("108,40 €", $this->getText("basketTotalProductsNetto"), "Net price changed or didn't display");
        $this->assertEquals("134,95 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't display");

        // Add more articles so sum would be more than 500eur.
        // Without sleep basket update do not make update before checking actual prices.
        $this->type("am_1", "5");
        sleep(1);
        $this->clickAndWait("basketUpdate");
        sleep(1);

        // Check basket prices.
        $this->assertEquals("542,00 €", $this->getText("basketTotalProductsNetto"), "Net price changed or didn't display");
        $this->assertTextPresent("102,98 €", "Articles VAT changed or didn't display");
        $this->assertEquals("662,73 €", $this->getText("basketGrandTotal"), "Grand total price changed or didn't display");

        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);

        // On 2nd step
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->waitForText("Lieferadresse");

        // On 3rd step
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->waitForText("Bitte wählen Sie Ihre Versandart");

        // Go to PayPal
        $this->selectPaymentPayPal();
        $this->click(self::SELECTOR_BASKET_NEXTSTEP);
        $this->payWithPayPal();

        $this->assertElementPresent("//button[text()='Zahlungspflichtig bestellen']");

        // Check what was communicated with PayPal
        $assertRequest = ['METHOD' => 'GetExpressCheckoutDetails'];
        $assertResponse = ['ACK' => 'Success',
            'EMAIL' => $this->getLoginDataByName('sBuyerLogin'),
            'L_PAYMENTREQUEST_0_NAME0' => 'Trapez ION SOL KITE 2011',
            'L_PAYMENTREQUEST_0_NUMBER0' => '1401',
            'L_PAYMENTREQUEST_0_AMT0' => '108.40',
            'L_PAYMENTREQUEST_0_AMT1' => '8.82',
            'L_PAYMENTREQUEST_0_AMT2' => '12.39',
            'L_PAYMENTREQUEST_0_AMT3' => '2.52',
            'PAYMENTREQUEST_0_TAXAMT' => '107.50',
            'PAYMENTREQUEST_0_AMT' => '686.23',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'PAYMENTREQUEST_0_ITEMAMT' => '565.73',
            'PAYMENTREQUEST_0_SHIPPINGAMT' => '13.00'];
        $this->assertLogData($assertRequest, $assertResponse);
    }

    /**
     * Testing ability to change country in standard PayPal.
     * NOTE: this test originally asserted data on PayPal page.
     * ($this->assertFalse($this->isElementPresent("id=changeAddressButton"), "In standard PayPal there should be not possibility to change address");)
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     */
    public function testPayPalStandard()
    {
        // Login to shop and go standard PayPal
        $this->openShop();
        $this->switchLanguage("English");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->clickNextStepInShopBasket();
        $this->assertTextPresent("Germany", "Users country should be Germany");
        $this->clickNextStepInShopBasket();
        $this->assertElementPresent("//input[@value='oxidpaypal']");
        $this->click("payment_oxidpaypal");
        $this->clickNextStepInShopBasket();

        $this->payWithPayPal();

        $this->assertTextPresent("PayPal", "Payment method not displayed in last order step");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");
    }

    /**
     * Select the payment "PayPal" in the order process.
     */
    protected function selectPaymentPayPal()
    {
        return $this->click("payment_oxidpaypal");
    }

    /**
     * Get standard checkout BUTTONSOURCE parameter according to shop edition.
     *
     * @return string
     */
    private function getButtonSource()
    {
        $facts = new \OxidEsales\Facts\Facts();
        $buttonSource = 'OXID_Cart_CommunityECS';

        if ('EE' == $facts->getEdition()) {
            $buttonSource = 'OXID_Cart_EnterpriseECS';
        }
        if ('PE' == $facts->getEdition()) {
            $buttonSource = 'OXID_Cart_ProfessionalECS';
        }

        return $buttonSource;
    }
}
