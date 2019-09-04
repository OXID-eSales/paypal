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
 * @todo add dependency between external tests. If one fails next should not start.
 */
abstract class BaseAcceptanceTestCase extends \OxidEsales\TestingLibrary\AcceptanceTestCase
{
    const TEST_LOGFILE_NAME = 'oepaypal_acceptance_log.txt';

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
    protected $_iWaitTimeMultiplier = 3;

    protected $retryTimes = 1;

    protected static $doStopMink = true;

    /**
     * Known test failure messages and some identifiers.
     * If they match, the tests are broken because of external issues with PayPal sandbox
     * and can safely be skipped for this run.
     *
     * @var array
     */
    protected $knownExceptions = [
        "Element 'login_email' was not found" ,
        "Element 'id=submitLogin' was not found!",
        "Element 'id=paypalExpressCheckoutDetailsButton' was not found!",
        "Timeout waiting for 'id=continue'" ,
        "Timeout waiting for 'id=submitLogin'",
        "Timeout waiting for 'Bestellen ohne Registrierung'",
        "Timeout waiting for 'cancel_return'",
        "Timeout waiting for '2 x Test product 1'",
        "Timeout waiting for 'Thank you'",
        "Timeout waiting for",
        "Entschuldigung",
        "Leider ist ein Fehler aufgetreten"
    ];

    /**
     * If in case of known error message any of these identifier groups
     * can be found, the test failure was ver likely caused by PayPal sandbox.
     *
     * @var array
     */
    protected $failIdentifiersGroups = [
        'internal_error'                         => [
            ['isTextPresent', 'internal'],
            ['isTextPresent', 'error'],
            ['isTextPresent', 'webmaster@paypal.com']
        ],
        'dispatch_error_en'                      => [
            ['isTextPresent', 'your last action could not be completed'],
            ['isTextPresent', 'Dispatch Error'],
            ['isTextPresent', 'PayPal']
        ],
        'dispatch_error_de'                      => [
            ['isTextPresent', 'letzte Aktion konnte leider nicht abgeschlossen werden'],
            ['isTextPresent', 'Dispatch Error'],
            ['isTextPresent', 'PayPal']
        ],
        'internal_error_sandbox'                 => [
            ['isTextPresent', 'internal'],
            ['isTextPresent', 'error'],
            ['isTextPresent', 'sandbox.paypal.com']
        ],
        'redirect_to_PP_failed_de'               => [
            ['isTextPresent', 'Warenkorb'],
            ['isElementPresent', 'paypalExpressCheckoutButton']
        ],
        'redirect_to_PP_failed_en'               => [
            ['isTextPresent', 'Cart'],
            ['isElementPresent', 'paypalExpressCheckoutButton']
        ],
        'not_logged_in_redirect_to_PP_failed_de' => [
            ['isTextPresent', 'Bestellen ohne Registrierung'],
            ['isElementPresent', 'paypalExpressCheckoutButton']
        ],
        'curl_error_sandbox'                     => [
            ['isTextPresent', 'Curl'],
            ['isTextPresent', 'error'],
            ['isTextPresent', '56']
        ],
        'technical_problems'                     => [
            ['isTextPresent', 'not'],
            ['isTextPresent', 'available'],
            ['isTextPresent', 'technical'],
            ['isTextPresent', 'problems']
        ]
    ];

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
            ),
            'OEPayPalDisableIPN' => array(
                'type' => 'str',
                'value' => true,
                'module' => 'module:oepaypal'
            ),
        ));

        $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'cleanPayPalLog');

        $language = oxNew(\OxidEsales\Eshop\Core\Language::class);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Language::class, $language);
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
        if (false !== stripos($message, '  Timeout')) {
            $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'setLogPermissions');
            $this->callShopSC(\OxidEsales\PayPalModule\Core\Logger::class, 'log', null, null, [$this->getHtmlSource()]);
            $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'renamePayPalLog');
        }
        parent::retryTest($message);
    }

    /**
     * Asserts that text is not present.
     *
     * @param string $text    text to search
     * @param string $message fail message
     * @return void
     */
    public function assertTextNotPresent($text, $message = '')
    {
        $this->addToAssertionCount(1);
        parent::assertTextNotPresent($text, $message);
    }

    /**
     * Fix for showing stack trace with phpunit 3.6 and later
     *
     * @param \Throwable $exception
     *
     * @throws Throwable
     */
    protected function onNotSuccessfulTest(\Throwable $exception)
    {
        try {
            self::$doStopMink = false;
            parent::onNotSuccessfulTest($exception);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $skipExplanation = $this->canTestBeSkipped($message);
            if (!is_null($skipExplanation)) {
                $this->logTestDebugMessage($skipExplanation);
                $exception = new \PHPUnit\Framework\SkippedTestError($skipExplanation);
            } else {
                $this->logTestDebugMessage(__FUNCTION__ . ' ' . get_class($exception) . ' ' . $message);
            }

            self::$doStopMink = true;
            self::stopMinkSession();
            throw $exception;
        }
        //if we reached this point, we still have a budget of open test repeats.
    }

    /**
     * Check if failure is likely to be caused by PayPal sandbox.
     * Not null return value means we can skip the failing test for this run.
     *
     * @param string $message
     *
     * @return string|null
     */
    protected function canTestBeSkipped($message)
    {
        $skipInfo = null;
        foreach ($this->knownExceptions as $known) {
            if (false !== strpos($message, $known)) {
                $failIdentifiersGroups = $this->failIdentifiersGroups;
                if ('Timeout waiting for' == $known) {
                    unset($failIdentifiersGroups ['redirect_to_PP_failed_de']);
                    unset($failIdentifiersGroups ['redirect_to_PP_failed_en']);
                    unset($failIdentifiersGroups ['not_logged_in_redirect_to_PP_failed_de']);
                }
                $identified = $this->identifyFailure($failIdentifiersGroups );
                if (!is_null($identified)) {
                    $skipInfo = $known . ' - Skipped automatically due to external issue with PayPal sandbox: ' . $identified;
                }
            }
        }
        return $skipInfo;
    }

    /**
     * Check if any group of failure cause identifiers can be found on page.
     *
     * @param array $failIdentifiersGroups
     *
     * @return null|string
     */
    protected function identifyFailure(array $failIdentifiersGroups)
    {
        $identified = null;
        $this->selectWindow(null);
        foreach ($failIdentifiersGroups as $key => $group) {
            $verified = true;
            foreach ($group as $checkFor) {
                $method = $checkFor[0];
                $argument = $checkFor[1];

                $verified &= $this->$method($argument);
            }
            if ($verified) {
                $identified = $key;

                continue;
            }
        }
        return $identified;
    }

    /**
     * Stops Mink session if it is started.
     */
    public static function stopMinkSession()
    {
        if (self::$doStopMink) {
            parent::stopMinkSession();
        }
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

        $this->moveTemplateBlockToEnd();

        $this->callShopSC('oxConfig', null, null, [
            'sOEPayPalTransactionMode' => [
                'type' => 'select',
                'value' => 'Sale',
                'module' => 'module:oepaypal'
            ],
            'sOEPayPalSandboxSignature' => [
                'type' => 'str',
                'value' => $this->getLoginDataByName('sOEPayPalSandboxSignature'),
                'module' => 'module:oepaypal'
            ],
        ]);

        $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'cleanPayPalLog');

        $language = oxNew(\OxidEsales\Eshop\Core\Language::class);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Language::class, $language);
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
     * @param bool   $isRetry       Retry login
     *
     */
    protected function loginToSandbox($loginEmail = null, $loginPassword = null, $isRetry = false)
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

        // try again
        if (!$this->isLoggedInToPP() && !$isRetry) {
            $this->loginToSandbox($loginEmail, $loginPassword, true);
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

        $this->loginToOldSandbox($loginEmail, $loginPassword);
    }

    /**
     * Old sandbox login.
     *
     * @param string $loginEmail
     * @param string $loginPassword
     */
    protected function loginToOldSandbox($loginEmail, $loginPassword)
    {
        $debug = '';

        $element = $this->getElementLazy('login_email', false);
        if ($element) {
            $element->setValue($loginEmail);
            $debug .= 'login_email--';
        }
        $element = $this->getElementLazy('email', false);
        if ($element) {
            $element->setValue($loginEmail);
            $debug .= 'email--';
        }

        //We might get a next button before we can enter the password
        $element = $this->getElementLazy("id=btnNext", false);
        if ($element) {
            $element->click();
            $this->_waitForAppear('isElementPresent', "//input[@id='password']", 5, true);
        }

        $element = $this->getElementLazy('login_password', false);
        if ($element) {
            $element->setValue($loginPassword);
            $debug .= 'login_password--';
        }
        $element = $this->getElementLazy('password', false);
        if ($element) {
            $element->setValue($loginPassword);
            $debug .= 'password--';
        }

        $loginFound = $this->clickLogin();

        $this->selectWindow(null);
        $this->_waitForAppear('isElementPresent', "//input[@id='continue']", 5, true);
        $this->_waitForAppear('isElementPresent', "//input[@id='confirmButtonTop']", 5, true);
        $this->_waitForAppear('isTextPresent', $this->getLoginDataByName('sBuyerFirstName'), 5, true);
        $this->_waitForAppear('isTextPresent', $loginEmail, 5, true);

        if ((false === $loginFound)
             && !$this->isTextPresent($this->getLoginDataByName('sBuyerFirstName'))
             && !$this->isTextPresent($loginEmail)
        ) {
            $this->markTestIncomplete('Cannot find login button. Login form fields found: ' . $debug);
        }
    }

    /**
     * Test helper, click login button if one can be found.
     *
     * @return bool
     */
    protected function clickLogin()
    {
        $loginFound = false;
        $xpath = "//button[@type='submit' and @id='btnLogin' and (contains(., 'Log In') or contains(., 'Einloggen'))]";
        $this->waitForItemAppear($xpath, 5, true);
        $element = $this->getElementLazy($xpath, false);
        if ($element) {
            $loginFound = true;
            $element->click();
        } else {
            $element = $this->getElementLazy(self::PAYPAL_LOGIN_BUTTON_ID_OLD, false);
            if ($element) {
                $loginFound = true;
                $element->click();
            }
        }
        return $loginFound;
    }

    /**
     * Selects shipping method in PayPal page
     *
     * @param string $method Method label
     */
    protected function selectPayPalShippingMethod($method)
    {
        $this->waitForItemAppear("id=shipMethod");
        $this->select("id=shipMethod", "label=$method");
        $this->_waitForAppear('isElementPresent', "//input[@id='continue']", 5, true);
        $this->_waitForAppear('isElementPresent', "//input[@id='confirmButtonTop']", 5, true);
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
        // old frontend, nothing to be done here.
        if (!$this->newPayPalUserInterface) {
            return;
        }

        $element = $this->getElementLazy('id=loginSection', false);
        if ($element) {
            if ($this->isTextPresent('Sie haben schon ein PayPal-Konto')) {
                $this->click("link=Einloggen");
            } else if ($this->isTextPresent('Have a PayPal account')) {
                $this->click("link=Log In");
            }
            return;
        }

        //still here? try this
        $frameSelector = "//iframe[@name='injectedUl']";

        $this->_waitForAppear('isElementPresent', $frameSelector, 5, true);

        if ($this->isElementPresent($frameSelector)) {
            $this->frame(self::PAYPAL_FRAME_NAME);
        } else {
            $this->markTestIncomplete('PayPal is not giving us the normal page, we miss the iframe...');
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
     * Express Checkout uses old User Interface or not.
     *
     * @param bool $useStandard
     */
    protected function expressCheckoutWillBeUsed($useExpress = true)
    {
        $this->newPayPalUserInterface = !$useExpress;
    }

    /**
     * PayPal has two pages with different layout.
     */
    protected function clickPayPalContinue()
    {
        //For the US buyer we might have one additional confirm button
        $this->waitForItemAppear("//button[text()='Continue']", 5, true);
        if ($this->isElementPresent("//button[text()='Continue']") && $this->isEditable("//button[text()='Continue']") ) {
            $this->clickAndWait("//button[text()='Continue']");
        }

        $this->waitForItemAppear("//input[@id='continue_abovefold']", 5, true);
        $this->waitForEditable("id=continue", 5, true);
        $this->waitForEditable("id=confirmButtonTop", 5, true);

        if ($this->isElementPresent("id=continue_abovefold") && $this->isEditable("id=continue_abovefold")) {
            $this->clickAndWait("id=continue_abovefold");
        } elseif($this->isElementPresent("id=continue") && $this->isEditable("id=continue")) {
            $this->clickAndWait("id=continue");
        } elseif ($this->isElementPresent("id=confirmButtonTop")) {
            $this->clickAndWait("id=confirmButtonTop");
        } elseif ($this->isElementPresent("id=retryLink")){
            $this->markTestIncomplete('PayPal is showing us their retry link so the have some internal problems.');
        }

        //we should be redirected back to shop at this point
        $this->_waitForAppear('isElementPresent', "id=breadCrumb", 5, true);
        $this->assertTrue($this->isElementPresent("id=breadCrumb"));
    }

    /**
     * Continue button is visible before PayPal does callback.
     * Then it becomes invisible while PayPal does callback.
     * Button appears when PayPal gets callback result.
     */
    private function clickPayPalContinueNewPage()
    {
        $this->assertTrue($this->isElementPresent("id=confirmButtonTop"));
        $this->click("id=confirmButtonTop");
    }

    /**
     * Continue button is visible before PayPal does callback.
     * Then it becomes invisible while PayPal does callback.
     * Button appears when PayPal gets callback result.
     */
    private function clickPayPalContinueOldPage()
    {
         $this->waitForItemAppear("//input[@id='continue_abovefold']", 5, true);
         $this->waitForEditable("id=continue", 5, true);
         $this->waitForEditable("id=confirmButtonTop", 5, true);

         if ($this->isElementPresent("id=continue_abovefold") && $this->isEditable("id=continue_abovefold")) {
             $this->clickAndWait("id=continue_abovefold");
         } elseif($this->isElementPresent("id=continue") && $this->isEditable("id=continue")) {
             $this->clickAndWait("id=continue");
         } elseif ($this->isElementPresent("id=confirmButtonTop")) {
             $this->clickAndWait("id=confirmButtonTop");
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
        $this->_waitForAppear('isElementPresent', "//input[@id='submitLogin']", 5, true);
        $this->_waitForAppear('isElementPresent', "//input[@id='continue']", 5, true);
        $this->_waitForAppear('isTextPresent', $this->getLoginDataByName('sBuyerFirstName'), 5, true);
        $this->_waitForAppear('isElementPresent', "//input[@id='confirmButtonTop']", 5, true);
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
            $actualValue = isset($logData[$key]) ? $logData[$key] : null;
            $this->assertEquals($value, $actualValue);
        }
    }

    /**
     * Finish payment process part that's to be done on PayPal page.
     *
     * @param bool $usBuyer
     * @param bool $doLogOut
     */
    protected function payWithPayPal($usBuyer = false, $doLogOut = false)
    {
        $this->checkforRetry();

        $loginMail = $this->getLoginDataByName('sBuyerLogin');
        $loginMail = $usBuyer? $this->getLoginDataByName('sBuyerUSLogin') : $loginMail;
        $loginPassword = $this->getLoginDataByName('sBuyerPassword');

        if ($doLogOut) {
            $this->cancelPayPal();
            $this->clickAndWait('paypalExpressCheckoutButton');
        }

        $this->logMeIntoSandbox($loginMail, $loginPassword);
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
        $this->waitForElement("paypalExpressCheckoutButton");
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

    /**
     * Change invoice country.
     *
     * @param string $country
     */
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
     * @param bool   $doLogOut
     */
    protected function payWithPayPalExpressCheckout($expressCheckoutButtonIdentification = 'paypalExpressCheckoutButton', $usBuyer = false, $doLogOut = false)
    {
        // Commented cause it didn't run:
        // $this->_waitForAppear('isElementPresent', "//input[@class='{$expressCheckoutButtonIdentification}']", 3, true);
        $this->expressCheckoutWillBeUsed();
        $this->clickAndWait($expressCheckoutButtonIdentification);
        $this->payWithPayPal($usBuyer, $doLogOut);
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

    /**
     * Assert, that the first name and the last name are correct on the admin order page.
     */
    protected function assureAdminOrderNameIsPresent()
    {
        $indexFirstName = $this->getFirstNameColumnIndex();
        $indexLastName = 1 + $indexFirstName;

        $this->assertEquals("Testing user acc Äß'ü", $this->getText("//tr[@id='row.1']/td[$indexFirstName]"), "Wrong user name is displayed in order");
        $this->assertEquals("PayPal Äß'ü", $this->getText("//tr[@id='row.1']/td[$indexLastName]"), "Wrong user last name is displayed in order");
    }

    /**
     * Get the column index of the first name in the admin order page table.
     *
     * @return int The column index of the first name in the admin order page table.
     */
    protected function getFirstNameColumnIndex()
    {
        $headers = $this->extractAdminOrderTableTitles();

        return 1 + array_search('First Name', $headers);
    }

    /**
     * Extract the title row pure texts of the admin orders list page.
     *
     * @return array The plain texts of the admin order list page table headline.
     */
    protected function extractAdminOrderTableTitles()
    {
        $tableBodyElement = $this->getElement("//tr[@id='row.1']/parent::tbody");
        $tableHeaderElements = $tableBodyElement->findAll("xpath", "//tr[not(@*)]//td//a");

        $headers = [];
        foreach ($tableHeaderElements as $tableHeaderElement) {
            /**
             * @var \Behat\Mink\Element\NodeElement $tableHeaderElement
             */
            $headers[] = trim($tableHeaderElement->getHtml());
        }

        return $headers;
    }

    /**
     * Move the PayPal template blocks to the end in the block chain.
     */
    protected function moveTemplateBlockToEnd()
    {
        $this->executeSql('UPDATE oxtplblocks SET OXPOS=2 WHERE OXMODULE="oepaypal"');
    }

    /**
     * Write test debug messages to separate log instead of EXCEPTION_LOG.txt.
     *
     * @param string $message
     */
    protected function logTestDebugMessage($message)
    {
        $logFile = \OxidEsales\Eshop\Core\Registry::getConfig()->getLogsDir() . DIRECTORY_SEPARATOR . self::TEST_LOGFILE_NAME;

        $time = microtime(true);
        $micro = sprintf("%06d", ($time - floor($time)) * 1000000);
        $date = new \DateTime(date('Y-m-d H:i:s.' . $micro, $time));
        $timestamp = $date->format('d M H:i:s.u Y');

        $message = "[$timestamp] " . $message . PHP_EOL;

        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Test helper.
     * Depending in time passed since last log in and on surviving cookies,
     * we might still be logged in to PP. Method checks current state.
     *
     * @return bool
     */
    private function isLoggedInToPP()
    {
        $element = $this->getElementLazy('id=loginSection', false);
        if ($element) {
            return false;
        }

        if ($this->isTextPresent($this->getLoginDataByName('sBuyerFirstName'))) {
            return true;
        }

        $element = $this->getElementLazy('link=Nicht Sie?', false);
        if ($element) {
            return true;
        }

        $element = $this->getElementLazy('link=Not you?', false);
        if ($element) {
            return true;
        }

        return false;
    }

    /**
     * Try to log out from sandbox.
     */
    protected function doPayPalLogOut()
    {
        $element = $this->getElementLazy('link=Nicht Sie?', false);
        if ($element) {
            $element->click();
            return;
        }
        $element = $this->getElementLazy('link=Not you?', false);
        if ($element) {
            $element->click();
        }
    }

    /**
     * Click cancel on payPal side to return to shop.
     */
    protected function cancelPayPal()
    {
        $element = $this->getElementLazy("id=cancelLink", false);
        if ($element) {
            $element->click();
            return;
        }
        $element = $this->getElementLazy("id=cancel_return", false);
        if ($element) {
            $element->click();
        }
    }

    /**
     * Sometimes we get a page shown by PP that things don't wort atm and we should retry.
     * Method here checks if retry link is available and if so clicks it.
     */
    private function checkforRetry()
    {
        $this->waitForElement("//body");
        if ($this->isElementPresent("//input[@id='cancelLink']")) {
            $element = $this->getElementLazy("id=retryLink", false);
            if ($element) {
                $element->click();
            }
        }
    }


    protected function logMeIntoSandbox($loginMail, $loginPassword)
    {
        $this->checkforRetry();

        //check for different landing page
        $element = $this->getElementLazy('id=loginSection', false);
        if ($element) {
            if ($this->isTextPresent('Sie haben schon ein PayPal-Konto')) {
                $this->click("link=Einloggen");
            } else if ($this->isTextPresent('Have a PayPal account')) {
                $this->click("link=Log In");
            }
        }

        //check if we need to select a frame
        $element = $this->getElementLazy('id=injectedUnifiedLogin', false);
        if ($element) {
            $frameSelector = "//iframe[@name='" . self::PAYPAL_FRAME_NAME . "']";
            $this->_waitForAppear('isElementPresent', $frameSelector, 5, true);
            if ($this->isElementPresent($frameSelector)) {
                $this->frame(self::PAYPAL_FRAME_NAME);
            } else {
                $this->markTestIncomplete('PayPal is not giving us the normal page, we miss the iframe...');
            }
        }

        $this->loginToOldSandbox($loginMail, $loginPassword);
    }
}
