<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\Tests\Codeception\Page;

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

    public $gdprCookieBanner = "#gdprCookieBanner";
    public $acceptAllPaypalCookies = "#acceptAllButton";

    public $loginSection = "#loginSection";

    public $cancelLink = "#cancelLink";
    public $returnToShop = "#cancel_return";

    public $breadCrumb = "#breadCrumb";

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

        // new login page
        if ($I->seePageHasElement($this->userLoginEmail)) {
            $I->waitForElementVisible($this->userLoginEmail, 30);
            $I->fillField($this->userLoginEmail, $userName);
            $I->click($this->nextButton);
            $I->waitForElementVisible($this->userPassword, 10);
            $I->fillField($this->userPassword, $userPassword);
            $I->click($this->loginButton);
        }

        // old login page
        if ($I->seePageHasElement($this->oldUserLoginEmail)) {
            $usingNewLogin = false;

            $I->waitForElementVisible($this->oldUserLoginEmail, 30);
            $I->fillField($this->oldUserLoginEmail, $userName);
            $I->waitForElementVisible($this->oldUserPassword, 5);
            $I->fillField($this->oldUserPassword, $userPassword);
            $I->click($this->oldLoginButton);
        }

        if ($I->seePageHasElement($this->oneTouchNotNowLink)) {
            $I->click($this->oneTouchNotNowLink);
        }

        $confirmButton = $usingNewLogin ? $this->newConfirmButton : $this->oldConfirmButton;
        $I->waitForElementClickable($confirmButton, 60);
        $I->waitForElementNotVisible($this->spinner, 90);

        // In case we have cookie message, accept all cookies
        $this->acceptAllPayPalCookies();

        $I->waitForElementNotVisible($this->spinner, 90);
        $this->acceptAllPayPalCookies();
        $I->click($confirmButton);
        $I->waitForDocumentReadyState();

        return new OrderCheckout($I);
    }

    public function waitForPayPalPage(): PayPalLogin
    {
        $I = $this->user;

        $I->waitForDocumentReadyState();
        $I->waitForElementNotVisible($this->spinner, 90);
        $I->wait(8);

        if ($I->seePageHasElement($this->loginSection)) {
            $I->click('.loginRedirect a');
            $I->waitForDocumentReadyState();
            $I->waitForElementNotVisible($this->spinner, 90);
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
        if ($I->seePageHasElement($this->gdprCookieBanner)) {
            $I->click($this->acceptAllPaypalCookies);
            // In case that the content blocking is enabled,
            // because the cookie came from a tracker
            // we wont be able to accept it, then remove the message
            if ($I->seePageHasElement($this->gdprCookieBanner)) {
                $I->executeJS("document.getElementById('".substr($this->gdprCookieBanner, 1)."').remove();");
            }
            $I->waitForElementNotVisible($this->gdprCookieBanner);
        }
    }    
}
