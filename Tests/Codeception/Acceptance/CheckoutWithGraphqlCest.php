<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\Facts\Facts;
use Codeception\Util\Fixtures;
use Codeception\Scenario;
use Codeception\Util\HttpCode;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use TheCodingMachine\GraphQLite\Types\ID;

class CheckoutWithGraphqlCest
{
    use GraphqlCheckoutTrait;

    public function _beforeSuite($settings = []): void // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $rootPath      = (new Facts())->getShopRootPath();
        $possiblePaths = [
            '/bin/oe-console',
            '/vendor/bin/oe-console',
        ];

        foreach ($possiblePaths as $path) {
            if (is_file($rootPath . $path)) {
                exec($rootPath . $path . ' oe:module:activate oe_graphql_base');
                exec($rootPath . $path . ' oe:module:activate oe_graphql_storefront');

                return;
            }
        }

        throw new Exception('Could not find script "/bin/oe-console" to activate module');
    }

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        $I->updateConfigInDatabase('blPerfNoBasketSaving', false, 'bool');
        $I->updateConfigInDatabase('blCalculateDelCostIfNotLoggedIn', false, 'bool');
        $I->updateConfigInDatabase('iVoucherTimeout', 10800, 'int'); // matches default value

        $I->activateFlowTheme();
        $I->clearShopCache();
        $I->setPayPalSettingsData();
        $I->updateConfigInDatabase('blUseStock', false, 'bool');

        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentMethod'));
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentCountry'));
        $I->updateInDatabase('oxuser', Fixtures::get('adminData'), ['OXUSERNAME' => 'admin']);
        $I->updateInDatabase(
            'oxuser',
            [
                'oxpassword' => '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2',
                'oxpasssalt' => 'b186f117054b700a89de929ce90c6aef'
            ],
            [
                'oxusername' => $I->getDemoUserName()
            ]
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphql(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order successfully with PayPal via graphql');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);
    }
}