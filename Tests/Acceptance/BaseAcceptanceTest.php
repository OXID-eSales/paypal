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

namespace OxidEsales\PayPalModule\Tests\Acceptance;

/**
 * @todo add dependency between external tests. If one fails next should not start.
 */
abstract class BaseAcceptanceTest extends \OxidEsales\TestingLibrary\AcceptanceTestCase
{
    const PAYPAL_LOGIN_BUTTON_ID_OLD = "id=submitLogin";
    const PAYPAL_LOGIN_BUTTON_ID_NEW = "id=btnLogin";

    const SELECTOR_ADD_TO_BASKET = "//form[@name='tobasketsearchList_1']//button";
    const SELECTOR_BASKET_NEXTSTEP = "//button[text()='Weiter zum nächsten Schritt']";

    const LOGIN_USERNAME = "testing_account@oxid-esales.dev";
    const LOGIN_USERPASS = "useruser";

    private $newPayPalUserInterface = true;
    const PAYPAL_FRAME_NAME = "injectedUl";
    const THANK_YOU_PAGE_IDENTIFIER = "Thank you";
    const IDENTITY_COLUMN_ORDER_PAYPAL_TAB_PRICE_VALUE = 2;

    /** @var int How much time to wait for pages to load. Wait time is multiplied by this value. */
    protected $_iWaitTimeMultiplier = 7;

    protected $retryTimes = 1;

    /**
     * Activates PayPal and adds configuration
     *
     * @param string $testSuitePath
     *
     * @throws \Exception
     */
    public function addTestData($testSuitePath)
    {
        parent::addTestData($testSuitePath);

        $this->callShopSC('oxConfig', null, null, array(
            'sOEPayPalTransactionMode' => array(
                'type' => 'select',
                'value' => 'Authorization',
                'module' => 'module:oepaypal'
            ),
            'sOEPayPalUsername' => array(
                'type' => 'str',
                'value' => $this->getLoginDataByName('sOEPayPalUsername'),
                'module' => 'module:oepaypal'
            ),
            'sOEPayPalPassword' => array(
                'type' => 'password',
                'value' => $this->getLoginDataByName('sOEPayPalPassword'),
                'module' => 'module:oepaypal'
            ),
            'sOEPayPalSignature' => array(
                'type' => 'str',
                'value' => $this->getLoginDataByName('sOEPayPalSignature'),
                'module' => 'module:oepaypal'
            ),
            'blOEPayPalSandboxMode' => array(
                'type' => 'bool',
                'value' => 1,
                'module' => 'module:oepaypal'
            ),
            'sOEPayPalSandboxUsername' => array(
                'type' => 'str',
                'value' => $this->getLoginDataByName('sOEPayPalSandboxUsername'),
                'module' => 'module:oepaypal'
            ),
            'sOEPayPalSandboxPassword' => array(
                'type' => 'password',
                'value' => $this->getLoginDataByName('sOEPayPalSandboxPassword'),
                'module' => 'module:oepaypal'
            ),
            'sOEPayPalSandboxSignature' => array(
                'type' => 'str',
                'value' => $this->getLoginDataByName('sOEPayPalSandboxSignature'),
                'module' => 'module:oepaypal'
            ),
            'blPayPalLoggerEnabled' => array(
                'type' => 'str',
                'value' => true,
                'module' => 'module:oepaypal'
            )
        ));

        $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'cleanPayPalLog');
    }

    /**
     * Before we retry a PayPal test log the page source.
     * Log it in PayPal log.
     * Move log under different name.
     *
     * @param string $message
     */
    public function retryTest($message = '')
    {
        if (false !== stripos($message, 'Timeout')) {
            $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'setLogPermissions');
            $this->callShopSC(\OxidEsales\PayPalModule\Core\Logger::class, 'log', null, null, [$this->getHtmlSource()]);
            $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'renamePayPalLog');
        }
        parent::retryTest($message);
    }

    /**
     * Set up fixture.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->clearCache();
        $this->clearCookies();
        $this->clearTemp();

        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalTransactionMode' => [
                'type' => 'select',
                'value' => 'Sale',
                'module' => 'module:oepaypal'
            ]]);

        $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'cleanPayPalLog');
    }

    /**
     * Tear down fixture.
     */
    protected function tearDown()
    {
        $this->newPayPalUserInterface = true;

        parent::tearDown();
    }

    /**
     * Login to PayPal sandbox.
     *
     * @param string $loginEmail    email to login.
     * @param string $loginPassword password to login.
     *
     * @todo wait, check that it actually logged in.
     */
    protected function loginToSandbox($loginEmail = null, $loginPassword = null)
    {
        if (!isset($loginEmail)) {
            $loginEmail = $this->getLoginDataByName('sBuyerLogin');
        }
        if (!isset($loginPassword)) {
            $loginPassword = $this->getLoginDataByName('sBuyerPassword');
        }

        if ($this->newPayPalUserInterface) {
            $this->loginToNewSandbox($loginEmail, $loginPassword);
        } else {
            $this->loginToOldSandbox($loginEmail, $loginPassword);
        }
    }

    /**
     * New sandbox login.
     *
     * @param string $loginEmail
     * @param string $loginPassword
     */
    private function loginToNewSandbox($loginEmail, $loginPassword)
    {
        $this->selectCorrectLoginFrame();

        $this->type("email", $loginEmail);
        $this->type("password", $loginPassword);
        $this->click(self::PAYPAL_LOGIN_BUTTON_ID_NEW);

        $this->selectWindow(null);
        $this->_waitForAppear('isTextPresent', $this->getLoginDataByName('sBuyerFirstName'), 3, true);
        $this->_waitForAppear('isElementPresent', "//input[@id='confirmButtonTop']", 10, true);
    }

    /**
     * Old sandbox login.
     *
     * @param string $loginEmail
     * @param string $loginPassword
     */
    private function loginToOldSandbox($loginEmail, $loginPassword)
    {
        $this->type("login_email", $loginEmail);
        $this->type("login_password", $loginPassword);
        $this->clickAndWait(self::PAYPAL_LOGIN_BUTTON_ID_OLD);
        $this->waitForItemAppear("id=continue");
    }

    /**
     * Selects shipping method in PayPal page
     *
     * @param string $method Method label
     */
    protected function selectPayPalShippingMethod($method)
    {
        $this->waitForItemAppear("id=shipping_method");
        $this->select("id=shipping_method", "label=$method");
        $this->waitForItemAppear("id=continue");
    }

    /**
     * Returns PayPal login data by variable name
     *
     * @param $varName
     *
     * @return mixed|null|string
     * @throws \Exception
     */
    protected function getLoginDataByName($varName)
    {
        if (!$varValue = getenv($varName)) {
            $varValue = $this->getArrayValueFromFile($varName, __DIR__ .'/oepaypalData.php');
        }

        if (!$varValue) {
            throw new \Exception('Undefined variable: ' . $varName);
        }

        return $varValue;
    }

    /**
     * Standard PayPal uses new User Interface.
     */
    protected function standardCheckoutWillBeUsed()
    {
        $this->newPayPalUserInterface = true;
    }

    /**
     * New PayPal interface uses iframe for user login.
     */
    private function selectCorrectLoginFrame()
    {
        if ($this->newPayPalUserInterface) {
            $this->frame(self::PAYPAL_FRAME_NAME);
        }
    }

    /**
     * Go to PayPal page by clicking Express Checkout button.
     *
     * @param string $expressCheckoutButtonIdentification PayPal Express Checkout button identification.
     */
    protected function selectPayPalExpressCheckout($expressCheckoutButtonIdentification = "paypalExpressCheckoutButton")
    {
        // Commented cause it didn't run:
        // $this->waitForItemAppear("//input[@id='{$expressCheckoutButtonIdentification}']", 10, true);
        $this->expressCheckoutWillBeUsed();
        $this->click($expressCheckoutButtonIdentification);
        $this->waitForPayPalPage();
    }

    /**
     * Express Checkout uses old User Interface.
     */
    protected function expressCheckoutWillBeUsed()
    {
        $this->newPayPalUserInterface = false;
    }

    /**
     * PayPal has two pages with different layout.
     */
    protected function clickPayPalContinue()
    {
        if ($this->newPayPalUserInterface) {
            $this->clickPayPalContinueNewPage();
        } else {
            $this->clickPayPalContinueOldPage();
        }

        //we should be redirected back to shop at this point
        $this->_waitForAppear('isElementPresent', "id=breadCrumb", 10, true);
    }

    /**
     * Continue button is visible before PayPal does callback.
     * Then it becomes invisible while PayPal does callback.
     * Button appears when PayPal gets callback result.
     */
    private function clickPayPalContinueNewPage()
    {
        $this->waitForItemAppear("//input[@id='confirmButtonTop']", 10, true);
        $this->waitForEditable("id=confirmButtonTop");
        $this->clickAndWait("id=confirmButtonTop");
    }

    /**
     * Continue button is visible before PayPal does callback.
     * Then it becomes invisible while PayPal does callback.
     * Button appears when PayPal gets callback result.
     */
    private function clickPayPalContinueOldPage()
    {
         $this->waitForItemAppear("//input[@id='continue']", 10, false);
         $this->waitForItemAppear("//input[@id='continue_abovefold']", 3, false);
         $this->waitForEditable("id=continue");
         if ($this->isElementPresent("id=continue_abovefold") && $this->isEditable("id=continue_abovefold")) {
           $this->clickAndWait("id=continue_abovefold");
         } else {
            $this->clickAndWait("id=continue");
         }
    }

    /**
     * Waits until PayPal page is loaded.
     * Decides if try to wait by new or old user interface.
     */
    private function waitForPayPalPage()
    {
        $this->checkForFailedToOpenPayPalPageError();

        if ($this->newPayPalUserInterface) {
            $this->waitForPayPalNewPage();
        } else {
            $this->waitForPayPalOldPage();
        }
    }

    /**
     * Waits until PayPal page is loaded.
     * PayPal page is external and not Shop related.
     * New user interface has iFrame which must be selected.
     */
    private function waitForPayPalNewPage()
    {
        $this->waitForElement("id=injectedUnifiedLogin", 10, true);

        // We sometimes end up on the old PayPal login page
        if (!$this->isElementPresent("id=injectedUnifiedLogin") && $this->isElementPresent(self::PAYPAL_LOGIN_BUTTON_ID_OLD)) {
            $this->newPayPalUserInterface = false;
            return;
        }

        $this->selectCorrectLoginFrame();

        $this->waitForElement(self::PAYPAL_LOGIN_BUTTON_ID_NEW);

        $this->selectWindow(null);
    }

    /**
     * Waits until PayPal page is loaded.
     * PayPal page is external and not Shop related.
     */
    private function waitForPayPalOldPage()
    {
        $this->waitForElement(self::PAYPAL_LOGIN_BUTTON_ID_OLD);
    }

    /**
     * @param string $basketPrice
     * @param string $capturedPrice
     */
    protected function checkOrderPayPalTabPricesCorrect($basketPrice, $capturedPrice)
    {
        $this->assertEquals("{$basketPrice} EUR", $this->getOrderPayPalTabBasketPrice(), "Full amount is not displayed in admin PayPal tab");
        $this->assertEquals("{$capturedPrice} EUR", $this->getOrderPayPalTabPrice(3, self::IDENTITY_COLUMN_ORDER_PAYPAL_TAB_PRICE_VALUE), "Captured amount is not displayed in admin PayPal tab");
        $this->assertEquals("0,00 EUR", $this->getOrderPayPalTabPrice(4, self::IDENTITY_COLUMN_ORDER_PAYPAL_TAB_PRICE_VALUE), "Refunded amount is not displayed in admin PayPal tab");
        $this->assertEquals("$capturedPrice EUR", $this->getOrderPayPalTabPrice(5, self::IDENTITY_COLUMN_ORDER_PAYPAL_TAB_PRICE_VALUE), "Resulting amount is not displayed in admin PayPal tab");
        $this->assertEquals("0,00 EUR", $this->getOrderPayPalTabPrice(6, self::IDENTITY_COLUMN_ORDER_PAYPAL_TAB_PRICE_VALUE), "Voided amount is not displayed in admin PayPal tab");
    }

    private function getOrderPayPalTabBasketPrice()
    {
        return $this->getOrderPayPalTabPrice(2, 2);
    }

    /**
     * @param integer $row
     * @param integer $column
     *
     * @return bool
     */
    private function getOrderPayPalTabPrice($row, $column)
    {
        return $this->getText("//table[@class='paypalActionsTable']/tbody/tr[" . $row . "]/td[" . $column . "]/b");
    }

    /**
     * @param $actionName
     * @param $amount
     * @param $paypalStatus
     */
    protected function checkOrderPayPalTabHistoryCorrect($actionName, $amount, $paypalStatus)
    {
        $this->assertEquals($actionName, $this->getText("//table[@id='historyTable']/tbody/tr[2]/td[2]"), "Money status is not displayed in admin PayPal tab");
        $this->assertEquals("{$amount} EUR", $this->getText("//table[@id='historyTable']/tbody/tr[2]/td[3]"));
        $this->assertEquals($paypalStatus, $this->getText("//table[@id='historyTable']/tbody/tr[2]/td[4]"), "Money status is not displayed in admin PayPal tab");
    }

    /**
     * @param $quantity
     * @param $productNumber
     * @param $productTitle
     * @param $productGrossPrice
     * @param $productTotalPrice
     * @param $productVat
     */
    protected function checkOrderPayPalTabProductsCorrect($quantity, $productNumber, $productTitle, $productGrossPrice, $productTotalPrice, $productVat)
    {
        $this->assertEquals($quantity, $this->getText("//tr[@id='art.1']/td"));
        $this->assertEquals($productNumber, $this->getText("//tr[@id='art.1']/td[2]"));
        $this->assertEquals($productTitle, $this->getText("//tr[@id='art.1']/td[3]"));
        $this->assertEquals("{$productGrossPrice} EUR", $this->getText("//tr[@id='art.1']/td[4]"));
        $this->assertEquals("{$productTotalPrice} EUR", $this->getText("//tr[@id='art.1']/td[5]"));
        $this->assertEquals($productVat, $this->getText("//tr[@id='art.1']/td[6]"));
    }

    /**
     * Validate last request/response pair in log.
     *
     * @param array $assertRequest  Values to assert.
     * @param array $assertResponse Values to assert.
     * @param bool  $cleanLog       Clean log after check.
     */
    protected function assertLogData($assertRequest, $assertResponse, $cleanLog = true)
    {
        $data = $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'getLogData');

        // last thing in log has to be the response from PayPal
        $response = array_pop($data);
        $sessionId = $response->sid;
        $this->assertEquals('response', $response->type);
        $this->assertLogValues($response->data, $assertResponse);

        // following last element has to be the related request
        $request = array_pop($data);
        $this->assertEquals('request', $request->type);
        $this->assertEquals($sessionId, $response->sid);
        $this->assertLogValues($request->data, $assertRequest);

        if ($cleanLog) {
            $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'cleanPayPalLog');
        }
    }

    /**
     * Validate log data.
     *
     * @param array $logData
     * @param array $expected
     */
    private function assertLogValues($logData, $expected)
    {
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $logData[$key]);
        }
    }

    /**
     * Finish payment process part that's to be done on PayPal page.
     *
     * @param bool $expressCheckout
     * @param bool $usBuyer
     */
    protected function payWithPayPal($expressCheckout = false, $usBuyer = false)
    {
        $loginMail = $this->getLoginDataByName('sBuyerLogin');

        //we might be automatically get logged in by PayPal, check before trying to log in again
        $this->selectWindow(null);
        // Commented cause it didn't run:
        // $this->_waitForAppear('isTextPresent', $this->getLoginDataByName('sBuyerFirstName'), 10, true);
        if (!$this->isElementPresent("//input[@id='confirmButtonTop']") && !$this->isElementPresent("//input[@id='continue']")) {
            if (!$expressCheckout) {
                // Commented cause it didn't run:
                // $this->waitForPayPalPage();
            }
            if ($usBuyer) {
                $loginMail = $this->getLoginDataByName('sBuyerUSLogin');
            }
            $this->loginToSandbox($loginMail);
        }
        $this->clickPayPalContinue();
    }

    /**
     * Wait, till the login to the PayPal sandbox is completed.
     */
    protected function waitForLoggedInToPayPalSandbox()
    {
        $this->waitForItemAppear("id=continue");
        $this->waitForItemAppear("id=displayShippingAmount");
    }

    /**
     * Click on the link to go to the first step in the OXID eShop basket.
     */
    protected function clickFirstStepInShopBasket()
    {
        $this->clickAndWait("link=1. Cart");
    }

    /**
     * Click on the link to go to the next step in the OXID eShop basket.
     */
    protected function clickNextStepInShopBasket()
    {
        $this->clickAndWait("//button[text()='Continue to the next step']");
    }

    protected function loginToShopFrontend()
    {
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->waitForElement("paypalExpressCheckoutButton", "PayPal express button not displayed in the cart");
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed");
        $this->assertElementPresent("//tr[@id='cartItem_1']/td[3]/div[2]");
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("Shipping costs:", "Shipping costs is not displayed correctly");
        $this->assertTextPresent("?");
        $this->assertTrue($this->isChecked("//input[@name='displayCartInPayPal' and @value='1']"));
        $this->assertTextPresent("Display cart in PayPal", "Text:Display cart in PayPal for checkbox not displayed");
        $this->assertElementPresent("displayCartInPayPal", "Checkbox:Display cart in PayPal not displayed");
    }

    protected function assertAllAvailableShippingMethodsAreDisplayed()
    {
        $this->assertTextPresent("Test Paypal:6 hour", "Not all available shipping methods is displayed");
        $this->assertTextPresent("Test Paypal:12 hour", "Not all available shipping methods is displayed");
        $this->assertTextPresent("Standard", "Not all available shipping methods is displayed");
        $this->assertTextPresent("Example Set1: UPS 48 hours", "Not all available shipping methods is displayed");
        $this->assertTextPresent("Example Set2: UPS Express 24 hours", "Not all available shipping methods is displayed");
    }

    protected function waitForShop()
    {
        $this->waitForItemAppear("id=breadCrumb");
    }

    /**
     * Select Belgium as the delivery address, if it not already is.
     */
    private function selectDeliveryAddressBelgium()
    {
        // @todo: introduce language independent if!
        if (!$this->isTextPresent("Test address in Belgium 15, Antwerp, Belgium")) {
            // adding new address (Belgium) to address list
            $this->clickAndWait("id=addShipAddress");
            $this->select("country_code", "label=Belgium");
            $this->type("id=shipping_address1", "Test address in Belgium 15");
            $this->type("id=shipping_city", "Antwerp");

            // returning to address list
            $this->click("//input[@id='continueBabySlider']");
        }

        $this->click("//label[@class='radio' and contains(.,'Test address in Belgium 15, Antwerp, Belgium')]/input");
    }

    protected function changeCountryInBasketStepTwo($country)
    {
        $this->click('userChangeAddress');

        $this->waitForElement("//select[@id='invCountrySelect']/option[text()='$country']");
        $this->select("//select[@id='invCountrySelect']", "label=$country");

        $this->clickNextStepInShopBasket();
    }

    /**
     * Handle express checkout on PayPal page.
     *
     * @param string $expressCheckoutButtonIdentification
     * @param bool   $usBuyer
     */
    protected function payWithPayPalExpressCheckout($expressCheckoutButtonIdentification = 'paypalExpressCheckoutButton', $usBuyer = false)
    {
        // Commented cause it didn't run:
        // $this->_waitForAppear('isElementPresent', "//input[@class='{$expressCheckoutButtonIdentification}']", 3, true);
        $this->expressCheckoutWillBeUsed();
        $this->click($expressCheckoutButtonIdentification);
        $this->payWithPayPal(true, $usBuyer);
    }

    /**
     * PayPal page might refuse connection and redirect back to the Shop with an error message.
     * This might happen for example when credentials are wrong.
     */
    protected function checkForFailedToOpenPayPalPageError()
    {
        $this->assertTextNotPresent("Security header is not valid", "Did not succeed to open PayPal page.");
        $this->assertTextNotPresent("ehlermeldung von PayPal", "Did not succeed to open PayPal page.");
    }
}
