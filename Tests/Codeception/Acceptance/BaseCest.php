<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\Facts\Facts;
use Codeception\Util\Fixtures;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Exception\ModuleSetupException;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\Codeception\Page\Checkout\ThankYou;
use OxidEsales\Codeception\Page\Checkout\PaymentCheckout;
use OxidEsales\Codeception\Page\Checkout\OrderCheckout;
use OxidEsales\Codeception\Page\Checkout\Basket as BasketCheckout;

abstract class BaseCest
{
    public function _before(AcceptanceTester $I): void
    {
        $this->activateModules();

        $I->clearShopCache();
        $I->updateConfigInDatabase('blUseStock', false, 'bool');
        $I->updateConfigInDatabase('bl_perfLoadPrice', true, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');
        $I->updateConfigInDatabase('blOEPayPalFinalizeOrderOnPayPal', false, 'bool');
        $I->updateConfigInDatabase('sOEPayPalTransactionMode', 'Sale', 'str');

        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentMethod'));
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentCountry'));

        $this->ensureShopUserData($I);
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->ensureShopUserData($I);
        $I->updateConfigInDatabase('blShowNetPrice', false, 'bool');

        $I->deleteFromDatabase('oxorder', ['OXORDERNR >=' => '2']);
        $I->deleteFromDatabase('oxuserbaskets', ['OXTITLE >=' => 'savedbasket']);
        $I->resetCookie('sid');
        $I->resetCookie('sid_key');
    }

    protected function getShopUrl(): string
    {
        $facts = new Facts();

        return $facts->getShopUrl();
    }

    /**
     * Activates modules
     */
    protected function activateModules(int $shopId = 1): void
    {
        $testConfig        = new \OxidEsales\TestingLibrary\TestConfig();
        $modulesToActivate = $testConfig->getModulesToActivate();

        if ($modulesToActivate) {
            $serviceCaller = new \OxidEsales\TestingLibrary\ServiceCaller();
            $serviceCaller->setParameter('modulestoactivate', $modulesToActivate);

            try {
                $serviceCaller->callService('ModuleInstaller', $shopId);
            } catch (ModuleSetupException $e) {
                // this may happen if the module is already active,
                // we can ignore this
            }
        }
    }

    protected function ensureShopUserData(AcceptanceTester $I): void
    {
        $toBeUpdated = $_ENV['sBuyerLogin'];
        if (0 < $I->grabNumRecords('oxuser', ['oxusername' => Fixtures::get('userName')])) {
            $toBeUpdated = Fixtures::get('userName');
            $I->deleteFromDatabase('oxuser', ['oxusername' => $_ENV['sBuyerLogin']]);
        }
        if (0 < $I->grabNumRecords('oxnewssubscribed', ['oxemail' => $_ENV['sBuyerLogin']])) {
            $I->deleteFromDatabase('oxnewssubscribed', ['oxemail' => $_ENV['sBuyerLogin']]);
        }

        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername'  => Fixtures::get('userName'),
                'oxcity'      => Fixtures::get('details')['oxcity'],
                'oxstreet'    => Fixtures::get('details')['oxstreet'],
                'oxstreetnr'  => Fixtures::get('details')['oxstreetnr'],
                'oxzip'       => Fixtures::get('details')['oxzip'],
                'oxfname'     => Fixtures::get('details')['firstname'],
                'oxlname'     => Fixtures::get('details')['lastname'],
                'oxcountryid' => 'a7c40f631fc920687.20179984' //DE
            ],
            [
                'oxusername' => $toBeUpdated
            ]
        );
        $I->updateInDatabase(
            'oxuser',
            [
                'oxpassword' => '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2',
                'oxpasssalt' => 'b186f117054b700a89de929ce90c6aef'
            ],
            [
                'oxusername' => Fixtures::get('userName')
            ]
        );

        $I->seeInDatabase('oxuser', ['oxusername' => Fixtures::get('userName')]);
        $I->dontSeeInDatabase('oxuser', ['oxusername' => $_ENV['sBuyerLogin']]);
    }

    protected function setUserDataSameAsPayPal(AcceptanceTester $I, bool $removePassword = false): void
    {
        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername' => $_ENV['sBuyerLogin'],
                'oxfname'    => $_ENV['sBuyerFirstName'],
                'oxlname'    => $_ENV['sBuyerLastName'],
                'oxstreet'   => 'ESpachstr.',
                'oxstreetnr' => '1',
                'oxzip'      => '79111',
                'oxcity'     => 'Freiburg'
            ],
            [
                'oxusername' => Fixtures::get('userName')
            ]
        );

        if ($removePassword) {
            $this->removePassword($I);
        }
    }

    protected function removePassword(AcceptanceTester $I): void
    {
        $I->updateInDatabase(
            'oxuser',
            [
                'oxpassword' => '',
                'oxpasssalt' => ''
            ],
            [
                'oxusername' => $_ENV['sBuyerLogin']
            ]
        );
    }

    protected function setUserNameSameAsPayPal(AcceptanceTester $I): void
    {
        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername' => $_ENV['sBuyerLogin'],
            ],
            [
                'oxusername' => Fixtures::get('userName')
            ]
        );
    }

    protected function proceedToPaymentStep(AcceptanceTester $I, string $userName = null): void
    {
        $userName = $userName ?: Fixtures::get('userName');

        $home = $I->openShop()
            ->loginUser($userName, Fixtures::get('userPassword'));
        $I->waitForText(Translator::translate('HOME'));

        //add product to basket and start checkout
        $this->fillBasket($I);
        $this->fromBasketToPayment($I);
    }

    protected function fromBasketToPayment(AcceptanceTester $I): void
    {
        $I->amOnPage('/en/cart');
        $basketPage = new BasketCheckout($I);
        $basketPage->goToNextStep()
            ->goToNextStep();

        $I->see(Translator::translate('PAYMENT_METHOD'));
    }

    protected function proceedToBasketStep(AcceptanceTester $I, string $userName = null, bool $logMeIn = true): void
    {
        $userName = $userName ?: Fixtures::get('userName');

        $home = $I->openShop();
        if ($logMeIn) {
            $home->loginUser($userName, Fixtures::get('userPassword'));
        }
        $I->waitForText(Translator::translate('HOME'));

        //add product to basket and start checkout
        $this->fillBasket($I);
        $I->seeElement('//input[@name="paypalExpressCheckoutButtonECS"]');
    }

    protected function fillBasket(AcceptanceTester $I): void
    {
        //add product to basket and start checkout
        $product = Fixtures::get('product');
        $basket = new Basket($I);
        $basket->addProductToBasketAndOpenBasket($product['id'], $product['amount'], 'basket');
        $I->see(Translator::translate('CONTINUE_TO_NEXT_STEP'));
    }

    protected function finalizeOrder(AcceptanceTester $I): string
    {
        $paymentPage = new PaymentCheckout($I);
        $paymentPage->goToNextStep()
            ->submitOrder();

        $thankYouPage = new ThankYou($I);

        return  $thankYouPage->grabOrderNumber();
    }

    protected function finalizeOrderInOrderStep(AcceptanceTester $I): string
    {
        $orderPage = new OrderCheckout($I);
        $orderPage->submitOrder();

        $thankYouPage = new ThankYou($I);

        return  $thankYouPage->grabOrderNumber();
    }

    protected function approvePayPalTransaction(AcceptanceTester $I, string $addParams = ''): string
    {
        //workaround to approve the transaction on PayPal side
        $loginPage = new PayPalLogin($I);
        $loginPage->openPayPalApprovalPage($I, $addParams);
        $token = $loginPage->getToken();
        $loginPage->approveStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        return $token;
    }

    protected function approveAnonymousPayPalTransaction(AcceptanceTester $I, string $addParams = ''): string
    {
        //workaround to approve the transaction on PayPal side
        $loginPage = new PayPalLogin($I);
        $loginPage->openPayPalApprovalPageAsAnonymousUser($I, $addParams);
        $token = $loginPage->getToken();
        $loginPage->approveStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        return $token;
    }

    protected function openOrderPayPal(AcceptanceTester $I, string $orderNumber): void
    {
        $adminPanel = $I->loginAdmin();
        $orders = $adminPanel->openOrders();
        $I->waitForDocumentReadyState();
        $orders->find($orders->orderNumberInput, $orderNumber);

        $I->selectListFrame();
        $I->click("//a[contains(@href, '#oepaypalorder_paypal')]", 'edit');
        $I->selectEditFrame();
    }
}
