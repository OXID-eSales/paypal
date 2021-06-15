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
use OxidEsales\Codeception\Page\Home;
use OxidEsales\Codeception\Module\Translation\Translator;

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

    /**
     * Set PayPal settings
     */
    public function setPayPalSettingsData()
    {
        $I = $this;
        $I->updateConfigInDatabase('blPayPalLoggerEnabled', true, 'bool');
        $I->updateConfigInDatabase('blOEPayPalSandboxMode', true, 'bool');
        $I->updateConfigInDatabase('sOEPayPalSandboxUserEmail', Fixtures::get('sOEPayPalSandboxUsername'), 'str');
        $I->updateConfigInDatabase('sOEPayPalSandboxUsername', Fixtures::get('sOEPayPalSandboxUsername'), 'str');
        $I->updateConfigInDatabase('sOEPayPalSandboxPassword', Fixtures::get('sOEPayPalSandboxPassword'), 'str');
        $I->updateConfigInDatabase('sOEPayPalSandboxSignature', Fixtures::get('sOEPayPalSandboxSignature'), 'str');
        $I->updateConfigInDatabase('oePayPalClientId', Fixtures::get('OEPayPalClientId'), 'str');
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
}
