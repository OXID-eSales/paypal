<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\Tests\Codeception\Page;

use Facebook\WebDriver\Exception\ElementNotVisibleException;
use OxidEsales\Codeception\Page\Checkout\OrderCheckout;
use OxidEsales\Codeception\Page\Page;

/**
 * Class PayPalLogin
 * @package OxidEsales\PayPalModule\Tests\Codeception\Page
 */
class PayPalLogin extends Page
{
    public $userLoginEmail = '#email';
    public $oldUserLoginEmail = '#login_email';

    public $userPassword = '#password';
    public $oldUserPassword = '#login_password';

    public $nextButton = '#btnNext';

    public $loginButton = '#btnLogin';
    public $oldLoginButton = '#submitLogin';

    public $newConfirmButton = '#confirmButtonTop';
    public $oldConfirmButton = '#continue_abovefold';

    public $oneTouchNotNowLink = '#notNowLink';

    public $spinner = '#spinner';

    public $gdprContainer = "#gdpr-container";
    public $gdprCookieBanner = "#gdprCookieBanner";
    public $acceptAllPaypalCookies = "#acceptAllButton";

    public $loginSection = "#loginSection";
    public $oldLoginSection = "#passwordSection";

    public $cancelLink = "#cancelLink";
    public $returnToShop = "#cancel_return";

    public $breadCrumb = "#breadCrumb";

    public $paymentConfirmButton = "#payment-submit-btn";
    public $globalSpinner = "//div[@data-testid='global-spinner']";
    public $preloaderSpinner = "//div[@id='preloaderSpinner']";

    public $paypalBannerContainer = "//div[@id='paypal-installment-banner-container']";

    public $backToInputEmail = "#backToInputEmailLink";
    public $errorSection = '#notifications #pageLevelErrors';
    public $splitPassword = '#splitPassword';
    public $splitEmail = '#splitEmail';
    public $rememberedEmail = "//div[@class='profileRememberedEmail']";

    /**
     * @param string $userName
     * @param string $userPassword
     *
     * @return OrderCheckout
     */
    public function loginAndCheckout(string $userName, string $userPassword): OrderCheckout
    {
        $I = $this->user;
        $usingNewLogin = true;

        $this->waitForPayPalPage();

        // In case we have cookie message, accept all cookies
        $this->acceptAllPayPalCookies();

        // new login page
        if ($I->seePageHasElement($this->userLoginEmail)) {
            $I->waitForElementVisible($this->userLoginEmail, 30);
            $I->fillField($this->userLoginEmail, $userName);
            $I->retryClick($this->nextButton);
            $this->waitForPayPalPage();

            $I->waitForElementVisible($this->userPassword, 10);
            $I->fillField($this->userPassword, $userPassword);
            $I->retryClick($this->loginButton);
        }

        // old login page
        if ($I->seePageHasElement($this->oldUserLoginEmail)) {
            $usingNewLogin = false;

            $I->waitForElementVisible($this->oldUserLoginEmail, 30);
            $I->fillField($this->oldUserLoginEmail, $userName);
            $I->waitForElementVisible($this->oldUserPassword, 5);
            $I->fillField($this->oldUserPassword, $userPassword);
            $I->retryClick($this->oldLoginButton);
        }

        $this->waitForPayPalPage();

        if ($I->seePageHasElement($this->oneTouchNotNowLink)) {
            $I->retryClick($this->oneTouchNotNowLink);
        }

        $confirmButton = $usingNewLogin ? $this->newConfirmButton : $this->oldConfirmButton;
        $I->waitForElementClickable($confirmButton, 60);

        $this->waitForPayPalPage();

        $I->retryClick($confirmButton);
        $I->waitForDocumentReadyState();

        return new OrderCheckout($I);
    }

    /**
     * @param string $userName
     * @param string $userPassword
     *
     * @return OrderCheckout
     */
    public function checkoutWithStandardPayPal(string $userName, string $userPassword): OrderCheckout
    {
        $I = $this->user;

        $this->loginToPayPal($userName, $userPassword);

        $this->confirmPayPal();

        //retry
        $this->waitForSpinnerDisappearance();
        $this->confirmPayPal();

        return new OrderCheckout($I);
    }

    public function loginToPayPal(string $userName, string $userPassword): void
    {
        $I = $this->user;

        $this->waitForPayPalPage();
        $this->removeCookieConsent();

        if ($I->seePageHasElement($this->splitPassword)
            && $I->seePageHasElement($this->rememberedEmail)
            && $I->seePageHasElement($this->backToInputEmail)
        ) {
            try {
                $I->seeAndClick($this->backToInputEmail);
                $I->waitForDocumentReadyState();
                $this->waitForSpinnerDisappearance();
                $I->waitForElementNotVisible($this->backToInputEmail);
            } catch(ElementNotVisibleException $e) {
                //nothing to be done, element was not visible
            }
        }

        if ($I->seePageHasElement($this->oldLoginSection)) {
            $I->waitForElementVisible($this->userLoginEmail, 5);
            $I->fillField($this->userLoginEmail, $userName);

            if ($I->seePageHasElement($this->nextButton)) {
                $I->retryClick($this->nextButton);
            }

            $I->waitForElementVisible($this->userPassword, 5);
            $I->fillField($this->userPassword, $userPassword);
            $I->retryClick($this->loginButton);
        }

        if ($I->seePageHasElement($this->oneTouchNotNowLink)) {
            $I->retryClick($this->oneTouchNotNowLink);
        }

        $this->waitForSpinnerDisappearance();
        $this->removeCookieConsent();
        $this->waitForSpinnerDisappearance();
        $I->wait(3);
    }

    /**
     * @param string $userName
     * @param string $userPassword
     */
    public function approveGraphqlStandardPayPal(string $userName, string $userPassword): void
    {
        $I = $this->user;

        $this->loginToPayPal($userName, $userPassword);

        $this->confirmPayPal();

        //retry
        $this->waitForSpinnerDisappearance();
        $this->confirmPayPal();

        //we should be back to shop frontend as we sent a redirect url to paypal
        $I->assertTrue($I->seePageHasElement($this->paypalBannerContainer));
    }

    public function confirmPayPal()
    {
        $I = $this->user;

        $this->waitForSpinnerDisappearance();
        $this->removeCookieConsent();

        if ($I->seePageHasElement(substr($this->newConfirmButton, 1))) {
            $I->retryClick($this->newConfirmButton);
            $I->waitForDocumentReadyState();
            $I->waitForElementNotVisible($this->globalSpinner, 60);
            $I->wait(10);
        }

        if ($I->seePageHasElement("//input[@id='" . substr($this->newConfirmButton, 1) . "']")) {
            $I->executeJS("document.getElementById('" . substr($this->newConfirmButton, 1) . "').click();");
            $I->waitForDocumentReadyState();
            $I->waitForElementNotVisible($this->globalSpinner, 60);
            $I->wait(10);
        }

        if ($I->seePageHasElement($this->paymentConfirmButton)) {
            $I->retryClick($this->paymentConfirmButton);
            $I->waitForDocumentReadyState();
            $I->waitForElementNotVisible($this->globalSpinner, 60);
            $I->wait(10);
        }
    }

    /**
     * @param string $userName
     * @param string $userPassword
     */
    public function approveGraphqlExpressPayPal(string $userName, string $userPassword): void
    {
        $I = $this->user;

        $this->approveExpressPayPal($userName, $userPassword);

        //we should be back to shop frontend as we sent a redirect url to paypal
        $I->seePageHasElement($this->paypalBannerContainer);
    }

    /**
     * @param string $userName
     * @param string $userPassword
     */
    public function approveExpressPayPal(string $userName, string $userPassword): void
    {
        $I = $this->user;

        $this->waitForPayPalPage();
        $this->waitForSpinnerDisappearance();
        $this->removeCookieConsent();

        $this->loginToPayPal($userName, $userPassword);
        $this->waitForSpinnerDisappearance();
        $I->wait(3);

        $this->confirmPayPal();

        //retry
        $this->waitForSpinnerDisappearance();
        $this->confirmPayPal();
    }

    public function waitForPayPalPage(): PayPalLogin
    {
        $I = $this->user;

        $I->waitForDocumentReadyState();
        $I->waitForElementNotVisible($this->spinner, 90);
        $I->wait(10);

        if ($I->seePageHasElement($this->loginSection)) {
            $I->retryClick('.loginRedirect a');
            $I->waitForDocumentReadyState();
            $this->waitForSpinnerDisappearance();
            $I->waitForElementNotVisible($this->loginSection);
         }

        return $this;
    }

    /**
     * Click cancel on payPal side to return to shop.
     */
    public function cancelPayPal(bool $isRetry = false): void
    {
        $I = $this->user;

        if ($I->seePageHasElement($this->cancelLink)) {
            $I->amOnUrl($I->grabAttributeFrom($this->cancelLink, 'href'));
            $I->waitForDocumentReadyState();
        } elseif ($I->seePageHasElement($this->returnToShop)) {
            $I->amOnUrl($I->grabAttributeFrom($this->returnToShop, 'href'));
            $I->waitForDocumentReadyState();
        }

        //we should be redirected back to shop at this point
        if ($I->dontSeeElement($this->breadCrumb) &&
            $I->dontSeeElement(strtolower($this->breadCrumb)) &&
            !$isRetry
        ) {
            $this->cancelPayPal(true);
        }
    }

    private function acceptAllPayPalCookies()
    {
        $I = $this->user;

        // In case we have cookie message, accept all cookies
        if ($I->seePageHasElement($this->acceptAllPaypalCookies)) {
            $I->retryClick($this->acceptAllPaypalCookies);
            $I->waitForElementNotVisible($this->acceptAllPaypalCookies);
        }
    }

    private function waitForSpinnerDisappearance()
    {
        $I = $this->user;
        $I->waitForElementNotVisible($this->preloaderSpinner, 30);
        $I->waitForElementNotVisible($this->globalSpinner, 30);
        $I->waitForElementNotVisible($this->spinner, 30);
    }

    private function removeCookieConsent()
    {
        $I = $this->user;
        if ($I->seePageHasElement($this->gdprContainer)) {
            $I->executeJS("document.getElementById('" . substr($this->gdprContainer, 1) . "').remove();");
        }
        if ($I->seePageHasElement($this->gdprCookieBanner)) {
            $I->executeJS("document.getElementById('" . substr($this->gdprCookieBanner, 1) . "').remove();");
        }
    }
}
