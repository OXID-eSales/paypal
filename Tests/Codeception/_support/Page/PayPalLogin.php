<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

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

    public $confirmButton = '#confirmButtonTop';
    public $oldConfirmButton = '#continue_abovefold';

    public $oneTouchNotNowLink = '#notNowLink';

    public $spinner = '#spinner';

    /**
     * @param string $userName
     * @param string $userPassword
     *
     * @return $this
     */
    public function loginAndCheckout(string $userName, string $userPassword)
    {
        $I = $this->user;
        $usingNewLogin = true;

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

        if ($usingNewLogin) {
            $I->waitForElementClickable($this->confirmButton, 60);
            $I->waitForElementNotVisible($this->spinner, 90);
            $I->scrollTo($this->confirmButton);
            $I->click($this->confirmButton);
        } else {
            $I->waitForElementClickable($this->oldConfirmButton, 60);
            $I->scrollTo($this->oldConfirmButton);
            $I->click($this->oldConfirmButton);
        }
        $I->waitForDocumentReadyState();

        return new OrderCheckout($I);
    }
}
