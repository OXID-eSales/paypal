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


require_once 'acceptance/oepaypal/oxidAdditionalSeleniumFunctions.php';

class Acceptance_oePayPal_oePayPalTest extends oxidAdditionalSeleniumFunctions
{
    protected $_sVersion = "EE";

    protected function setUp( $skipDemoData = false )
    {
        parent::setUp( false );

        if ( OXID_VERSION_PE_PE ) :
            $this->_sVersion = "PE";
        endif;
        if ( OXID_VERSION_EE ) :
            $this->_sVersion = "EE";
        endif;
        if ( OXID_VERSION_PE_CE ) :
            $this->_sVersion = "CE";
        endif;

    }

    /**
     * Executed after test is down
     *
     */
    protected function tearDown()
    {
        $this->callUrl( shopURL . "/_restoreDB.php", "restoreDb=1" );
        parent::tearDown();
    }

    /**
     * Returns PayPal login data by variable name
     *
     * @param $sVarName
     * @return mixed|null|string
     * @throws Exception
     */
    public function getLoginDataByName( $sVarName )
    {
        if ( !$sVarValue = getenv( $sVarName ) ) {
            $sVarValue = $this->getArrayValueFromFile( $sVarName, 'acceptance/oepaypal/testdata/oepaypalData.php' );
        }

        if ( !$sVarValue ) {
            throw new Exception( 'Undefined variable: ' . $sVarName );
        }

        return $sVarValue;
    }

    /**
     * Call script file
     *
     * @param $sShopUrl
     * @param string $sParams
     */
    public function callUrl( $sShopUrl, $sParams = "" )
    {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $sShopUrl );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );

        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $sParams );
        curl_setopt( $ch, CURLOPT_USERAGENT, "OXID-SELENIUMS-CONNECTOR" );
        $sRes = curl_exec( $ch );

        curl_close( $ch );
    }

    /**
     * Copy files to shop
     *
     * @param $sCopyDir
     * @param $sShopDir
     * @throws Exception
     */
    public function copyFile( $sCopyDir, $sShopDir )
    {
        $sCmd = "cp -frT " . escapeshellarg( $sCopyDir ) . " " . escapeshellarg( $sShopDir );
        if ( SHOP_REMOTE ) {
            $sCmd = "scp -rp " . escapeshellarg( $sCopyDir . "/." ) . " " . escapeshellarg( SHOP_REMOTE );
        }
        exec( $sCmd, $sOut, $ret );
        $sOut = implode( "\n", $sOut );
        if ( $ret > 0 ) {
            throw new Exception( $sOut );
        }
    }

    // ------------------------ PayPal module ----------------------------------

    /**
     * test for activating PayPal
     * @group paypal_standalone
     */
    public function testActivatePayPal()
    {
        //copy module files to shop
        if ( defined( 'MODULE_PKG_DIR' ) ) {
            $sModuleDir = MODULE_PKG_DIR;
            $sCopyDir = rtrim( $sModuleDir, "/" ) . "/copy_this";
            $this->copyFile( $sCopyDir, oxPATH );
            $sCopyDir = rtrim( $sModuleDir, "/" ) . "/changed_full";
            $this->copyFile( $sCopyDir, oxPATH );
        }
        $this->getLoginDataByName( 'sOEPayPalUsername' );
        $this->open( shopURL . "_prepareDB.php?version=" . $this->_sVersion );
        $this->open( shopURL . "admin" );
        $this->loginAdminForModule( "Extensions", "Modules" );
        $this->openTab( "link=PayPal" );
        $this->frame( "edit" );
        $this->clickAndWait( "module_activate" );
        $this->frame( "list" );
        $this->clickAndWait( "//a[text()='Settings']" );
        $this->frame( "edit" );
        $this->click( "//b[text()='Capture']" );
        $this->click( "//b[text()='API signature']" );
        $this->click( "//b[text()='Development settings']" );

        $this->select( "//select[@name='confselects[sOEPayPalTransactionMode]']", "value=Authorization" );

        $this->type( "//input[@name='confstrs[sOEPayPalUsername]']", $this->getLoginDataByName( 'sOEPayPalUsername' ) );
        $this->type( "//input[@name='confstrs[sOEPayPalPassword]']", $this->getLoginDataByName( 'sOEPayPalPassword' ) );
        $this->type( "//input[@name='confstrs[sOEPayPalSignature]']", $this->getLoginDataByName( 'sOEPayPalSignature' ) );

        $this->click( "//input[@name='confbools[blOEPayPalSandboxMode]' and @type='checkbox']" );
        $this->type( "//input[@name='confstrs[sOEPayPalSandboxUsername]']", $this->getLoginDataByName( 'sOEPayPalSandboxUsername' ) );
        $this->type( "//input[@name='confstrs[sOEPayPalSandboxPassword]']", $this->getLoginDataByName( 'sOEPayPalSandboxPassword' ) );
        $this->type( "//input[@name='confstrs[sOEPayPalSandboxSignature]']", $this->getLoginDataByName( 'sOEPayPalSandboxSignature' ) );
        $this->clickAndWait( "//input[@name='save']" );
        $this->callUrl( shopURL . "/_restoreDB.php", "dumpDb=1" );
    }

    /**
     * testing PayPal payment selection
     * @group paypal_standalone
     */
    public function testPayPalPayment1()
    {
        $this->openShop();

        $this->switchLanguage( "Deutsch" );
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "Deutsch" );
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->click( "userChangeAddress" );
        $this->waitForItemAppear( "order_remark" );
        $this->type( "order_remark", "Testing paypal" );
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->click( "payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->waitForElement( "login.x" );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->click( "id=continue" );
        $this->waitForText( "Bitte prüfen Sie alle Daten, bevor Sie Ihre Bestellung abschließen!" );
        $this->assertEquals("0,99 €", $this->getText("basketGrandTotal"),"Grand total price changed or didn't displayed");
        $this->assertEquals( "Zahlungsart Ändern PayPal", $this->clearString( $this->getText( "orderPayment" ) ) );
        $this->assertEquals( "Versandart Ändern Test S&H set", $this->clearString( $this->getText( "orderShipping" ) ) );
        $this->assertEquals( "Adressen Ändern Rechnungsadresse E-Mail: testing_account@oxid-esales.com SeleniumTestCase Äß'ü Testing acc for Selenium Herr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Deutschland Ihre Mitteilung an uns Testing paypal", $this->clearString( $this->getText( "orderAddress" ) ) );
        $this->clickAndWait( "//button[text()='Zahlungspflichtig bestellen']", null, 90 );
        $this->assertTrue( $this->isTextPresent( "Vielen Dank für Ihre Bestellung im OXID eShop" ), "Order is not finished successful" );

        //Checking if order is saved in Admin
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.1']/td[6]" ) );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.1']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2" );

        // Go to PayPal tab to check all order info
        $this->frame( "list" );
        $this->click("//a[contains(@href, '#oepaypalorder_paypal')]");
        $this->waitForFrameToLoad("edit");
        $this->frame( "edit" );
        $this->assertTrue($this->isTextPresent("Shop payment status:"),"record 'Shop payment status:' is not displayed in admin PayPal tab");
        $this->assertTrue($this->isTextPresent("Full order price:"),"record 'Full order price:': is not displayed in admin PayPal tab");
        $this->assertTrue($this->isTextPresent("Captured amount:"),"record 'Captured amount:': is not displayed in admin PayPal tab");
        $this->assertTrue($this->isTextPresent("Refunded amount:"),"Refunded amount:': is not displayed in admin PayPal tab");
        $this->assertTrue($this->isTextPresent("Resulting payment amount:"),"Resulting payment amount:': is not displayed in admin PayPal tab");
        $this->assertTrue($this->isTextPresent("Voided amount:"),"record 'Voided amount:': is not displayed in admin PayPal tab");
        $this->assertTrue($this->isTextPresent("Money capture:"),"Money capture:': is not displayed in admin PayPal tab");
        $this->assertTrue($this->isTextPresent("Pending"),"status 'Pending': is not displayed in admin PayPal tab");
        $this->assertEquals("0,99 EUR", $this->getText("//tr[2]/td[2]/b"),"Full amount is not displayed in admin PayPal tab");
        $this->assertEquals("0,00 EUR", $this->getText("//tr[3]/td[2]/b"),"Captured amount is not displayed in admin PayPal tab");
        $this->assertEquals("0,00 EUR", $this->getText("//tr[4]/td[2]/b"),"Refunded amount is not displayed in admin PayPal tab");
        $this->assertEquals("0,00 EUR", $this->getText("//tr[5]/td[2]/b"),"Resulting amount is not displayed in admin PayPal tab");
        $this->assertEquals("0,00 EUR", $this->getText("//tr[6]/td[2]/b"),"Voided amount is not displayed in admin PayPal tab");
        $this->assertTrue($this->isElementPresent("id=captureButton"));
        $this->assertTrue($this->isElementPresent("id=voidButton"));
        $this->assertEquals("authorization", $this->getText("//table[2]/tbody/tr[2]/td[2]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//tr[2]/td[3]"));
        $this->assertEquals("Pending", $this->getText("//tr[2]/td[4]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0,99 EUR", $this->getText("//tr[2]/td[2]/b"));
        $this->assertEquals("1", $this->getText("//tr[@id='art.1']/td"));
        $this->assertEquals("1001", $this->getText("//tr[@id='art.1']/td[2]"));
        $this->assertEquals("Test product 1", $this->getText("//tr[@id='art.1']/td[3]"));
        $this->assertEquals("0,99 EUR", $this->getText("//tr[@id='art.1']/td[4]"));
        $this->assertEquals("0,99 EUR", $this->getText("//tr[@id='art.1']/td[5]"));
        $this->assertEquals("19", $this->getText("//tr[@id='art.1']/td[6]"));

        // Perform capturing
        $this->click("id=captureButton");
        $this->selectFrame("relative=up");
        $this->selectFrame("relative=up");
        $this->selectFrame("basefrm");
        $this->selectFrame("edit");
        $this->click("id=captureSubmit");
        $this->waitForPageToLoad("30000");

        // Check does all order info displayed properly after capturing
        $this->assertEquals("0,99 EUR", $this->getText("//tr[2]/td[2]/b"));
        $this->assertEquals("0,99 EUR", $this->getText("//tr[3]/td[2]/b"));
        $this->assertEquals("0,00 EUR", $this->getText("//tr[4]/td[2]/b"));
        $this->assertEquals("0,99 EUR", $this->getText("//tr[5]/td[2]/b"));
        $this->assertEquals("0,00 EUR", $this->getText("//tr[6]/td[2]/b"));
        $this->assertEquals("Completed", $this->getText("//b"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("capture", $this->getText("//table[2]/tbody/tr[2]/td[2]"));
        $this->assertEquals("0.99 EUR", $this->getText("//tr[2]/td[3]"));
        $this->assertEquals("Completed", $this->getText("//tr[2]/td[4]"),"Money status is not displayed in admin PayPal tab");
        $this->assertTrue($this->isElementPresent("id=refundButton0"), "Refunding is not available");
        $this->assertEquals("authorization", $this->getText("//table[2]/tbody/tr[3]/td[2]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//tr[3]/td[3]"));
        $this->assertEquals("Pending", $this->getText("//tr[3]/td[4]"),"Money status is not displayed in admin PayPal tab");

        // Perform Refund and check all info
        $this->click("id=refundButton0");
        $this->clickAndWait("id=refundSubmit");
        $this->assertEquals("refund", $this->getText("//table[2]/tbody/tr[2]/td[2]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//tr[2]/td[3]"));
        $this->assertEquals("Instant", $this->getText("//tr[2]/td[4]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("capture", $this->getText("//table[2]/tbody/tr[3]/td[2]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//tr[3]/td[3]"));
        $this->assertEquals("Completed", $this->getText("//tr[3]/td[4]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("authorization", $this->getText("//table[2]/tbody/tr[4]/td[2]"),"Money status is not displayed in admin PayPal tab");
        $this->assertEquals("0.99 EUR", $this->getText("//tr[4]/td[3]"));
        $this->assertEquals("Pending", $this->getText("//tr[4]/td[4]"),"Money status is not displayed in admin PayPal tab");
    }

    /**
     * testing PayPal ECS in detail page and ECS in mini basket
     * @group paypal_standalone
     */
    public function testECS()
    {
        // Open shop and add product to the basket
        $this->openShop();
        $this->searchFor( "1001" );
        $this->clickAndWait("//ul[@id='searchList']/li/form/div/a[2]/span");
        $this->clickAndWait("id=toBasket");

        // Open mini basket
        $this->click("id=minibasketIcon");
        $this->assertTrue($this->isElementPresent("//div[@id='paypalExpressCheckoutDetailsBox']/div/a"),"No express PayPal button in mini cart");
        $this->assertTrue($this->isElementPresent("id=paypalExpressCheckoutDetailsButton"),"No express PayPal button in mini cart");
        $this->assertTrue($this->isElementPresent("name=displayCartInPayPal"),"No express PayPal checkbox for displaying cart in PayPal in mini cart");
        $this->assertTrue($this->isTextPresent("Display cart in PayPal"),"No express PayPal text about displaying cart in PayPal in mini cart");
        $this->assertTrue($this->isElementPresent("id=paypalExpressCheckoutMiniBasketImage"),"No express PayPal image in mini cart");
        $this->assertTrue($this->isElementPresent("id=paypalHelpIconMiniBasket"),"No express PayPal checkbox help button for displaying cart in PayPal in mini cart");

        // Open ECS in details page
        $this->clickAndWait("id=paypalExpressCheckoutDetailsButton");
        $this->assertTrue($this->isElementPresent("//div[@id='popupECS']/p"),"No Express PayPal popup appears");
        $this->assertTrue($this->isElementPresent("id=actionNotAddToBasketAndGoToCheckout"),"No button in PayPal popup");
        $this->assertTrue($this->isElementPresent("id=actionAddToBasketAndGoToCheckout"),"No button in PayPal popup");
        $this->assertTrue($this->isElementPresent("link=open current cart"),"No link open current cart in popup");
        $this->assertTrue($this->isElementPresent("//div[@id='popupECS']/div/div/button"),"No cancel button in PayPal popup");

        // Select add to basket and go to checkout
        $this->clickAndWait("id=actionAddToBasketAndGoToCheckout");
        $this->assertTrue($this->isTextPresent("Item price: €0.99"));
        $this->assertTrue($this->isTextPresent("exact:Quantity: 2"));

        // Cancel order
        $this->clickAndWait("name=cancel_return");
        // Go to checkout with PayPal  with same amount in basket
        $this->clickAndWait("id=paypalExpressCheckoutDetailsButton");
        $this->clickAndWait("id=actionNotAddToBasketAndGoToCheckout");
        $this->assertTrue($this->isTextPresent("Item price: €0.99"),"Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("€1.98"),"Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("exact:Quantity: 2"),"Item quantity doesn't mach ot didn't displayed");

        // Cancel order
        $this->clickAndWait("name=cancel_return");

        // Go to home page and purchase via PayPal
        $this->assertTrue($this->isTextPresent("2 x Test product 1"),"Item quantity doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("1,98 €"),"Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isElementPresent("id=paypalHelpIconMiniBasket"));
        $this->assertTrue($this->isElementPresent("id=paypalExpressCheckoutMiniBasketBox"));
        $this->assertTrue($this->isElementPresent("name=displayCartInPayPal"));
        $this->clickAndWait("id=paypalExpressCheckoutMiniBasketImage");

        $this->assertTrue($this->isTextPresent("€1.98"),"Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("exact:Quantity: 2"),"Item quantity doesn't mach ot didn't displayed");
        $this->waitForItemAppear( "id=submitLogin" );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=shipping_method" );
        $this->assertTrue( $this->isTextPresent( "Test product 1"), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isTextPresent( "€1,98" ),"Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("exact:Anzahl: 2"),"Item quantity doesn't mach ot didn't displayed");
        $this->clickAndWait( "id=continue" );
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Purchased product name is not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Item #: 1001" ), "Product number not displayed in last order step" );
        // $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle() );
        $this->assertEquals("1,98 €", $this->getText("basketGrandTotal"),"Grand total price changed  or didn't displayed");
        $this->assertTrue( $this->isTextPresent( "PayPal" ), "Payment method not displayed in last order step" );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );
    }

    /**
     * testing paypal express button
     * @group paypal_standalone
     */
    public function testPayPalExpress2()
    {
        //Testing when user is logged in
        $this->openShop();
        $this->switchLanguage( "Deutsch" );
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "Deutsch" );
        $this->waitForElementPresent( "paypalExpressCheckoutButton" );
        $this->assertTrue( $this->isElementPresent( "paypalExpressCheckoutButton" ) );
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->waitForElementPresent( "paypalExpressCheckoutButton" );
        $this->assertTrue( $this->isElementPresent( "paypalExpressCheckoutButton" ), "PayPal express button not displayed in the cart" );

        //Go to PayPal express
        $this->click( "name=paypalExpressCheckoutButton" );
        $this->waitForItemAppear( "id=submitLogin" );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->click( "id=continue" );
        $this->waitForItemAppear( "id=continue" );
        $this->click( "id=continue" );

        $this->clickAndWait( "continue" );
        $this->assertEquals("0,99 €", $this->getText("basketGrandTotal"),"Grand total price changed or didn't displayed");
        $this->assertEquals( "Adressen Ändern Rechnungsadresse E-Mail: testing_account@oxid-esales.com SeleniumTestCase Äß'ü Testing acc for Selenium Herr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Deutschland", $this->clearString( $this->getText( "orderAddress" ) ) );

        //Testing when user is not logged in
        $this->openShop();
        $this->switchLanguage( "Deutsch" );
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "Deutsch" );

        $this->waitForElementPresent( "paypalExpressCheckoutButton" );
        $this->assertTrue( $this->isElementPresent( "paypalExpressCheckoutButton" ), "PayPal express button not displayed in the cart" );

        //Go to PayPal express
        $this->click( "name=paypalExpressCheckoutButton" );
        $this->waitForItemAppear( "id=submitLogin" );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=shipping_method" );
        $this->click( "id=continue" );
        $this->waitForItemAppear( "id=displayShippingAmount" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isTextPresent( "€0,99" ) );
        $this->click( "id=continue" );

        //User is on the 4th page
        $this->waitForText( "Bitte prüfen Sie alle Daten, bevor Sie Ihre Bestellung abschließen!" );
        $this->assertEquals("0,99 €", $this->getText("basketGrandTotal"),"Grand total price changed or didn't displayed");
        $this->assertEquals( "Zahlungsart Ändern PayPal", $this->clearString( $this->getText( "orderPayment" ) ) );
        $this->assertEquals( "Adressen Ändern Rechnungsadresse E-Mail: {$this->getLoginDataByName( 'sBuyerLogin' )} {$this->getLoginDataByName( 'sBuyerFirstName' )} {$this->getLoginDataByName( 'sBuyerLastName' )} ESpachstr. 1 79111 Freiburg Deutschland", $this->clearString( $this->getText( "orderAddress" ) ) );
        $this->assertEquals( "Versandart Ändern Test S&H set", $this->clearString( $this->getText( "orderShipping" ) ) );
        $this->clickAndWait( "//button[text()='Zahlungspflichtig bestellen']", null, 90 );
        $this->assertTrue( $this->isTextPresent( "Vielen Dank für Ihre Bestellung im OXID eShop" ), "Order is not finished successful" );

        //Checking if order is saved in Admin
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->openTab( "link=2", "setfolder" );
        $this->frame( "list" );
        $this->openTab( "link=Main", "setDelSet" );
        $this->assertEquals( "Test S&H set", $this->getSelectedLabel( "setDelSet" ) );

    }

    /**
     * testing if express button is not visible when PayPal is not active
     * @group paypal_standalone
     */
    public function testPayPalExpressWhenPayPalInactive()
    {
        //Disable PayPal
        $this->loginAdminForModule( "Extensions", "Modules" );
        $this->openTab( "link=PayPal" );
        $this->frame( "edit" );
        $this->clickAndWait( "module_deactivate" );
        $this->assertTrue( $this->isElementPresent( "id=module_activate" ), "The button Activate module is not displayed " );

        //After PayPal module is deactivated,  PayPal express button should  not be available in basket
        $this->openShop();
        $this->switchLanguage( "Deutsch" );
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "Deutsch" );
        $this->assertFalse( $this->isElementPresent( "paypalExpressCheckoutBox" ), "PayPal should not be displayed, because Paypal is deactivated" );
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertFalse( $this->isElementPresent( "paypalExpressCheckoutBox" ), "PayPal should not be displayed, because Paypal is deactivated" );

        //On 2nd step
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->waitForText( "Lieferadresse" );

        //On 3rd step
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->waitForText( "Bitte wählen Sie Ihre Versandart" );
        $this->selectAndWait( "sShipSet", "label=Standard" );
        $this->assertEquals( "Kosten: 3,90 €", $this->getText( "shipSetCost" ) );
        $this->assertFalse( $this->isElementPresent( "//input[@value='oxidpaypal']" ) );
        $this->selectAndWait( "sShipSet", "label=Test S&H set" );
        $this->assertFalse( $this->isElementPresent( "//input[@value='oxidpaypal']" ) );

        // clearing cache as disabled module is cached
        $this->clearTmp();
    }

    /**
     * testing when payment method has unassigned country Germany, user is not login to the shop, and purchase as PayPal user from Germany
     * @group paypal_standalone
     */
    public function testPayPalPaymentForGermany()
    {
        //Make an order with PayPal
        $this->openShop();
        $this->switchLanguage( "Deutsch" );
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "Deutsch" );
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->click( "userChangeAddress" );
        $this->waitForItemAppear( "order_remark" );
        $this->type( "order_remark", "Testing paypal" );
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->click( "name=sShipSet" );
        $this->select( "name=sShipSet", "label=Test S&H set" );
        $this->waitForItemAppear( "payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Weiter zum nächsten Schritt']" );
        $this->waitForElement( "login.x" );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->click( "id=continue" );
        $this->waitForText( "Bitte prüfen Sie alle Daten, bevor Sie Ihre Bestellung abschließen!" );
        $this->clickAndWait( "//button[text()='Zahlungspflichtig bestellen']", null, 90 );
        $this->assertTrue( $this->isTextPresent( "Vielen Dank für Ihre Bestellung im OXID eShop" ), "The order not finished successful" );

        //Go to an admin and check this order nr
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.1']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.1']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2", "setfolder" );
        $this->assertTextPresent( "Internal Status: OK" );
        $this->frame( "edit" );
        $this->assertTrue( $this->isTextPresent( "Order No.: 2" ), "Order number is not displayed in admin" );

        //Check user's order information in admin
        $this->assertEquals( "1 *", $this->getText( "//table[2]/tbody/tr/td[1]" ), "Quantity of product is not correct in admin" );
        $this->assertEquals( "Test product 1", $this->getText( "//td[3]" ), "Purchased product name is not displayed in admin" );
        $this->assertEquals( "0,99 EUR", $this->getText( "//td[5]" ), "Unit price is not displayed in admin" );
        $this->assertEquals( "0,99", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->frame( "list" );
        $this->openTab( "link=Products", "//input[@value='Update']" );
        $this->assertEquals( "1", $this->getValue( "//tr[@id='art.1']/td[1]/input" ), "Quantity of product is not correct in admin" );
        $this->assertEquals( "0,99 EUR", $this->getText( "//tr[@id='art.1']/td[7]" ), "Unit price is not displayed in admin" );
        $this->assertEquals( "0,99 EUR", $this->getText( "//tr[@id='art.1']/td[8]" ), "Total price is not displayed in admin" );

        //Update product quantities to 5
        $this->type( "//tr[@id='art.1']/td[1]/input", "5" );
        $this->clickAndWait( "//input[@value='Update']" );
        $this->assertEquals( "0,99 EUR", $this->getText( "//tr[@id='art.1']/td[7]" ), "Unit price is not displayed in admin" );
        $this->assertEquals( "4,95 EUR", $this->getText( "//tr[@id='art.1']/td[8]" ), "Total price is incorrect after update" );
        $this->assertEquals( "4,95", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->frame( "list" );
        $this->openTab( "link=Main", "setDelSet" );
        $this->assertEquals( "Test S&H set", $this->getSelectedLabel( "setDelSet" ), "Shipping method is not displayed in admin" );
        $this->assertEquals( "PayPal", $this->getSelectedLabel( "setPayment" ) );

        //Separate Germany from PayPal payment method and assign United States
        $this->open( shopURL . "/_updateDB.php?filename=unasignCountryFromPayPal.sql" );

        ///Go to make an order but do not finish it
        $this->openShop();

        //Check if PayPal logo in frontend is active in both languages
        $this->assertTrue( $this->isElementPresent( "paypalPartnerLogo" ), "PayPal logo not shown in frontend page" );
        $this->switchLanguage( "Deutsch" );
        $this->assertTrue( $this->isElementPresent( "paypalPartnerLogo" ), "PayPal logo not shown in frontend page" );
        $this->switchLanguage( "English" );

        //Search for the product and add to cart
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );
        $this->waitForElementPresent( "paypalExpressCheckoutButton" );
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Product:Test product 1 is not shown in 1st order step " );
        $this->assertTrue( $this->isElementPresent( "//tr[@id='cartItem_1']/td[3]/div[2]" ), "There product:Test product 1 is not shown in 1st order step" );
        //  $this->assertEquals( "OXID Surf and Kite Shop | Cart | purchase online", $this->getTitle(), "Tittle of the page is incorrect" );
        $this->assertEquals( "Grand total: 0,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Grand Total is not displayed correctly" );
        $this->assertFalse( $this->isTextPresent( "Shipping cost" ), "Shipping cost should not be displayed" );
        $this->assertTrue( $this->isTextPresent( "exact:?" ) );
        $this->storeChecked( "//input[@name='displayCartInPayPal' and @value='1']" );
        $this->assertTrue( $this->isTextPresent( "Display cart in PayPal" ), "An option text:Display cart in PayPal is not displayed" );
        $this->assertTrue( $this->isElementPresent( "name=displayCartInPayPal" ), "An option Display cart in PayPal is not displayed" );

        //Go to PayPal express to make an order
        $this->click( "name=paypalExpressCheckoutButton" );
        $this->waitForItemAppear( "id=submitLogin" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed in PayPal" );

        //Login to PayPal as US user
        $this->_loginToSandbox( $this->getLoginDataByName( 'sBuyerUSLogin' ) );

        //After login to PayPal check does all necessary element displayed correctly
        $this->click( "id=submitLogin" );
        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=displayShippingAmount" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Purchased product is not displayed in basket in PayPal" );
        $this->assertFalse( $this->isTextPresent( "Shipping method: Stadard Price:€6.90 EUR" ), "Standard Price:€6.90 EUR shipping cost for this user should not be displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerUSLogin' ) ), "User login name is not displayed in PayPal " );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Purchased product name is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Item number: 1001" ), "Product number is not displayed in PayPal " );
        $this->assertTrue( $this->isTextPresent( "Quantity: 1" ), "Product quantities is not displayed in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=shippingHandling" ), "Shipping cost is not calculated in PayPal" );

        //Go to shop
        $this->waitForTextPresent( "Total €7.89 EUR" );
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );

        //Now user is on the 1st "cart" step with an error message:
        $this->assertTrue( $this->isTextPresent( "Based on your choice in PayPal Express Checkout, order total has changed. Please check your shopping cart and continue. Hint: for continuing with Express Checkout press Express Checkout button again." ), "An error message is not dispayed in shop 1st order step" );
        $this->assertTrue( $this->isElementPresent( "id=basketRemoveAll" ), "an option Remove is not displayed in 1st cart step" );
        $this->assertTrue( $this->isElementPresent( "id=basketRemove" ), "an option All is not displayed in 1st cart step" );
        $this->assertTrue( $this->isElementPresent( "id=basketUpdate" ), "an option Update is not displayed in 1st cart step" );
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//tr[@id='cartItem_1']/td[3]/div[2]" ), "There product:Test product 1 is not shown in 1st order step" );
        //  $this->assertEquals( "OXID Surf and Kite Shop | Cart | purchase online", $this->getTitle(), " Title in 1st order step is incorrect" );
        $this->assertEquals( "Grand total: 7,73 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->assertEquals( "Shipping cost 6,90 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Shipping cost is not displayed correctly" );

        $this->assertTrue( $this->isTextPresent( "Display cart in PayPal" ), "Text:Display cart in PayPal for checkbox not displayed" );
        $this->assertTrue( $this->isElementPresent( "name=displayCartInPayPal" ), "Checkbox:Display cart in PayPal not displayed in cart" );
        $this->assertTrue( $this->isElementPresent( "paypalExpressCheckoutButton" ), "PayPal express button not displayed in the cart" );

        //Go to next step and change country to Germany
        $this->click( "xpath=(//button[@type='submit'])[3]" );
        $this->waitForItemAppear( "id=userChangeAddress" );
        $this->click( "id=userChangeAddress" );
        $this->click( "id=invCountrySelect" );
        $this->select( "id=invCountrySelect", "label=Germany" );
        $this->click( "id=userNextStepTop" );
        $this->waitForPageToLoad( "30000" );

        //Check if PayPal is not displayed for Germany
        $this->assertEquals( "Test S&H set Standard Example Set1: UPS 48 hours Example Set2: UPS Express 24 hours", $this->getText( "name=sShipSet" ), "Not all shipping methods are available in drop down" );
        $this->assertEquals( "COD (Cash on Delivery)", $this->getText( "//form[@id='payment']/dl[5]/dt/label/b" ), "Wrong payment method is shown" );
        $this->assertTrue( $this->isTextPresent( "COD (Cash on Delivery)" ), "Wrong payment method is shown" );
        $this->assertFalse( $this->isTextPresent( "PayPal (0,00 €)" ), "PayPal should not be displayed as payment method" );

        //Also check if PayPal not displayed in the 1st cart step
        $this->click( "link=1. Cart" );
        $this->waitForPageToLoad( "30000" );
        $this->assertTrue( $this->isTextPresent( "Display cart in PayPal" ), "Text:Display cart in PayPal for checkbox not displayed" );
        $this->assertTrue( $this->isElementPresent( "name=displayCartInPayPal" ), "Checkbox:Display cart in PayPal not displayed in cart" );
        $this->assertTrue( $this->isElementPresent( "paypalExpressCheckoutButton" ), "PayPal express button not displayed in the cart" );

        ///Go to admin and check previous order status and check if new order didn't appear in admin and it didn't overwritten on previous order.
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.1']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.1']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2", "setfolder" );

        $this->assertTextPresent( "Internal Status: OK" );
        $this->assertTrue( $this->isTextPresent( "Order No.: 2" ), "Order number is not displayed in admin" );

        //Check user's order nr 2 information in admin
        $this->assertEquals( "5 *", $this->getText( "//table[2]/tbody/tr/td[1]" ), "Product quantities are incorrect in admin" );
        $this->assertEquals( "Test product 1", $this->getText( "//td[3]" ), "Product name is incorrect in admin" );
        $this->assertEquals( "4,95 EUR", $this->getText( "//td[5]" ) );
        $this->assertEquals( "4,95", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ), "Product total displayed " );
        $this->frame( "list" );
        $this->openTab( "link=Products", "//input[@value='Update']" );
        $this->assertEquals( "5", $this->getValue( "//tr[@id='art.1']/td[1]/input" ), "Product quantities are incorrect in admin" );
        $this->assertEquals( "0,99 EUR", $this->getText( "//tr[@id='art.1']/td[7]" ), "Product price is incorrect in admin" );
        $this->assertEquals( "4,95 EUR", $this->getText( "//tr[@id='art.1']/td[8]" ), "Product total is incorrect in admin" );
        $this->assertEquals( "4,95", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ), "Product total is incorrect in admin" );
        $this->frame( "list" );
        $this->openTab( "link=Main", "setDelSet" );
        $this->assertEquals( "Test S&H set", $this->getSelectedLabel( "setDelSet" ), "Shipping method is incorrect in admin" );

        //Go to basket and make an order,
        //TODO there is a bug #4501: after updating quantities in admin in table saves this info and now then user goes to
        //cart there are left 5 quantities instead of 1, then this bug will be fixed need to change selenium
        $this->open( shopURL . "_cc.php" );
        $this->openShop();
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );

        $this->assertEquals( "Grand total: 0,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Grand total is not displayed correctly" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertTrue( $this->isElementPresent( "id=showShipAddress" ), "Shipping address is not displayed in 2nd order step" );
        $this->click( "id=userNextStepBottom" );
        $this->waitForElementPresent( "paymentNextStepBottom" );
        $this->assertTrue( $this->isElementPresent( "name=sShipSet" ), "Shipping method drop down is not shown" );
        $this->assertEquals( "Test S&H set", $this->getSelectedLabel( "sShipSet" ), "Wrong shipping method is selected, should be:Test S&H set " );
        $this->click( "id=paymentNextStepBottom" );

        //go to last order step, check if payment method is not PayPal
        $this->waitForElementPresent( "orderAddress" );
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Product name is not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Item #: 1001" ), "Product number not displayed in last order step" );
        $this->assertEquals( "Shipping cost 0,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Shipping cost is not displayed correctly" );
        //   $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle(), "Page tittle is incorect in last order step" );
        $this->assertEquals( "Surcharge Payment Method 7,50 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Payment price is not displayed in carts" );
        $this->assertEquals( "Grand total: 12,45 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ), "Grand total is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "Test S&H set" ) );
        // $this->assertFalse($this->isTextPresent("PayPal"));
        $this->assertTrue( $this->isTextPresent( "COD" ) );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        // After successful purchase, go to admin and check order status
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.2']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.2']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->assertEquals( "0000-00-00 00:00:00", $this->getText( "//tr[@id='row.1']/td[4]" ) );
        $this->openTab( "link=3", "setfolder" );
        $this->assertTextPresent( "Internal Status: OK" );
        $this->assertTextPresent( "Order No.: 3", "Order number is not displayed in admin" );
        $this->assertEquals( "5 *", $this->getText( "//table[2]/tbody/tr/td[1]" ) );
        $this->assertEquals( "Test product 1", $this->getText( "//td[3]" ), "Purchased product name is not displayed in Admin" );
        $this->assertEquals( "12,45", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->frame( "list" );
        $this->openTab( "link=Products", "//input[@value='Update']" );
        $this->assertEquals( "7,50", $this->getText( "//table[@id='order.info']/tbody/tr[6]/td[2]" ), "charges of payment method is not displayed" );
        $this->assertEquals( "0,79", $this->getText( "//table[@id='order.info']/tbody/tr[4]/td[2]" ), "VAT is not displayed" );
        $this->assertEquals( "4,16", $this->getText( "//table[@id='order.info']/tbody/tr[3]/td[2]" ), "Product Net price is not displayed" );
        $this->frame( "list" );
        $this->openTab( "link=Main", "setDelSet" );
        $this->assertEquals( "Test S&H set", $this->getSelectedLabel( "setDelSet" ), "Shipping method is not displayed in admin" );
        $this->assertEquals( "COD (Cash on Delivery)", $this->getSelectedLabel( "setPayment" ), "Payment method is not displayed in admin" );

    }


    /**
     * testing different countries with shipping rules assigned to this countries
     * @group paypal_standalone
     */
    public function testPayPalPaymentForLoginUser()
    {
        $this->openShop();

        //Search for the product and add to cart
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );

        //Login to shop and go to the basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->waitForElementPresent( "paypalExpressCheckoutButton", "PayPal express button not displayed in the cart" );
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//tr[@id='cartItem_1']/td[3]/div[2]" ) );
        // $this->assertEquals( "OXID Surf and Kite Shop | Cart | purchase online", $this->getTitle() );
        $this->assertEquals( "Grand total: 0,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "Shipping cost" ), "Shipping cost is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "exact:?" ) );
        $this->storeChecked( "//input[@name='displayCartInPayPal' and @value='1']" );
        $this->assertTrue( $this->isTextPresent( "Display cart in PayPal" ), "Text:Display cart in PayPal for checkbox not displayed" );
        $this->assertTrue( $this->isElementPresent( "name=displayCartInPayPal" ), "Checkbox:Display cart in PayPal not displayed" );

        //Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->click("name=paypalExpressCheckoutButton");
        $this->waitForItemAppear("id=submitLogin");
        $this->assertTrue($this->isTextPresent("Test product 1"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Item number: 1001"), "Product number not displayed in paypal ");
        $this->assertFalse($this->isTextPresent("Grand total: €0,99"), "Grand total should not be displayed");

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=displayShippingAmount" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€0,99" ), "Product price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "exact:Versandkosten:" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandmethode: Test S&H set: €0,00 EUR" ), "Shipping method is not shown in PayPal" );
        // $this->assertEquals("Testing user acc Äß&amp;#039;ü PayPal Äß&amp;#039;ü Musterstr. Äß&#039;ü 1 79098 Musterstadt Äß&#039;ü Deutschland Versandmethode: Test S&H set: €0,00 EUR", $this->clearString($this->getText("//div[@class='inset confidential']")));
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1001" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,99" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=shippingHandling" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Gesamtbetrag €0,99 EUR" ), "Total price is not displayed in PayPal" );

        //Cancel order and go back to the shop with uncecked option
        $this->click( "name=cancel_return" );
        $this->waitForElementPresent( "paypalExpressCheckoutButton" );
        $this->uncheck( "//input[@name='displayCartInPayPal']" );

        //Go to PayPal via PayPal Express without  "Display cart in PayPal"
        $this->click( "name=paypalExpressCheckoutButton" );
        $this->waitForItemAppear( "id=submitLogin" );
        $this->assertFalse( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed in PayPal" );
        $this->assertfalse( $this->isTextPresent( "Item number: 1001" ), "Item number should not be displayed in PayPal" );
        $this->assertFalse( $this->isTextPresent( "Grand total: €0,99" ), "Grand total should not be displayed in PayPal" );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=displayShippingAmount" );
        $this->assertFalse( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€0,99" ), "Product price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "exact:Versandkosten:" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandmethode: Test S&H set: €0,00 EUR" ) );
        // $this->assertEquals("Testing user acc Äß&amp;#039;ü PayPal Äß&amp;#039;ü Musterstr. Äß&#039;ü 1 79098 Musterstadt Äß&#039;ü Deutschland Versandmethode: Test S&H set: €0,00 EUR", $this->clearString($this->getText("//div[@class='inset confidential']")));
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        //$this->assertTrue($this->isTextPresent("Artikelnummer: 1001"));
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,99" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=shippingHandling" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Gesamtbetrag €0,99 EUR" ), "Total price is not displayed in PayPal" );

        //Change delivery address with country which has not PayPal assigned as payment method inside Paypal

        $this->click( "id=changeAddressButton" );
        $this->waitForItemAppear( "id=addShipAddress" );

        //checking if there is already Belgium address
        if ( !$this->isTextPresent( "Test address in Belgium 15, Antwerp, Belgien", "" ) ) {
            // adding new address (Belgium) to address list
            $this->clickAndWait( "id=addShipAddress" );
            $this->select( "id=country_code", "label=Belgien" );
            $this->type( "id=shipping_address1", "Test address in Belgium 15" );
            $this->type( "id=shipping_city", "Antwerp" );
            //returning to address list
            $this->click( "//input[@id='continueBabySlider']" );
        }
        // selecting Belgium address
        $this->click( "//label[@class='radio' and contains(.,'Test address in Belgium 15, Antwerp, Belgien')]/input" );

        $this->click( "//input[@id='continueBabySlider']" );

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=messageBox" );
        $this->waitForTextPresent( "Gesamtbetrag €0,99 EUR" );
        $this->waitForTextPresent( "PayPal Testshop versendet nicht an diesen Ort. Verwenden Sie eine andere Adresse." );

        //Cancel paying with PayPal and back to the shop
        $this->click( "name=cancel_return" );
        $this->waitForElementPresent( "paypalExpressCheckoutButton" );
        $this->assertFalse( $this->isTextPresent( "Continue to the next step" ), "Unexpected return to basket, should be returned to home page." );
        $this->openBasket( "English" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Check exist user address
        $this->assertEquals( "E-mail: testing_account@oxid-esales.com SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany", $this->clearString( $this->getText( "//ul[@id='addressText']//li" ) ), "User address is incorect" );

        //Change to new one which has not PayPal assigned as payment method inside PayPal
        $this->click( "userChangeAddress" );
        $this->waitForItemAppear( "invCountrySelect" );

        $this->select( "invCountrySelect", "label=United States" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->clickAndWait( "link=1. Cart" );
        $this->assertFalse( $this->isElementPresent( "paypalPartnerLogo" ), "PayPal logo should not be displayed fot US" );

        //Created additional 3 shipping methods with shipping cost rules for Austria
        if ( OXID_VERSION_EE ):
            $this->open( shopURL . "/_updateDB.php?filename=newDeliveryMethod_ee.sql" );
        endif;
        if ( !OXID_VERSION_EE ):
            $this->open( shopURL . "/_updateDB.php?filename=newDeliveryMethod_pe.sql" );
        endif;

        $this->openBasket( "English" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Change country to Austria
        $this->click( "userChangeAddress" );
        $this->waitForItemAppear( "invCountrySelect" );
        $this->select( "invCountrySelect", "label=Austria" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Check all available shipping methods
        $this->assertTrue( $this->isTextPresent( "PayPal" ) );
        $this->assertTrue( $this->isTextPresent( "exact:Charges: 0,50 €" ) );
        $this->assertTrue( $this->isTextPresent( "Test Paypal:6 hour Test Paypal:12 hour Standard Example Set1: UPS 48 hours Example Set2: UPS Express 24 hours" ), "Not all available shipping methods is displayed" );

        //Go to 1st step and make an order via PayPal express
        $this->clickAndWait("link=1. Cart");
        $this->click("name=paypalExpressCheckoutButton");
        $this->waitForItemAppear("id=submitLogin");
        $this->assertTrue($this->isTextPresent("Test product 1"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Item number: 1001"), "Product number not displayed in the 1st order step ");

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=displayShippingAmount" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€0,99" ), "Product price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandkosten:€0,50" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,99" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=shippingHandling" ), "Shipping cost is not calculated in PayPal" );
        $this->waitForTextPresent( "Gesamtbetrag €1,49 EUR" );
        $this->select( "id=shipping_method", "label=Test Paypal:12 hour Price: €0,90 EUR" );
        $this->waitForTextPresent( "Gesamtbetrag €1,89 EUR" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€0,99" ), "Product price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandkosten:€0,90" ), "Shipping cost is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,99" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Gesamtbetrag €1,89 EUR" ), "Total price is not displayed in PayPal" );

        //Go to shop
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );

        //Check are all info in the last order step correct
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Purchased product name is not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Item #: 1001" ), "Product number not displayed in last order step" );
        $this->assertEquals( "Shipping cost 0,90 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Shipping cost is not displayed correctly" );
        // $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle() );
        $this->assertEquals( "Grand total: 1,89 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "Test Paypal:12 hour" ), "Shipping method not displayed in order " );
        $this->assertTrue( $this->isTextPresent( "PayPal" ), "Payment method not displayed in last order step" );
        $this->assertFalse( $this->isTextPresent( "COD" ), "Wrong payment method displayed in last order step" );

        //Go back to 1st order step and change product quantities to 20
        $this->clickAndWait( "link=1. Cart" );

        $this->assertEquals( "Total Products (incl. tax): 0,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ), "Total price not displayed in basket" );
        $this->assertEquals( "Total Products (net): 0,83 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ), "Total price not displayed in basket" );
        $this->assertEquals( "Grand total: 1,89 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->type( "id=am_1", "20" );
        $this->click( "id=basketUpdate" );
        sleep( 3 );
        $this->assertEquals( "Total Products (incl. tax): 19,80 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ), "Total price not displayed in basket" );
        $this->assertEquals( "Total Products (net): 16,64 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ), "Total price not displayed in basket" );
        $this->assertEquals( "Grand total: 20,60 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );

        //Go to PayPal to make an order
        $this->click("name=paypalExpressCheckoutButton");
        $this->waitForItemAppear("id=submitLogin");
        $this->assertTrue($this->isTextPresent("Test product 1"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Item number: 1001"), "Product number not displayed in the PayPal");

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=displayShippingAmount" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€19,80" ), "Product price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandkosten:€0,80" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isElementPresent( "id=showname0" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,99" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 20" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isElementPresent( "id=shippingHandling" ), "Shipping cost is not calculated in PayPal" );
        $this->waitForTextPresent( "Gesamtbetrag €19,80 EUR" );
        $this->waitForItemAppear( "id=shipping_method" );
        $this->select( "id=shipping_method", "label=Test Paypal:6 hour Price: €0,40 EUR" );
        $this->waitForTextPresent( "Gesamtbetrag €20,20 EUR" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€19,80" ), "Product price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandkosten:€0,40" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,99" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Gesamtbetrag €20,20 EUR" ), "Total price is not displayed in PayPal" );

        //Go to shop
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );

        //Check are all info in the last order step correct
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Purchased product name is not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Item #: 1001" ), "Product number not displayed in last order step" );
        $this->assertEquals( "Shipping cost 0,40 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Shipping cost is not displayed correctly" );
        // $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle() );
        $this->assertEquals( "Grand total: 20,20 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "Test Paypal:6 hour" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isTextPresent( "PayPal" ), "Payment method not displayed in last order step" );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );
    }

    /**
     * testing ability to change country in standard PayPal
     * @group paypal_standalone
     */
    public function testPayPalStandard()
    {
        //Login to shop and go standard PayPal
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->assertTrue( $this->isTextPresent( "Germany" ), "Users country should be Germany" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->assertTrue( $this->isElementPresent( "//input[@value='oxidpaypal']" ) );
        $this->click( "payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Login to standard PayPal and check ability to change country
        $this->waitForElement( "login.x" );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->assertFalse( $this->isElementPresent( "id=changeAddressButton" ), "In standard PayPal there should be not possibility to change address" );
        $this->click( "id=continue" );
        $this->assertEquals( "Ihre Zahlungsinformationen auf einen Blick - PayPal", $this->getTitle() );
        $this->assertTrue( $this->isTextPresent( "PayPal" ), "Payment method not displayed in last order step" );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

    }


    /**
     * test if payment method PayPal is deactivated in shop backend, the PayPal express button should also disappear.
     * @group paypal_standalone
     */
    public function testPayPalActive()
    {
        // Set PayPal payment inactive.
        $this->open(shopURL."/_updateDB.php?filename=setPayPalPaymentInactive.sql");

        //Go to shop to check is PayPal not visible in front end
        $this->openShop();
        $this->assertFalse( $this->isElementPresent( "paypalPartnerLogo" ), "PayPal logo not shown in frontend page" );
        $this->switchLanguage( "Deutsch" );
        $this->assertFalse( $this->isElementPresent( "paypalPartnerLogo" ), "PayPal logo not shown in frontend page" );
        $this->switchLanguage( "English" );

        //Go to basket and check is express PayPal not visible
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );
        $this->assertFalse( $this->isElementPresent( "paypalExpressCheckoutButton" ), "PayPal express button should be not visible in frontend" );

        //Login to shop and go to the basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertFalse( $this->isElementPresent( "paypalExpressCheckoutButton" ), "PayPal express button should be not visible in frontend" );
    }


    /**
     * test if discounts working correct with PayPal.
     * @group paypal_standalone
     */
    public function testPayPalDiscountsCategory()
    {
        // Add vouchers to shop
        if ( OXID_VERSION_EE ):
            $this->open( shopURL . "/_updateDB.php?filename=newDiscounts_ee.sql" );
        endif;
        if ( OXID_VERSION_PE ):
            $this->open( shopURL . "/_updateDB.php?filename=newDiscounts_pe.sql" );
        endif;
        //Go to shop and add product
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "1000" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );

        //Login to shop and go to basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertTrue( $this->isTextPresent( "Test product 0" ) );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isTextPresent( "+1" ) );
        $this->assertEquals( "5,00 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );
        $this->assertEquals( "5,00 € \n10,00 €", $this->getText( "//tr[@id='cartItem_1']/td[6]" ), "price with discount not shown in basket" );
        // Go to 2nd step
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to 3rd step and select PayPal as payment method
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->waitForItemAppear( "id=payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to PayPal
        $this->waitForItemAppear("id=submitLogin");
        $this->assertEquals("Pay with a PayPal account - PayPal", $this->getTitle());
        $this->assertTrue($this->isTextPresent("€5,00"));
        $this->assertTrue($this->isTextPresent("€0,00"));
        $this->assertEquals("Total €5,00 EUR", $this->getText("//div[@id='miniCart']/div[3]/ul/li/span"));
        $this->assertTrue($this->isTextPresent("Total €5,00 EUR"));

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1000" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €5,00" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1001" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €0,00", $this->getText( "//li[@id='multiitem1']/ul[2]/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[2]/li[4]" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "€5,00" ) );
        $this->assertEquals( "Gesamtbetrag €5,00 EUR", $this->getText( "//div[@id='miniCart']/div[3]/ul/li/span" ), "Total price is not displayed in PayPal" );
        $this->click( "id=continue_abovefold" );

        //Go to shop to finish the order
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->assertTrue( $this->isTextPresent( "Test product 0" ), "Purchased product name is not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed in last order step" );
        $this->assertEquals( "Item #: 1001", $this->getText( "//tr[@id='cartItem_2']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertEquals( "Item #: 1000", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "1 +1" ) );
        $this->assertEquals( "4,20 €", $this->getText( "basketTotalProductsNetto" ), "Neto price changed or didn't displayed" );
        $this->assertEquals( "plus VAT 19% Amount: 0,80 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "5,00 €", $this->getText( "basketTotalProductsGross" ), "Bruto price changed  or didn't displayed" );
        $this->assertEquals( "0,00 €", $this->getText( "basketDeliveryGross" ), "Shipping price changed  or didn't displayed" );
        $this->assertEquals( "5,00 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );

        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        //Go to admin and check the order
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.1']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.1']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2", "setfolder" );
        $this->frame( "edit" );
        $this->assertTrue( $this->isTextPresent( "Internal Status: OK" ) );
        $this->assertEquals( "5,00 EUR", $this->getText( "//td[5]" ) );
        $this->assertEquals( "Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.com", $this->clearString( $this->getText( "//td[1]/table[1]/tbody/tr/td[1]" ) ) );
        $this->assertEquals( "5,00", $this->getText( "//table[@id='order.info']/tbody/tr[1]/td[2]" ) );
        $this->assertEquals( "- 0,00", $this->getText( "//table[@id='order.info']/tbody/tr[2]/td[2]" ) );
        $this->assertEquals( "4,20", $this->getText( "//table[@id='order.info']/tbody/tr[3]/td[2]" ) );
        $this->assertEquals( "0,80", $this->getText( "//table[@id='order.info']/tbody/tr[4]/td[2]" ) );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[6]/td[2]" ) );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[1]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[2]" ), "line with discount info is not displayed" );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "PayPal", $this->getText( "//table[4]/tbody/tr[1]/td[2]" ), "Payment method not displayed in admin" );
        $this->assertEquals( "Test S&H set", $this->getText( "//table[4]/tbody/tr[2]/td[2]" ), "Shipping method is not displayed in admin" );
    }

    /**
     * test if few different discounts working correct with PayPal.
     * @group paypal_standalone
     */
    public function testPayPalDiscountsFromTill()
    {
        // Add vouchers to shop
        if ( OXID_VERSION_EE ):
            $this->open( shopURL . "/_updateDB.php?filename=newDiscounts_ee.sql" );
        endif;
        if ( OXID_VERSION_PE ):
            $this->open( shopURL . "/_updateDB.php?filename=newDiscounts_pe.sql" );
        endif;

        //Go to shop and add product
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "1004" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );

        //Login to shop and go to basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertTrue( $this->isTextPresent( "Test product 4" ) );

        $this->assertEquals( "Discount discount from 10 till 20", $this->getText( "//div[@id='basketSummary']/table/tbody/tr[2]/th" ) );
        $this->assertEquals( "-0,30 €", $this->getText( "//div[@id='basketSummary']/table/tbody/tr[2]/td" ) );
        $this->assertEquals( "Grand total: 14,70 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ), "Grand total is not displayed correctly" );

        // Go to 2nd step
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to 3rd step and select PayPal as payment method
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->waitForItemAppear( "id=payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to PayPal
        $this->waitForItemAppear("id=submitLogin");
        $this->assertEquals("Pay with a PayPal account - PayPal", $this->getTitle());
        $this->assertTrue($this->isTextPresent("€15,00"));
        $this->assertTrue($this->isTextPresent("€0,00"));
        $this->assertEquals("-€0,30", $this->getText("//div[@id='miniCart']/div[2]/ul/li[2]/span"));
        $this->assertEquals("Total €14,70 EUR", $this->getText("//div[@id='miniCart']/div[3]/ul/li/span"));

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );

        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1004", "//li[@id='multiitem1']/ul[1]" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €15,00", "//li[@id='multiitem1']/ul[1]" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1", "//li[@id='multiitem1']/ul[1]" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1001", "//li[@id='multiitem1']/ul[2]" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,00", "//li[@id='multiitem1']/ul[2]" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1", "//li[@id='multiitem1']/ul[2]" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€15,00" ) );
        $this->assertTrue( $this->isTextPresent( "Versandrabatt -€0,30", "//div[@id='miniCart']" ) );
        $this->assertTrue( $this->isTextPresent( "Gesamtbetrag €14,70 EUR", "//div[@id='miniCart']" ) );
        $this->click( "id=continue_abovefold" );

        //Go to last step to check the order
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->assertTrue( $this->isTextPresent( "Test product 4" ), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed" );
        $this->assertEquals( "Item #: 1004", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertEquals( "Item #: 1001", $this->getText( "//tr[@id='cartItem_2']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "1 +1" ) );
        $this->assertEquals( "-0,30 €", $this->getText( "//div[@id='basketSummary']/table/tbody/tr[2]/td" ) );

        $this->assertEquals( "Total Products (incl. tax): 15,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "Discount discount from 10 till 20 -0,30 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "Total Products (net): 12,35 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,35 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ) );
        $this->assertEquals( "Shipping cost 0,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Shipping cost is not displayed correctly" );
        $this->assertEquals( "Grand total: 14,70 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ), "Grand total is not displayed correctly" );

        //Go back to 1st order step and change product quantities to 3
        $this->clickAndWait( "link=1. Cart" );
        $this->type( "id=am_1", "3" );
        $this->click( "id=basketUpdate" );
        sleep( 5 );
        $this->assertEquals( "Grand total: 42,75 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ), "Grand total is not displayed correctly" );
        $this->assertEquals( "Discount discount from 20 till 50", $this->getText( "//div[@id='basketSummary']/table/tbody/tr[2]/th" ) );
        $this->assertEquals( "-2,25 €", $this->getText( "//div[@id='basketSummary']/table/tbody/tr[2]/td" ) );
        // Go to 2nd step
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to 3rd step and select PayPal as payment method
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->waitForItemAppear( "id=payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to PayPal
        $this->waitForItemAppear("id=submitLogin");
        $this->assertEquals("Pay with a PayPal account - PayPal", $this->getTitle());
        $this->assertTrue($this->isTextPresent("Test product 4€45,00", "//div[@id='miniCart']"));
        $this->assertTrue($this->isTextPresent("Test product 1€0,00", "//div[@id='miniCart']"));
        $this->assertTrue($this->isTextPresent("Item total €45,00", "//div[@id='miniCart']"));
        $this->assertTrue($this->isTextPresent("Shipping discount -€2,25", "//div[@id='miniCart']"));

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );

        $this->assertTrue( $this->isTextPresent( "Test product 4€45,00", "//li[@id='multiitem1']/ul[1]" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1004", "//li[@id='multiitem1']/ul[1]" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €15,00", "//li[@id='multiitem1']/ul[1]" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 3", "//li[@id='multiitem1']/ul[1]" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Test product 1€0,00", "//li[@id='multiitem1']/ul[2]" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1001", "//li[@id='multiitem1']/ul[2]" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,00", "//li[@id='multiitem1']/ul[2]" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1", "//li[@id='multiitem1']/ul[2]" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandrabatt -€2,25", "//div[@id='miniCart']" ) );
        $this->assertTrue( $this->isTextPresent( "Gesamtbetrag €42,75 EUR", "//div[@id='miniCart']" ) );
        $this->click( "id=continue_abovefold" );

        //Go to shop to finish the order
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->assertTrue( $this->isTextPresent( "Test product 4" ), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed" );
        $this->assertEquals( "Item #: 1004", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertEquals( "Item #: 1001", $this->getText( "//tr[@id='cartItem_2']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "1 +1" ) );
        $this->assertEquals( "-2,25 €", $this->getText( "//div[@id='basketSummary']/table/tbody/tr[2]/td" ) );

        $this->assertEquals( "Total Products (incl. tax): 45,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "Discount discount from 20 till 50 -2,25 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "Total Products (net): 35,92 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 6,83 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ) );
        $this->assertEquals( "Shipping cost 0,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Shipping cost is not displayed correctly" );
        $this->assertEquals( "Grand total: 42,75 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ), "Grand total is not displayed correctly" );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        //Go to admin and check the order
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.1']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.1']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2", "setfolder" );
        $this->frame( "edit" );
        $this->assertTrue( $this->isTextPresent( "Internal Status: OK" ) );
        $this->assertEquals( "0,00 EUR", $this->getText( "//td[5]" ) );

        $this->assertEquals( "Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.com", $this->clearString( $this->getText( "//td[1]/table[1]/tbody/tr/td[1]" ) ) );
        $this->assertEquals( "45,00", $this->getText( "//table[@id='order.info']/tbody/tr[1]/td[2]" ) );
        $this->assertEquals( "- 2,25", $this->getText( "//table[@id='order.info']/tbody/tr[2]/td[2]" ) );
        $this->assertEquals( "35,92", $this->getText( "//table[@id='order.info']/tbody/tr[3]/td[2]" ) );
        $this->assertEquals( "6,83", $this->getText( "//table[@id='order.info']/tbody/tr[4]/td[2]" ) );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "42,75", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[1]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[2]" ), "line with discount info is not displayed" );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[6]/td[2]" ) );
        $this->assertEquals( "PayPal", $this->getText( "//table[4]/tbody/tr[1]/td[2]" ), "Payment method not displayed in admin" );
        $this->assertEquals( "Test S&H set", $this->getText( "//table[4]/tbody/tr[2]/td[2]" ), "Shipping method is not displayed in admin" );
    }

    /**
     * test if vouchers working correct with PayPal
     * @group paypal_standalone
     */
    public function testPayPalVouchers()
    {
        // Add vouchers to shop
        if ( OXID_VERSION_EE ):
            $this->open( shopURL . "/_updateDB.php?filename=newVouchers_ee.sql" );
        endif;
        if ( OXID_VERSION_PE ):
            $this->open( shopURL . "/_updateDB.php?filename=newVouchers_pe.sql" );
        endif;

        //Go to shop and add product
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "1003" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );

        //Login to shop and go to basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Grand total: 15,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->type( "name=voucherNr", "111111" );
        $this->clickAndWait( "//button[text()='Submit Coupon']" );
        $this->assertTrue( $this->isTextPresent( "Remove" ) );
        $this->assertTrue( $this->isTextPresent( "Coupon (No. 111111)" ) );
        $this->assertEquals( "Coupon (No. 111111) Remove -10,00 €", $this->getText( "//div[@id='basketSummary']//tr[4]" ) );
        $this->assertEquals( "Grand total: 5,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ), "Grand total is not displayed correctly" );

        // Go to 2nd step
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to 3rd step and select paypla as payment method
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->waitForItemAppear( "id=payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to PayPal
        $this->waitForItemAppear("id=submitLogin");
        $this->assertEquals("Pay with a PayPal account - PayPal", $this->getTitle());
        $this->assertTrue($this->isTextPresent("€15,00"));
        $this->assertEquals("-€10,00", $this->getText("//div[@id='miniCart']/div[2]/ul/li[2]/span"));
        $this->assertEquals("Total €5,00 EUR", $this->getText("//div[@id='miniCart']/div[3]/ul/li/span"));

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1003" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €15,00" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1" ), "Product quantity is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "€10,00" ) );
        //$this->assertEquals("-€10,00", $this->getText("//div[@id='miniCart']/div[2]/ul/li[2]/span"));
        $this->assertTrue( $this->isTextPresent( "-€10,00", "//div[@id='miniCart']" ) );
        $this->assertEquals( "Gesamtbetrag €5,00 EUR", $this->getText( "//div[@id='miniCart']/div[3]/ul/li/span" ), "Total price is not displayed in PayPal" );
        $this->click( "id=continue_abovefold" );

        //Go to shop to finish the order
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Item #: 1003", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );

        $this->assertEquals("Total products (incl. tax): 15,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")));
        $this->assertEquals("Total products (excl. tax): 4,20 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")));
        $this->assertEquals("plus 19% tax, amount: 0,80 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")));
        $this->assertEquals("Shipping costs: 0,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Shipping costs: is not displayed correctly");
        $this->assertEquals("Grand total: 5,00 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[6]")), "Grand total is not displayed correctly");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTrue($this->isTextPresent("Thank you for your order in OXID eShop"), "Order is not finished successful");

        //Go to admin and check the order
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.1']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.1']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2", "setfolder" );
        $this->frame( "edit" );
        $this->assertTrue( $this->isTextPresent( "Internal Status: OK" ) );
        $this->assertEquals( "15,00 EUR", $this->getText( "//td[5]" ) );
        $this->assertEquals( "Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.com", $this->clearString( $this->getText( "//td[1]/table[1]/tbody/tr/td[1]" ) ) );
        $this->assertEquals( "15,00", $this->getText( "//table[@id='order.info']/tbody/tr[1]/td[2]" ) );
        $this->assertEquals( "- 0,00", $this->getText( "//table[@id='order.info']/tbody/tr[2]/td[2]" ) );
        $this->assertEquals( "4,20", $this->getText( "//table[@id='order.info']/tbody/tr[3]/td[2]" ) );
        $this->assertEquals( "0,80", $this->getText( "//table[@id='order.info']/tbody/tr[4]/td[2]" ) );
        $this->assertEquals( "- 10,00", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[6]/td[2]" ) );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->assertEquals( "5,00", $this->getText( "//table[@id='order.info']/tbody/tr[8]/td[2]" ) );

        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[1]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[2]" ), "line with discount info is not displayed" );
        $this->assertEquals( "- 10,00", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "PayPal", $this->getText( "//table[4]/tbody/tr[1]/td[2]" ), "Payment method not displayed in admin" );
        $this->assertEquals( "Test S&H set", $this->getText( "//table[4]/tbody/tr[2]/td[2]" ), "Shipping method is not displayed in admin" );
    }


    /**
     * test if VAT is calculated in PayPal correct with different VAT options set in admins
     * @group paypal_standalone
     */
    public function testPayPalVAT()
    {
        // Change price for PayPal payment methode
        $this->open( shopURL . "/_updateDB.php?filename=vatOptions.sql" );

        // Set on all VAT options
        if ( OXID_VERSION_EE ):
            $this->open(shopURL."/_updateDB.php?filename=testPaypaVAT.sql");
        endif;
        if ( OXID_VERSION_PE ):
            $this->open(shopURL."/_updateDB.php?filename=testPaypaVAT_pe.sql");
        endif;

        //Go to shop and add product
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "1003" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );

        //Login to shop and go to basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Test product 3", $this->getText( "//tr[@id='cartItem_1']/td[3]/div[1]" ) );

        //Added wrapping and card to basket
        $this->click( "id=header" );
        $this->click( "link=add" );
        $this->click( "id=wrapping_a6840cc0ec80b3991.74884864" );
        $this->click( "id=chosen_81b40cf0cd383d3a9.70988998" );
        $this->clickAndWait( "//button[text()='Apply']" );

        $this->assertEquals( "Total Products (net): 15,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,85 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "Total Products (incl. tax): 17,85 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "Shipping (net): 13,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,47 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ) );
        $this->assertEquals( "3,51 €", $this->getText( "basketWrappingGross" ), "Wrapping price changed or didn't displayed" );
        $this->assertEquals( "3,57 €", $this->getText( "basketGiftCardGross" ), "Card price changed or didn't displayed" );
        $this->assertEquals( "40,40 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );

        // Go to 2nd step
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to 3rd step and select PayPal as payment method
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->waitForItemAppear( "id=payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to PayPal
        $this->waitForItemAppear("id=submitLogin");
        $this->assertEquals("Pay with a PayPal account - PayPal", $this->getTitle());
        $this->assertTrue($this->isTextPresent("€17,85"));
        $this->assertTrue($this->isTextPresent("€12,50"));
        $this->assertTrue($this->isTextPresent("€3,51"));
        $this->assertTrue($this->isTextPresent("€3,57"));
        $this->assertEquals("Total €52,90 EUR", $this->getText("//div[@id='miniCart']/div[3]/ul/li/span"));
        $this->assertTrue($this->isTextPresent("Item total €37,43"));
        $this->assertTrue($this->isTextPresent("Shipping and handling:"));
        $this->assertTrue($this->isTextPresent("€15,47"));

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1003" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €17,85", $this->getText( "//li[@id='multiitem1']/ul/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Surcharge Type of Payment" ) );
        $this->assertEquals( "Artikelpreis: €12,50", $this->getText( "//li[@id='multiitem1']/ul[2]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[2]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Giftwrapper" ) );
        $this->assertEquals( "Artikelpreis: €3,51", $this->getText( "//li[@id='multiitem1']/ul[3]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[3]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Greeting Card" ) );
        $this->assertEquals( "Artikelpreis: €3,57", $this->getText( "//li[@id='multiitem1']/ul[4]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[4]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Warenwert€37,43" ), "Product price is not displayed in PayPal" );
        $this->assertEquals( "Gesamtbetrag €52,90 EUR", $this->getText( "//div[@id='miniCart']/div[3]/ul/li/span" ), "Total price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );
        $this->click( "id=continue_abovefold" );

        //Go to shop to finish the order
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Item #: 1003", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Greeting card" ) );
        $this->assertEquals( "3,57 €", $this->getText( "id=orderCardTotalPrice" ) );
        $this->assertEquals( "3,51 €", $this->getText( "//div[@id='basketSummary']/table/tbody/tr[8]/td" ) );

        $this->assertEquals( "Total Products (net): 15,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,85 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "Total Products (incl. tax): 17,85 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "Shipping (net): 13,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,47 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ) );
        $this->assertEquals( "Surcharge Payment Method 10,50 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ) );
        $this->assertEquals( "Surcharge VAT 19% Amount: 2,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[7]" ) ) );
        $this->assertEquals( "3,51 €", $this->getText( "basketWrappingGross" ), "Wrapping price changed or didn't displayed" );
        $this->assertEquals( "3,57 €", $this->getText( "basketGiftCardGross" ), "Card price changed or didn't displayed" );
        $this->assertEquals( "52,90 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        //Go to admin and check the order
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.1']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.1']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2", "setfolder" );
        $this->frame( "edit" );
        $this->assertTrue( $this->isTextPresent( "Internal Status: OK" ) );
        $this->assertEquals( "17,85 EUR", $this->getText( "//td[5]" ) );
        $this->assertEquals( "Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.com", $this->clearString( $this->getText( "//td[1]/table[1]/tbody/tr/td[1]" ) ) );
        $this->assertEquals( "17,85", $this->getText( "//table[@id='order.info']/tbody/tr[1]/td[2]" ) );
        $this->assertEquals( "- 0,00", $this->getText( "//table[@id='order.info']/tbody/tr[2]/td[2]" ) );
        $this->assertEquals( "15,00", $this->getText( "//table[@id='order.info']/tbody/tr[3]/td[2]" ) );
        $this->assertEquals( "2,85", $this->getText( "//table[@id='order.info']/tbody/tr[4]/td[2]" ) );
        $this->assertEquals( "15,47", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "12,50", $this->getText( "//table[@id='order.info']/tbody/tr[6]/td[2]" ) );
        $this->assertEquals( "3,51", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->assertEquals( "3,57", $this->getText( "//table[@id='order.info']/tbody/tr[8]/td[2]" ) );
        $this->assertEquals( "52,90", $this->getText( "//table[@id='order.info']/tbody/tr[9]/td[2]" ) );

        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[1]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[2]" ), "line with discount info is not displayed" );
        $this->assertEquals( "PayPal", $this->getText( "//table[4]/tbody/tr[1]/td[2]" ), "Payment method not displayed in admin" );
        $this->assertEquals( "Test S&H set", $this->getText( "//table[4]/tbody/tr[2]/td[2]" ), "Shipping method is not displayed in admin" );
    }

    /**
     * test if option "Calculate default Shipping costs when User is not logged in yet" is working correct in PayPal
     * @group paypal_standalone
     */
    public function testPayPalShippingCostNotLoginUser()
    {
        // Change price for PayPal payment method
        $this->open( shopURL . "/_updateDB.php?filename=vatOptions.sql" );

        // Go to admin and set on "Calculate default Shipping costs when User is not logged in yet "
        $this->loginAdminForModule( "Master Settings", "Core Settings" );
        $this->openTab( "link=Settings" );
        $this->click( "link=Other settings" );
        sleep( 1 );
        $this->check( "//input[@name='confbools[blCalculateDelCostIfNotLoggedIn]'and @value='true']" );
        $this->clickAndWait( "save" );

        //Go to shop and add product
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "1003" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Test product 3", $this->getText( "//tr[@id='cartItem_1']/td[3]/div[1]" ) );

        //Added wrapping and card to basket
        $this->click( "id=header" );
        $this->click( "link=add" );
        $this->click( "id=wrapping_a6840cc0ec80b3991.74884864" );
        $this->click( "id=chosen_81b40cf0cd383d3a9.70988998" );
        $this->clickAndWait( "//button[text()='Apply']" );

        $this->assertEquals( "Total Products (net): 12,61 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,39 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "Total Products (incl. tax): 15,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "Shipping cost 3,90 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Shipping cost is not displayed correctly" );
        $this->assertEquals( "2,95 €", $this->getText( "basketWrappingGross" ), "Wrapping price changed or didn't displayed" );
        $this->assertEquals( "24,85 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );

        //Go to PayPal express
        $this->click( "name=paypalExpressCheckoutButton" );
        $this->waitForItemAppear( "id=submitLogin" );

        //Go to PayPal
        $this->assertEquals("Mit PayPal bezahlen - PayPal", $this->getTitle());

        $this->assertTrue( $this->isTextPresent( "€15.00" ) );
        $this->assertTrue( $this->isTextPresent( "€10.50" ) );
        $this->assertTrue( $this->isTextPresent( "€2.95" ) );
        $this->assertTrue( $this->isTextPresent( "€3.00" ) );
        $this->assertTrue( $this->isTextPresent( "Item total €31.45" ) );

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue_abovefold" );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1003" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €15,00", $this->getText( "//li[@id='multiitem1']/ul/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Surcharge Type of Payment" ) );
        $this->assertEquals( "Artikelpreis: €10,50", $this->getText( "//li[@id='multiitem1']/ul[2]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[2]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Giftwrapper" ) );
        $this->assertEquals( "Artikelpreis: €2,95", $this->getText( "//li[@id='multiitem1']/ul[3]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[3]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Greeting Card" ) );
        $this->assertEquals( "Artikelpreis: €3,00", $this->getText( "//li[@id='multiitem1']/ul[4]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[4]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Warenwert€31,45" ), "Product total is not displayed in PayPal" );
        $this->assertEquals( "Gesamtbetrag €31,45 EUR", $this->getText( "//div[@id='miniCart']/div[3]/ul/li/span" ), "Total price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );
        $this->waitForTextPresent( "Gesamtbetrag €44,45 EUR" );

        $this->click( "id=continue_abovefold" );
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );

        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Item #: 1003", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Greeting card" ) );
        $this->assertEquals( "3,00 €", $this->getText( "id=orderCardTotalPrice" ) );

        $this->assertEquals( "Total Products (net): 12,61 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,39 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "Total Products (incl. tax): 15,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "Shipping cost 13,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Shipping cost is not displayed correctly" );
        $this->assertEquals( "Surcharge Payment Method 10,50 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ) );
        $this->assertEquals( "2,95 €", $this->getText( "basketWrappingGross" ), "Wrapping price changed or didn't displayed" );
        $this->assertEquals( "44,45 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );

        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        //Go to admin and check the order
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( $this->getLoginDataByName( 'sBuyerFirstName' ), $this->getText( "//tr[@id='row.1']/td[6]" ) );
        $this->assertEquals( $this->getLoginDataByName( 'sBuyerLastName' ), $this->getText( "//tr[@id='row.1']/td[7]" ) );
        $this->openTab( "link=2", "setfolder" );
        $this->frame( "edit" );
        $this->assertTrue( $this->isTextPresent( "Internal Status: OK" ) );
        $this->assertEquals( "15,00 EUR", $this->getText( "//td[5]" ) );
        $this->assertEquals( "Billing Address: {$this->getLoginDataByName( 'sBuyerFirstName' )} {$this->getLoginDataByName( 'sBuyerLastName' )} ESpachstr. 1 79111 Freiburg Germany E-mail: {$this->getLoginDataByName( 'sBuyerLogin' )}", $this->clearString( $this->getText( "//td[1]/table[1]/tbody/tr/td[1]" ) ) );
        $this->assertEquals( "15,00", $this->getText( "//table[@id='order.info']/tbody/tr[1]/td[2]" ) );
        $this->assertEquals( "- 0,00", $this->getText( "//table[@id='order.info']/tbody/tr[2]/td[2]" ) );
        $this->assertEquals( "12,61", $this->getText( "//table[@id='order.info']/tbody/tr[3]/td[2]" ) );
        $this->assertEquals( "2,39", $this->getText( "//table[@id='order.info']/tbody/tr[4]/td[2]" ) );
        $this->assertEquals( "13,00", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "10,50", $this->getText( "//table[@id='order.info']/tbody/tr[6]/td[2]" ) );
        $this->assertEquals( "2,95", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->assertEquals( "3,00", $this->getText( "//table[@id='order.info']/tbody/tr[8]/td[2]" ) );
        $this->assertEquals( "44,45", $this->getText( "//table[@id='order.info']/tbody/tr[9]/td[2]" ) );

        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[1]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[2]" ), "line with discount info is not displayed" );
        $this->assertEquals( "PayPal", $this->getText( "//table[4]/tbody/tr[1]/td[2]" ), "Payment method not displayed in admin" );
        $this->assertEquals( "Test S&H set", $this->getText( "//table[4]/tbody/tr[2]/td[2]" ), "Shipping method is not displayed in admin" );
    }


    /**
     * test if PayPal works correct when last product ir purchased.
     * @group paypal_standalone
     */
    public function testPayPalStockOne()
    {
        $this->open( shopURL . "/_updateDB.php?filename=changeStock.sql" );
        $this->openShop();
        $this->searchFor( "1001" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->openBasket( "English" );

        //Login to shop and go to the basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->waitForElementPresent( "paypalExpressCheckoutButton", "PayPal express button not displayed in the cart" );
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Purchased product name is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//tr[@id='cartItem_1']/td[3]/div[2]" ) );
        //   $this->assertEquals( "OXID Surf and Kite Shop | Cart | purchase online", $this->getTitle() );
        $this->assertEquals( "Grand total: 0,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "Shipping cost" ), "Shipping cost is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "exact:?" ) );
        $this->storeChecked( "//input[@name='displayCartInPayPal' and @value='1']" );
        $this->assertTrue( $this->isTextPresent( "Display cart in PayPal" ), "Text:Display cart in PayPal for checkbox not displayed" );
        $this->assertTrue( $this->isElementPresent( "name=displayCartInPayPal" ), "Checkbox:Display cart in PayPal not displayed" );

        //Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->click("name=paypalExpressCheckoutButton");
        $this->waitForItemAppear("id=submitLogin");
        $this->assertTrue($this->isTextPresent("Test product 1"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Item number: 1001"), "Product number not displayed in PayPal ");
        $this->assertFalse($this->isTextPresent("Grand total: €0,99"), "Grand total should not be displayed");

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );
        $this->waitForItemAppear( "id=displayShippingAmount" );

        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Purchased product name is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Warenwert€0,99" ), "Product price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "exact:Versandkosten:" ), "Shipping cost is not calculated in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ), "Product name is not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandmethode: Test S&H set: €0,00 EUR" ), "Shipping method is not shown in PayPal" );
        $this->assertEquals( "Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Deutschland Versandmethode: Test S&H set: €0,00 EUR", $this->clearString( $this->getText( "//div[@class='inset confidential']" ) ) );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1001" ), "Product number not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Artikelpreis: €0,99" ), "Product price not shown in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Anzahl: 1" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isElementPresent( "id=shippingHandling" ), "Shipping cost is not calculated in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Gesamtbetrag €0,99 EUR" ), "Total price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( "Versandkosten:€0,00" ), "Total price is not displayed in PayPal" );

        $this->waitForTextPresent( "Gesamtbetrag €0,99 EUR" );
        $this->waitForTextPresent( "Versandkosten:€0,00" );
        $this->assertTrue( $this->isTextPresent( "Versandkosten:€0,00" ), "Shipping cost is not calculated in PayPal" );
        $this->waitForItemAppear( "id=shippingHandling" );
        $this->assertTrue( $this->isElementPresent( "id=shippingHandling" ), "Shipping cost is not calculated in PayPal" );

        $this->waitForItemAppear( "id=continue" );

        // adding sleep to wait while "continue" button will be active
        sleep( 10 );

        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );

        //Check are all info in the last order step correct
        $this->assertTrue( $this->isElementPresent( "link=Test product 1" ), "Purchased product name is not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Item #: 1001" ), "Product number not displayed in last order step" );
        $this->assertEquals( "Shipping cost 0,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ), "Shipping cost is not displayed correctly" );
        // $this->assertEquals( "OXID Surf and Kite Shop | Order | purchase online", $this->getTitle() );
        $this->assertEquals( "Grand total: 0,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ), "Grand total is not displayed correctly" );
        $this->assertTrue( $this->isTextPresent( "PayPal" ), "Payment method not displayed in last order step" );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );
    }

    /**
     * test if PayPal works when proportional calculation is used for additional products.
     * @group paypal_standalone
     */
    public function testPayPalProportional()
    {
        // Change price for PayPal payment method
        $this->open( shopURL . "/_updateDB.php?filename=newVAT.sql" );

        // Go to admin and set on all VAT options
        $this->loginAdminForModule( "Master Settings", "Core Settings" );
        $this->openTab( "link=Settings" );
        $this->click( "link=VAT" );
        sleep( 1 );
        $this->check( "//input[@name='confbools[blShowVATForWrapping]'and @value='true']" );
        $this->check( "//input[@name='confbools[blShowVATForDelivery]'and @value='true']" );
        $this->check( "//input[@name='confbools[blShowVATForPayCharge]'and @value='true']" );
        $this->clickAndWait( "save" );

        //Go to shop and add product
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "100" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_2']//button" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_3']//button" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_4']//button" );

        $this->openBasket( "English" );

        //Login to shop and go to basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertTrue( $this->isTextPresent( "Test product 0" ) );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ) );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertTrue( $this->isTextPresent( "Test product 4" ) );

        //Added wrapping and card to basket
        $this->click( "id=header" );
        $this->click( "link=add" );
        $this->click( "id=wrapping_a6840cc0ec80b3991.74884864" );
        $this->click( "id=chosen_81b40cf0cd383d3a9.70988998" );
        $this->clickAndWait( "//button[text()='Apply']" );
        $this->assertEquals( "Total Products (net): 36,33 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "plus VAT 2% Amount: 0,20 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "plus VAT 13% Amount: 0,11 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "plus VAT 15% Amount: 1,96 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,39 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ) );

        $this->assertEquals( "Total Products (incl. tax): 40,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ) );
        $this->assertEquals( "Shipping (net): 0,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[7]" ) ) );
        $this->assertEquals( "Gift Wrapping (net): 2,89 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[8]" ) ) );
        $this->assertEquals( "2,89 €", $this->getText( "basketWrappingNetto" ), "Wrapping price changed or didn't displayed" );
        $this->assertEquals( "0,06 €", $this->getText( "basketWrappingVat" ), "Wrapping vat changed or didn't displayed" );

        $this->assertEquals( "2,52 €", $this->getText( "basketGiftCardNetto" ), "Card price changed or didn't displayed" );
        $this->assertEquals( "0,48 €", $this->getText( "basketGiftCardVat" ), "Card VAT price changed or didn't displayed" );
        $this->assertEquals( "46,94 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );

        // Go to 2nd step
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to 3rd step and select PayPal as payment method
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->waitForItemAppear( "id=payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to PayPal
        $this->waitForItemAppear("id=submitLogin");
        $this->assertEquals("Pay with a PayPal account - PayPal", $this->getTitle());
        $this->assertTrue($this->isTextPresent("€10,00"));
        $this->assertTrue($this->isTextPresent("€0,99"));
        $this->assertTrue($this->isTextPresent("€15,00"));
        $this->assertTrue($this->isTextPresent("€2,95"));
        $this->assertTrue($this->isTextPresent("€3,00"));
        $this->assertEquals("Total €46,94 EUR", $this->getText("//div[@id='miniCart']/div[3]/ul/li/span"));
        $this->assertTrue($this->isTextPresent("Item total €46,94"));

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1000" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €10,00", $this->getText( "//li[@id='multiitem1']/ul/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1001" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €0,99", $this->getText( "//li[@id='multiitem1']/ul[2]/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[2]/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1003" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €15,00", $this->getText( "//li[@id='multiitem1']/ul[3]/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[3]/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1004" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €15,00", $this->getText( "//li[@id='multiitem1']/ul[4]/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[4]/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Giftwrapper" ) );
        $this->assertEquals( "Artikelpreis: €2,95", $this->getText( "//li[@id='multiitem1']/ul[5]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[5]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Greeting Card" ) );
        $this->assertEquals( "Artikelpreis: €3,00", $this->getText( "//li[@id='multiitem1']/ul[6]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[6]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Warenwert€46,94" ), "Product price is not displayed in Paypal" );
        $this->assertEquals( "Gesamtbetrag €46,94 EUR", $this->getText( "//div[@id='miniCart']/div[3]/ul/li/span" ), "Total price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );
        $this->click( "id=continue_abovefold" );

        //Go to shop to finish the order
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->assertTrue( $this->isTextPresent( "Test product 0" ) );
        $this->assertEquals( "Item #: 1000", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ) );
        $this->assertEquals( "Item #: 1001", $this->getText( "//tr[@id='cartItem_2']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Item #: 1003", $this->getText( "//tr[@id='cartItem_3']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Test product 4" ) );
        $this->assertEquals( "Item #: 1004", $this->getText( "//tr[@id='cartItem_4']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Greeting card" ) );

        $this->assertEquals( "36,33 €", $this->getText( "basketTotalProductsNetto" ), "Net price changed or didn't displayed" );
        $this->assertEquals( "0,20 €", $this->getText( "//div[@id='basketSummary']//tr[2]/td" ), "VAT 2% changed " );
        $this->assertEquals( "0,11 €", $this->getText( "//div[@id='basketSummary']//tr[3]/td" ), "VAT 13% changed " );
        $this->assertEquals( "1,96 €", $this->getText( "//div[@id='basketSummary']//tr[4]/td" ), "VAT 15% changed " );
        $this->assertEquals( "2,39 €", $this->getText( "//div[@id='basketSummary']//tr[5]/td" ), "VAT 19% changed " );
        $this->assertEquals( "40,99 €", $this->getText( "basketTotalProductsGross" ), "Brut price changed  or didn't displayed" );
        $this->assertEquals( "0,00 €", $this->getText( "basketDeliveryNetto" ), "Shipping price changed  or didn't displayed" );
        $this->assertEquals( "2,89 €", $this->getText( "basketWrappingNetto" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "0,06 €", $this->getText( "basketWrappingVat" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "2,52 €", $this->getText( "basketGiftCardNetto" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "0,48 €", $this->getText( "basketGiftCardVat" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "46,94 €", $this->getText( "basketGrandTotal" ), "Grand total price changed  or didn't displayed" );

        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        //Go to admin to activate proportional calculation
        $this->loginAdminForModule( "Master Settings", "Core Settings" );
        $this->openTab( "link=Settings" );
        $this->click( "link=VAT" );
        sleep( 1 );
        $this->check( "//input[@name='confstrs[sAdditionalServVATCalcMethod]'and @value='proportional']" );
        $this->clickAndWait( "save" );

        //Go to shop and add product
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->searchFor( "100" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_1']//button" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_2']//button" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_3']//button" );
        $this->clickAndWait( "//form[@name='tobasketsearchList_4']//button" );

        $this->openBasket( "English" );

        //Login to shop and go to basket
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->assertTrue( $this->isTextPresent( "Test product 0" ) );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ) );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertTrue( $this->isTextPresent( "Test product 4" ) );

        //Added wrapping and card to basket
        $this->click( "id=header" );
        $this->click( "link=add" );
        $this->click( "id=wrapping_a6840cc0ec80b3991.74884864" );
        $this->click( "id=chosen_81b40cf0cd383d3a9.70988998" );
        $this->clickAndWait( "//button[text()='Apply']" );

        $this->assertEquals( "Total Products (net): 36,33 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[1]" ) ) );
        $this->assertEquals( "plus VAT 2% Amount: 0,20 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[2]" ) ) );
        $this->assertEquals( "plus VAT 13% Amount: 0,11 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[3]" ) ) );
        $this->assertEquals( "plus VAT 15% Amount: 1,96 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[4]" ) ) );
        $this->assertEquals( "plus VAT 19% Amount: 2,39 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[5]" ) ) );

        $this->assertEquals( "Total Products (incl. tax): 40,99 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[6]" ) ) );
        $this->assertEquals( "Shipping (net): 0,00 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[7]" ) ) );
        $this->assertEquals( "Gift Wrapping (net): 2,89 €", $this->clearString( $this->getText( "//div[@id='basketSummary']//tr[8]" ) ) );
        $this->assertEquals( "2,89 €", $this->getText( "basketWrappingNetto" ), "Wrapping price changed or didn't displayed" );
        $this->assertEquals( "0,06 €", $this->getText( "basketWrappingVat" ), "Wrapping vat changed or didn't displayed" );
        $this->assertEquals( "2,66 €", $this->getText( "basketGiftCardNetto" ), "Card price changed or didn't displayed" );
        $this->assertEquals( "0,34 €", $this->getText( "basketGiftCardVat" ), "Card VAT price changed or didn't displayed" );
        $this->assertEquals( "46,94 €", $this->getText( "basketGrandTotal" ), "Grand total price changed or didn't displayed" );

        // Go to 2nd step
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to 3rd step and select PayPal as payment method
        $this->clickAndWait( "//button[text()='Continue to the next step']" );
        $this->waitForItemAppear( "id=payment_oxidpaypal" );
        $this->click( "id=payment_oxidpaypal" );
        $this->clickAndWait( "//button[text()='Continue to the next step']" );

        //Go to PayPal
        $this->waitForItemAppear("id=submitLogin");
        $this->assertEquals("Pay with a PayPal account - PayPal", $this->getTitle());
        $this->assertTrue($this->isTextPresent("€10,00"));
        $this->assertTrue($this->isTextPresent("€0,99"));
        $this->assertTrue($this->isTextPresent("€15,00"));
        $this->assertTrue($this->isTextPresent("€2,95"));
        $this->assertTrue($this->isTextPresent("€3,00"));
        $this->assertEquals("Total €46,94 EUR", $this->getText("//div[@id='miniCart']/div[3]/ul/li/span"));
        $this->assertTrue($this->isTextPresent("Item total €46,94"));

        $this->_loginToSandbox();

        $this->waitForItemAppear( "id=continue" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1000" ), "Product number not shown in Paypal" );
        $this->assertEquals( "Artikelpreis: €10,00", $this->getText( "//li[@id='multiitem1']/ul/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1001" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €0,99", $this->getText( "//li[@id='multiitem1']/ul[2]/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[2]/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1003" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €15,00", $this->getText( "//li[@id='multiitem1']/ul[3]/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[3]/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Artikelnummer: 1004" ), "Product number not shown in PayPal" );
        $this->assertEquals( "Artikelpreis: €15,00", $this->getText( "//li[@id='multiitem1']/ul[4]/li[3]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[4]/li[4]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Giftwrapper" ) );
        $this->assertEquals( "Artikelpreis: €2,95", $this->getText( "//li[@id='multiitem1']/ul[5]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[5]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Greeting Card" ) );
        $this->assertEquals( "Artikelpreis: €3,00", $this->getText( "//li[@id='multiitem1']/ul[6]/li[2]" ), "Product price not shown in PayPal" );
        $this->assertEquals( "Anzahl: 1", $this->getText( "//li[@id='multiitem1']/ul[6]/li[3]" ), "Product quantity is not shown in PayPal" );

        $this->assertTrue( $this->isTextPresent( "Warenwert€46,94" ), "Product price is not displayed in PayPal" );
        $this->assertEquals( "Gesamtbetrag €46,94 EUR", $this->getText( "//div[@id='miniCart']/div[3]/ul/li/span" ), "Total price is not displayed in PayPal" );
        $this->assertTrue( $this->isTextPresent( $this->getLoginDataByName( 'sBuyerLogin' ) ) );
        $this->assertTrue( $this->isTextPresent( "Ihr Warenkorb" ) );
        $this->click( "id=continue_abovefold" );

        //Go to shop to finish the order
        $this->clickAndWait( "id=continue" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->assertTrue( $this->isTextPresent( "Test product 0" ) );
        $this->assertEquals( "Item #: 1000", $this->getText( "//tr[@id='cartItem_1']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Test product 1" ) );
        $this->assertEquals( "Item #: 1001", $this->getText( "//tr[@id='cartItem_2']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Test product 3" ) );
        $this->assertEquals( "Item #: 1003", $this->getText( "//tr[@id='cartItem_3']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Test product 4" ) );
        $this->assertEquals( "Item #: 1004", $this->getText( "//tr[@id='cartItem_4']/td[2]/div[2]" ), "Product number not displayed in last order step" );
        $this->assertTrue( $this->isTextPresent( "Greeting card" ) );

        $this->assertEquals( "36,33 €", $this->getText( "basketTotalProductsNetto" ), "Net price changed or didn't displayed" );
        $this->assertEquals( "0,20 €", $this->getText( "//div[@id='basketSummary']//tr[2]/td" ), "VAT 2% changed " );
        $this->assertEquals( "0,11 €", $this->getText( "//div[@id='basketSummary']//tr[3]/td" ), "VAT 13% changed " );
        $this->assertEquals( "1,96 €", $this->getText( "//div[@id='basketSummary']//tr[4]/td" ), "VAT 15% changed " );
        $this->assertEquals( "2,39 €", $this->getText( "//div[@id='basketSummary']//tr[5]/td" ), "VAT 19% changed " );
        $this->assertEquals( "40,99 €", $this->getText( "basketTotalProductsGross" ), "Brut price changed  or didn't displayed" );
        $this->assertEquals( "0,00 €", $this->getText( "basketDeliveryNetto" ), "Shipping price changed  or didn't displayed" );
        $this->assertEquals( "2,89 €", $this->getText( "basketWrappingNetto" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "0,06 €", $this->getText( "basketWrappingVat" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "2,66 €", $this->getText( "basketGiftCardNetto" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "0,34 €", $this->getText( "basketGiftCardVat" ), "Wrapping price changed  or didn't displayed" );
        $this->assertEquals( "46,94 €", $this->getText( "basketGrandTotal" ), "Grand total price changed  or didn't displayed" );

        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        //Go to admin and check the order
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->assertEquals( "Testing user acc Äß'ü", $this->getText( "//tr[@id='row.2']/td[6]" ), "Wrong user name is displayed in order" );
        $this->assertEquals( "PayPal Äß'ü", $this->getText( "//tr[@id='row.2']/td[7]" ), "Wrong user last name is displayed in order" );
        $this->openTab( "link=2", "setfolder" );
        $this->frame( "edit" );
        $this->assertTrue( $this->isTextPresent( "Internal Status: OK" ) );
        $this->assertEquals( "10,00 EUR", $this->getText( "//td[5]" ) );

        $this->assertEquals( "Billing Address: Company SeleniumTestCase Äß'ü Testing acc for Selenium Mr Testing user acc Äß'ü PayPal Äß'ü Musterstr. Äß'ü 1 79098 Musterstadt Äß'ü Germany E-mail: testing_account@oxid-esales.com", $this->clearString( $this->getText( "//td[1]/table[1]/tbody/tr/td[1]" ) ) );
        $this->assertEquals( "40,99", $this->getText( "//table[@id='order.info']/tbody/tr[1]/td[2]" ) );
        $this->assertEquals( "- 0,00", $this->getText( "//table[@id='order.info']/tbody/tr[2]/td[2]" ) );
        $this->assertEquals( "36,33", $this->getText( "//table[@id='order.info']/tbody/tr[3]/td[2]" ) );
        $this->assertEquals( "0,20", $this->getText( "//table[@id='order.info']/tbody/tr[4]/td[2]" ) );
        $this->assertEquals( "0,11", $this->getText( "//table[@id='order.info']/tbody/tr[5]/td[2]" ) );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[6]/td[2]" ) );
        $this->assertEquals( "0,00", $this->getText( "//table[@id='order.info']/tbody/tr[7]/td[2]" ) );
        $this->assertEquals( "2,95", $this->getText( "//table[@id='order.info']/tbody/tr[8]/td[2]" ) );
        $this->assertEquals( "3,00", $this->getText( "//table[@id='order.info']/tbody/tr[9]/td[2]" ) );
        $this->assertEquals( "46,94", $this->getText( "//table[@id='order.info']/tbody/tr[10]/td[2]" ) );

        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[1]" ), "line with discount info is not displayed" );
        $this->assertTrue( $this->isElementPresent( "//table[@id='order.info']/tbody/tr[2]/td[2]" ), "line with discount info is not displayed" );
        $this->assertEquals( "PayPal", $this->getText( "//table[4]/tbody/tr[1]/td[2]" ), "Payment method not displayed in admin" );
        $this->assertEquals( "Test S&H set", $this->getText( "//table[4]/tbody/tr[2]/td[2]" ), "Shipping method is not displayed in admin" );
    }

    /**
     * test if PayPal works in Netto mode
     * @group paypal_standalone
     */
    public function testPayPalExpressNettoMode()
    {
        // Activate the necessary options Neto mode
        if ( OXID_VERSION_EE ):
            $this->open(shopURL."/_updateDB.php?filename=NettoModeTurnOn_ee.sql");
        endif;
        if ( OXID_VERSION_PE ):
            $this->open(shopURL."/_updateDB.php?filename=NettoModeTurnOn_pe.sql");
        endif;

        // Add articles to basket.
        $this->openShop();
        $this->searchFor("1401");
        $this->clickAndWait("//form[@name='tobasketsearchList_1']//button");

        // Change price for PayPal payment method
        $this->open(shopURL."/_updateDB.php?filename=vatOptions.sql");

        $this->openBasket("English");

        //Added wrapping and card to basket.
        $this->click("id=header");
        $this->click("link=add");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Apply']");

        // Check wrapping and card prices.
        $this->assertEquals("2,95 €", $this->getText("basketWrappingGross"),"Wrapping price changed or didn't display");
        $this->assertEquals("3,00 €", $this->getText("basketGiftCardGross"),"Card price changed or didn't display");

        // Check basket prices.
        $this->assertEquals("108,40 €", $this->getText("basketTotalProductsNetto"),"Net price changed or didn't display");
        $this->assertEquals("134,95 €", $this->getText("basketGrandTotal"),"Grand total price changed or didn't display");

        //Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->assertTrue($this->isElementPresent("paypalExpressCheckoutButton"));
        $this->click("name=paypalExpressCheckoutButton");
        $this->waitForItemAppear("id=submitLogin");

        // Check if article is correct shown in login page.
        $this->assertTrue($this->isTextPresent("Harness SOL KITE"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Item number: 1401"), "Product number not displayed in PayPal ");
        $this->assertTrue($this->isTextPresent("Item price: €108.40"),"Article price is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Item price: €8.82"),"Surcharge is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Item price: €2.48"),"Gift wrapping is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Item price: €2.52"),"Gift card is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Item total €122.22"),"Total items sum should be displayed");

        $this->_loginToSandbox();

        // Check if article sum and VAT is shown correctly after login to PayPal.
        // Continue button is visible before PayPal does callback.
        // Then it becomes invisible while PayPal does callback.
        // Button appears when PayPal gets callback result.
        // Need to ensure that call back is returned otherwise total sum would be shown wrongly.
        $this->waitForItemAppear("id=continue");
        $this->waitForItemAppear("id=displayShippingAmount");
        sleep(10);
        $this->waitForText("Steuer");
        $this->assertTrue($this->isTextPresent("Harness SOL KITE"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Warenwert€122,22"), "Product price is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Steuer:€23,23"), "Product VAT is not displayed in PayPal");
        $this->waitForItemAppear("id=displayShippingAmount");
        $this->assertTrue($this->isTextPresent("Gesamtbetrag €158,45 EUR"), "Total price is not displayed in PayPal");

        $this->click("id=continue");
        $this->waitForText("Please check all data on this overview before submitting your order!");
    }

    /**
     * test if PayPal works in Net mode
     * @group paypal_standalone
     */
    public function testPayPalStandardNettoMode()
    {
        // Activate the necessary options Neto mode
        // Turn Trusted Shops functionality on
        if ( OXID_VERSION_EE ):
            $this->open( shopURL . "/_updateDB.php?filename=NettoModeTurnOn_ee.sql" );
            $this->open( shopURL . "/_updateDB.php?filename=trustedShopsOxConfig.sql");
        endif;
        if ( OXID_VERSION_PE ):
            $this->open( shopURL . "/_updateDB.php?filename=NettoModeTurnOn_pe.sql" );
            $this->open( shopURL . "/_updateDB.php?filename=trustedShopsOxConfig_pe.sql");
        endif;

        // Add articles to basket.
        $this->openShop();
        $this->searchFor("1401");
        $this->clickAndWait("//form[@name='tobasketsearchList_1']//button");

        // Change price for PayPal payment method
        $this->open(shopURL."/_updateDB.php?filename=vatOptions.sql");

        // Need to wait after switching language as basket layout might not appear if JavaScript is not loaded.
        $this->switchLanguage("Deutsch");
        sleep(1);
        $this->openBasket("Deutsch");

        //Added wrapping and card to basket.
        $this->click("id=header");
        $this->click("link=hinzufügen");
        $this->click("id=wrapping_a6840cc0ec80b3991.74884864");
        $this->click("id=chosen_81b40cf0cd383d3a9.70988998");
        $this->clickAndWait("//button[text()='Übernehmen']");

        // Check wrapping and card prices.
        $this->assertEquals("2,95 €", $this->getText("basketWrappingGross"),"Wrapping price changed or didn't display");
        $this->assertEquals("3,00 €", $this->getText("basketGiftCardGross"),"Card price changed or didn't display");

        // Check basket prices.
        $this->assertEquals("108,40 €", $this->getText("basketTotalProductsNetto"),"Net price changed or didn't display");
        $this->assertEquals("134,95 €", $this->getText("basketGrandTotal"),"Grand total price changed or didn't display");

        // Add more articles so sum would be more than 500eur. 500eur would turn on Trusted Shop payment.
        // Without sleep basket update do not make update before checking actual prices.
        $this->type("am_1", "10");
        sleep(1);
        $this->clickAndWait("basketUpdate");
        sleep(1);

        // Check basket prices.
        $this->assertEquals("1.084,00 €", $this->getText("basketTotalProductsNetto"),"Net price changed or didn't display");
        $this->assertTrue($this->isTextPresent("205,96 €"), "Articles VAT changed or didn't display");
        $this->assertEquals("1.322,46 €", $this->getText("basketGrandTotal"),"Grand total price changed or didn't display");

        $this->loginInFrontend("testing_account@oxid-esales.com", "useruser");

        //On 2nd step
        $this->clickAndWait("//button[text()='Weiter zum nächsten Schritt']");
        $this->waitForText("Lieferadresse");

        //On 3rd step
        $this->clickAndWait("//button[text()='Weiter zum nächsten Schritt']");
        $this->waitForText("Bitte wählen Sie Ihre Versandart");

        // Check trusted shop protection
        $this->check("//input[@name='bltsprotection'and @value='1']");

        // Go to PayPal
        $this->click("payment_oxidpaypal");
        $this->click("//button[text()='Weiter zum nächsten Schritt']");
        $this->waitForItemAppear("id=submitLogin");

        // Check if article is correct shown in login page.
        $this->assertTrue($this->isTextPresent("Trapez ION SOL KITE 2011"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Artikelnummer: 1401"), "Product number not displayed in PayPal ");
        $this->assertTrue($this->isTextPresent("Artikelpreis: €108,40"),"Article price is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Artikelpreis: €8,82"),"Surcharge is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Artikelpreis: €24,79"),"Gift wrapping is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Artikelpreis: €2,52"),"Gift card is not correct. Should be in Net mode.");
        $this->assertTrue($this->isTextPresent("Warenwert€1.120,95"),"Total items sum should be displayed");
        $this->assertTrue($this->isTextPresent("Steuer:€212,99"), "Product VAT is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Versandkosten:€13,00"), "Product shipping cost is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Gesamtbetrag €1.346,94 EUR"), "Total price is not displayed in PayPal");

        $this->_loginToSandbox();

        // Check if article sum and VAT is shown correctly after login to PayPal.
        $this->waitForItemAppear("id=continue");
        $this->waitForItemAppear("id=displayShippingAmount");
        $this->assertTrue($this->isTextPresent("Trapez ION SOL KITE 2011"), "Purchased product name is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Warenwert€1.120,95"), "Product price is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Steuer:€212,99"), "Product VAT is not displayed in PayPal");
        $this->assertTrue($this->isTextPresent("Gesamtbetrag €1.346,94 EUR"), "Total price is not displayed in PayPal");

        // Continue button is visible before PayPal does callback.
        // Then it becomes invisible while PayPal does callback.
        // Button appears when PayPal gets callback result.
        $this->waitForItemAppear("id=continue");
        $this->click("id=continue");
        $this->waitForItemAppear("id=continue");
        $this->click("id=continue");

        $this->waitForText("Bitte prüfen Sie alle Daten, bevor Sie Ihre Bestellung abschließen!");
    }

    /**
     * test if PayPal is not shown in frontend after configs is set in admin
     * @group paypal_standalone
     */
    public function testPayPalShortcut()
    {
        // Turn Off all PayPal shortcut in frontend
        if ( OXID_VERSION_EE ):
            $this->open( shopURL . "/_updateDB.php?filename=testPayPalShortcut_ee.sql" );
        endif;
        if ( OXID_VERSION_PE ):
            $this->open( shopURL . "/_updateDB.php?filename=testPayPalShortcut_pe.sql");
        endif;

        // Add articles to basket.
        $this->openShop();
        $this->switchLanguage( "English" );
        $this->loginInFrontend( "testing_account@oxid-esales.com", "useruser" );
        $this->searchFor( "1001" );
        $this->clickAndWait("//ul[@id='searchList']/li/form/div/a[2]/span");
        $this->assertFalse($this->isElementPresent("id=paypalExpressCheckoutDetailsButton"),"After PayPal is disabled in admin PayPal should not be visible in admin");
        $this->clickAndWait("id=toBasket");
        $this->click("id=minibasketIcon");
        $this->assertFalse($this->isElementPresent("id=paypalExpressCheckoutMiniBasketImage"));
        $this->clickAndWait("link=Display cart");
        $this->assertFalse($this->isElementPresent("xpath=(//input[@name='paypalExpressCheckoutButton'])[2]"));
        $this->clickAndWait("xpath=(//button[@type='submit'])[3]");
        $this->clickAndWait("xpath=(//button[@type='submit'])[5]");
        $this->clickAndWait("id=userNextStepTop");
        $this->assertFalse($this->isElementPresent("id=payment_oxidpaypal"));
        $this->clickAndWait( "id=paymentNextStepBottom" );
        $this->waitForItemAppear( "id=breadCrumb" );
        $this->clickAndWait( "//button[text()='Order now']" );
        $this->assertTrue( $this->isTextPresent( "Thank you for your order in OXID eShop" ), "Order is not finished successful" );

        //Go to Admin
        $this->loginAdminForModule( "Administer Orders", "Orders", "btn.help", "link=2" );
        $this->openTab( "link=2" );

        // Go to PayPal tab
        $this->frame( "list" );
        $this->click("//a[contains(@href, '#oepaypalorder_paypal')]");
        $this->waitForFrameToLoad("edit");
        $this->frame( "edit" );
        $this->assertEquals("This tab is for orders with the PayPal payment method only", $this->getText("//div[2]/div[2]"));
    }

    /**
     * Login to PayPal sandbox.
     *
     * @param string $sLoginEmail email to login.
     * @param string $sLoginPassword password to login.
     */
    protected function _loginToSandbox( $sLoginEmail = null, $sLoginPassword = null )
    {
        if ( !isset( $sLoginEmail ) ) {
            $sLoginEmail = $this->getLoginDataByName( 'sBuyerLogin' );
        }
        if ( !isset( $sLoginPassword ) ) {
            $sLoginPassword = $this->getLoginDataByName( 'sBuyerPassword' );
        }

        $this->type( "login_email", $sLoginEmail );
        $this->type( "login_password", $sLoginPassword );
        $this->click( "id=submitLogin" );
    }
}