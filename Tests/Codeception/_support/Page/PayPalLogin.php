<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Page;

use Codeception\Module\WebDriver;
use OxidEsales\Codeception\Page\Account\UserAccount;
use OxidEsales\Codeception\Page\Checkout\OrderCheckout;
use OxidEsales\Codeception\Page\Page;

/**
 * Class PayPalLogin
 * @package OxidEsales\PayPalModule\Tests\Codeception\Page
 */
class PayPalLogin extends Page
{
    public $userLoginEmail = '#email';

    public $userPassword = '#password';

    public $nextButton = '#btnNext';

    public $loginButton = '#btnLogin';

    public $confirmButton = '#confirmButtonTop';

    public $oneTouchNotNowButton = '#notNowLink';

    public $spinner = '#spinner';

    /**
     * @param string $userName
     * @param string $userPassword
     *
     * @return UserAccount
     */
    public function loginPayPalUser(string $userName, string $userPassword)
    {
        $I = $this->user;

        if ($I->seePageHasElement($this->userLoginEmail)) {
            $I->waitForElementVisible($this->userLoginEmail, 30);
            $I->fillField($this->userLoginEmail, $userName);
            $I->click($this->nextButton);
            $I->waitForElementVisible($this->userPassword, 10);
            $I->fillField($this->userPassword, $userPassword);
            $I->click($this->loginButton);
        }

        if ($I->seePageHasElement($this->oneTouchNotNowButton)) {
            $I->click($this->oneTouchNotNowButton);
        }

        $I->waitForElementClickable($this->confirmButton, 60);
        $I->waitForElementNotVisible($this->spinner, 60);
        $I->click($this->confirmButton);

        return new OrderCheckout($I);
    }
}
