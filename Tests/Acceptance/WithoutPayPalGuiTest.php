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
class WithoutPayPalGuiTest extends BaseAcceptanceTestCase
{
    /**
     * testing different countries with shipping rules assigned to this countries
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_nobuyerlogin
     */
    public function testForLoginUserChangeUserCountryToUnassignedPaymentMethod()
    {
        $this->addToBasket('1001');
        $this->loginToShopFrontend();

        $this->waitForElement('paypalExpressCheckoutButton');
        $this->clickNextStepInShopBasket();

        // Check that the user mail address exists and is the expected.
        $this->assertEquals("E-mail: testing_account@oxid-esales.dev SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany", $this->clearString($this->getText("//ul[@id='addressText']//li")), "User address is incorect");

        // Change to new one which has not PayPal assigned as payment method inside PayPal
        $this->changeCountryInBasketStepTwo('United States');
        $this->clickFirstStepInShopBasket();

        $this->assertFalse($this->isElementPresent('paypalPartnerLogo'), 'PayPal logo should not be displayed for US');
    }

    /**
     * testing PayPal ECS in detail page and ECS in mini basket
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_nobuyerlogin
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
    }

    /**
     * testing if express button is not visible when PayPal is not active
     *
     * @group paypal_standalone
     * @group paypal_nobuyerlogin
     */
    public function testPayPalExpressWhenPayPalInactive()
    {
        // Disable PayPal
        $this->loginAdminForModule("Extensions", "Modules");
        $this->openListItem("PayPal");
        $this->frame("edit");
        $this->clickAndWait("module_deactivate");
        $this->assertElementPresent("id=module_activate", "The button Activate module is not displayed ");

        // After PayPal module is deactivated,  PayPal express button should  not be available in basket
        $this->clearCache();
        $this->openShop();
        $this->switchLanguage("Deutsch");
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("Deutsch");
        $this->assertElementNotPresent("paypalExpressCheckoutBox", "PayPal should not be displayed, because Paypal is deactivated");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertElementNotPresent("paypalExpressCheckoutBox", "PayPal should not be displayed, because Paypal is deactivated");

        // On 2nd step
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->waitForText("Lieferadresse");

        // On 3rd step
        $this->clickAndWait(self::SELECTOR_BASKET_NEXTSTEP);
        $this->waitForText("Bitte wählen Sie Ihre Versandart");
        $this->selectAndWait("sShipSet", "label=Standard");
        $this->assertEquals("Kosten: 3,90 €", $this->getText("shipSetCost"));
        $this->assertElementNotPresent("//input[@value='oxidpaypal']");
        $this->selectAndWait("sShipSet", "label=Test S&H set");
        $this->assertElementNotPresent("//input[@value='oxidpaypal']");

        // clearing cache as disabled module is cached
        $this->clearCache();
    }

    /**
     * Testing ability to change country in standard PayPal.
     * NOTE: this test originally asserted data on PayPal page.
     * ($this->assertFalse($this->isElementPresent("id=changeAddressButton"), "In standard PayPal there should be not possibility to change address");)
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_nobuyerlogin
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
    }

    /**
     * test if payment method PayPal is deactivated in shop backend, the PayPal express button should also disappear.
     *
     * @group paypal_standalone
     * @group paypal_nobuyerlogin
     */
    public function testPayPalActive()
    {
        // Set PayPal payment inactive.
        $this->importSql(__DIR__ . '/testSql/setPayPalPaymentInactive.sql');

        // Go to shop to check is PayPal not visible in front end
        $this->openShop();
        $this->assertFalse($this->isElementPresent("paypalPartnerLogo"), "PayPal logo not shown in frontend page");
        $this->switchLanguage("Deutsch");
        $this->assertFalse($this->isElementPresent("paypalPartnerLogo"), "PayPal logo not shown in frontend page");
        $this->switchLanguage("English");

        // Go to basket and check is express PayPal not visible
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket("English");
        $this->assertFalse($this->isElementPresent("paypalExpressCheckoutButton"), "PayPal express button should be not visible in frontend");

        // Login to shop and go to the basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->assertFalse($this->isElementPresent("paypalExpressCheckoutButton"), "PayPal express button should be not visible in frontend");
    }

    /**
     * test if PayPal is not shown in frontend after configs is set in admin
     *
     * @group paypal_standalone
     * @group paypal_nobuyerlogin
     */
    public function testPayPalShortcut()
    {
        // Turn Off all PayPal shortcut in frontend
        $this->importSql(__DIR__ . '/testSql/testPayPalShortcut_' . SHOP_EDITION . '.sql');

        // Add articles to basket.
        $this->openShop();
        $this->switchLanguage("English");
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->searchFor("1001");
        $this->clickAndWait("//ul[@id='searchList']/li/form/div/a[2]/span");
        $this->assertFalse($this->isElementPresent("id=paypalExpressCheckoutDetailsButton"), "After PayPal is disabled in admin PayPal should not be visible in admin");
        $this->clickAndWait("id=toBasket");
        $this->click("id=minibasketIcon");
        $this->assertFalse($this->isElementPresent("id=paypalExpressCheckoutMiniBasketImage"));
        $this->clickAndWait("link=Display cart");
        $this->assertFalse($this->isElementPresent("//input[name='paypalExpressCheckoutButton']"));
        $this->clickAndWait("id=basketUpdate");
        $this->clickNextStepInShopBasket();
        $this->clickAndWait("id=userNextStepTop");
        $this->assertFalse($this->isElementPresent("id=payment_oxidpaypal"));
        $this->clickAndWait("id=paymentNextStepBottom");
        $this->waitForShop();
        $this->clickAndWait("//button[text()='Order now']");

        $this->assertTextPresent("Thank you for ordering at OXID eShop", "Order is not finished successful");

        // Go to Admin
        $this->loginAdminForModule("Administer Orders", "Orders", "btn.help", "link=2");
        $this->openListItem("2");

        // Go to PayPal tab
        $this->openTab("PayPal");
        $this->assertEquals("This tab is for orders with the PayPal payment method only", $this->getText("//div[2]/div[2]"));
    }

    /**
     * This is a regression test:
     * There was a bug in the PayPal module, that after deactivation of the PayPal module the admin was not working any
     * more until the browser session was cleared.
     * Technical background: the basket object is stored in/restored from the session on each page or frame reload,
     * As the PayPal module extends the basket object, an instance of the specific PayPal basket object is stored.
     * After module deactivation this object cannot be restored.
     *
     * @group paypal_nobuyerlogin
     */
    public function testModuleDeactivationDoesNotResultInMaintenancePage()
    {
        $pageReloadTime = 2; // seconds
        $this->loginAdminForModule("Extensions", "Modules");
        $this->openListItem("PayPal");
        $this->frame("edit");
        // Deactivate the PayPal module, if it is not active activate it first.
        try {
            $this->click("module_deactivate");
        } catch (\Exception $exception) {
            // The module was not active, so activate and deactivate it
            $this->click("module_activate");
            $this->logoutAdmin("link=Logout");
            $this->loginAdminForModule("Extensions", "Modules");
            $this->openListItem("PayPal");
            $this->frame("edit");
            $this->click("module_deactivate");
        }

        // It is not possible to use assertTextNotPresent here, as the timeout of that function is to long
        sleep($pageReloadTime);
        $this->assertFalse(
            $this->isTextPresent('Maintenance mode'),
            'The eShop Admin went into Maintenance mode after module deactivation. 
                The text "Maintenance mode" is present on the page.'
        );
    }
}