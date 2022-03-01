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

namespace OxidEsales\PayPalModule\Tests\Codeception;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Admin\AdminLoginPage;
use OxidEsales\Codeception\Admin\AdminPanel;
use OxidEsales\Codeception\Page\Home;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Facts\Facts;
use OxidEsales\PayPalModule\Tests\Codeception\Admin\PayPalOrder;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    protected $maxRetries = 20;

    /**
     * Open shop first page.
     */
    public function openShop()
    {
        $I = $this;
        $homePage = new Home($I);
        $I->amOnPage($homePage->URL);

        return $homePage;
    }

    /**
     * @return \OxidEsales\Codeception\Admin\AdminPanel
     */
    public function openAdminLoginPage()
    {
        $I = $this;
        $adminPanel = new AdminLoginPage($I);
        $I->amOnPage($adminPanel->URL);

        return $adminPanel;
    }

    public function activatePaypalModule(): void
    {
        $rootPath = (new Facts())->getShopRootPath();
        $possiblePaths = [
            '/bin/oe-console',
            '/vendor/bin/oe-console',
        ];
        foreach ($possiblePaths as $path) {
            if (is_file($rootPath . $path)) {
                exec($rootPath . $path . ' oe:module:activate oepaypal');
                return;
            }
        }
        throw new \Exception('Could not find script "/bin/oe-console" to activate module');
    }

    public function deactivatePaypalModule(): void
    {
        $rootPath = (new Facts())->getShopRootPath();
        $possiblePaths = [
            '/bin/oe-console',
            '/vendor/bin/oe-console',
        ];
        foreach ($possiblePaths as $path) {
            if (is_file($rootPath . $path)) {
                exec($rootPath . $path . ' oe:module:deactivate oepaypal');
                return;
            }
        }
        throw new \Exception('Could not find script "/bin/oe-console" to deactivate module');
    }

    /**
     * Switch to PayPal Installment banner iframe
     * and check if body contains elements.
     */
    public function seePayPalInstallmentBanner()
    {
        $I = $this;

        $I->waitForElement("//div[contains(@id, 'paypal-installment-banner-container')]//iframe");
        $I->switchToIFrame("//div[contains(@id, 'paypal-installment-banner-container')]//iframe");
        $I->waitForElementVisible("//body[node()]");

        // Switch back to main window, otherwise we will stay in PP banner iframe
        $I->switchToIFrame();

        return $this;
    }

    public function activateFlowTheme()
    {
        $I = $this;

        //prepare testing with flow theme
        $I->updateConfigInDatabase('sTheme', 'flow', 'str');
        $I->updateConfigInDatabase('oePayPalBannersStartPageSelector', '#wrapper .row', 'str');
        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPageSelector', '#content .page-header .clearfix', 'str');
        $I->updateConfigInDatabase('oePayPalBannersProductDetailsPageSelector', '.detailsParams', 'str');
        $I->updateConfigInDatabase('oePayPalBannersPaymentPageSelector', '.checkoutSteps ~ .spacer', 'str');
    }

    public function activateWaveTheme()
    {
        $I = $this;

        //prepare testing with wave theme
        $I->updateConfigInDatabase('sTheme', 'wave', 'str');
        $I->updateConfigInDatabase('oePayPalBannersStartPageSelector', '#wrapper .container', 'str');
        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPageSelector', '.page-header', 'str');
        $I->updateConfigInDatabase('oePayPalBannersProductDetailsPageSelector', '#detailsItemsPager', 'str');
        $I->updateConfigInDatabase('oePayPalBannersPaymentPageSelector', '.checkout-steps', 'str');
    }

    /**
     * @param float $amount
     */
    public function seePayPalInstallmentBannerInFlowAndWaveTheme(float $amount = 0, $breadCrumbText = '')
    {
        $I = $this;

        //Check installment banner body in Flow theme
        $I->reloadPage();
        $I->waitForPageLoad();
        if ($breadCrumbText) {
            $I->see($breadCrumbText);
        }
        $I->dontSee(Translator::translate('ERROR_MESSAGE_ARTICLE_ARTICLE_NOT_BUYABLE'));
        $I->seePayPalInstallmentBanner();
        $I->checkInstallmentBannerData($amount);

        //Check installment banner body in Wave theme
        $this->activateWaveTheme();
        $I->reloadPage();
        $I->waitForPageLoad();
        if ($breadCrumbText) {
            $I->see($breadCrumbText);
        }
        $I->dontSee(Translator::translate('ERROR_MESSAGE_ARTICLE_ARTICLE_NOT_BUYABLE'));
        $I->seePayPalInstallmentBanner();
        $I->checkInstallmentBannerData($amount);
    }

    /**
     * @param float  $amount
     * @param string $ratio
     * @param string $currency
     */
    public function checkInstallmentBannerData(float $amount = 0, string $ratio = '20x1', string $currency = 'EUR')
    {
        $I = $this;

        $onloadMethod = $I->executeJS("return PayPalMessage.toString()");
        $I->assertRegExp($this->prepareMessagePartRegex(sprintf("amount: %s", $amount)), $onloadMethod);
        $I->assertRegExp($this->prepareMessagePartRegex(sprintf("ratio: '%s'", $ratio)), $onloadMethod);
        $I->assertRegExp($this->prepareMessagePartRegex(sprintf("currency: '%s'", $currency)), $onloadMethod);
    }

    /**
     * @return array
     */
    public function getExistingUserData(): array
    {
        return Fixtures::get('user');
    }

    /**
     * @return string
     */
    public function getExistingUserName(): string
    {
        return $this->getExistingUserData()['oxusername'];
    }

    /**
     * @return string
     */
    public function getExistingUserPassword(): string
    {
        return Fixtures::get('userPassword');
    }

    /**
     * @return string
     */
    public function getDemoUserName(): string
    {
        return Fixtures::get('demoUserName');
    }

    /**
     * Wrap the message part in message required conditions
     *
     * @param string $part
     * @return string
     */
    protected function prepareMessagePartRegex($part)
    {
        return "/paypal.Messages\(\{[^}\)]*{$part}/";
    }

    public function openAdmin(): AdminLoginPage
    {
        $I = $this;
        $adminLogin = new AdminLoginPage($I);
        $I->amOnPage($adminLogin->URL);
        return $adminLogin;
    }

    public function loginAdmin(): AdminPanel
    {
        $adminPage = $this->openAdmin();
        $admin = Fixtures::get('adminUser');
        return $adminPage->login($admin['userLoginName'], $admin['userPassword']);
    }

    public function getShopUrl(): string
    {
        $facts = new Facts();

        return $facts->getShopUrl();
    }

    public function switchToLastWindow()
    {
        $I = $this;
        $I->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
            $handles=$webdriver->getWindowHandles();
            $last_window = end($handles);
            $webdriver->switchTo()->window($last_window);
            $size = new \Facebook\WebDriver\WebDriverDimension(1920, 1280);
            $webdriver->manage()->window()->setSize($size);
        });
    }

    /**
     * @param AcceptanceTester $I
     * @param int              $orderNumber
     *
     * @return PayPalOrder
     * @throws \Exception
     */
    public function openAdminOrder(int $orderNumber, int $retry = 0)
    {
        $I = $this;

        if ($retry >= $this->maxRetries) {
            $I->makeScreenshot();
            $I->makeHtmlSnapshot();
            $I->markTestIncomplete('Did not manage to open the PayPal order tab');
        }

        $adminLoginPage = $I->openAdminLoginPage();
        $adminUser = Fixtures::get('adminUser');
        $adminPanel = $adminLoginPage->login($adminUser['userLoginName'], $adminUser['userPassword']);

        $ordersList = $adminPanel->openOrders($adminPanel);

        $ordersList->searchByOrderNumber($orderNumber);
        $I->wait(1);
        if ($I->seePageHasElement('//a[text()="' . $orderNumber . '"]')) {
            $I->retryClick('//a[text()="' . $orderNumber . '"]');
        } elseif ($I->seePageHasElement('//a[text()="PayPal"]')) {
            $I->retryClick('//a[text()="PayPal"]');
        } else {
            $this->openAdminOrder($orderNumber, ++$retry);
        }

        $I->wait(1);
        $paypalOrder = new PayPalOrder($I);
        if (!$I->seePageHasElement($paypalOrder->paypalTab)) {
            $this->openAdminOrder($orderNumber, ++$retry);
        }

        $I->retryClick($paypalOrder->paypalTab);
        $I->selectEditFrame();

        if (!$I->seePageHasElement($paypalOrder->captureButton)) {
            $this->openAdminOrder($orderNumber, ++$retry);
        }

        return $paypalOrder;
    }
}
