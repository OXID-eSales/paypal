<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\Facts\Facts;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use Codeception\Util\Fixtures;
use Codeception\Scenario;
use Codeception\Util\HttpCode;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use TheCodingMachine\GraphQLite\Types\ID;
use OxidEsales\PayPalModule\GraphQL\Exception\WrongPaymentMethod;
use OxidEsales\PayPalModule\GraphQL\Exception\BasketValidation;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\GraphQL\Storefront\Basket\Exception\PlaceOrder;

class ExpressCheckoutWithGraphqlCest
{
    private const EXPIRED_TOKEN = 'EC-20P17490LV1421614';

    use GraphqlCheckoutTrait;
    use GraphqlExpressCheckoutTrait;

    public function _beforeSuite($settings = []): void // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $rootPath = (new Facts())->getShopRootPath();
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
                'oxusername' => $I->getDemoUserName(),
                'oxcity'     => 'Freiburg'
            ],
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );
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
     * @group paypal_graphql_express
     */
    public function expressCheckoutWithGraphql(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, is logged in via graphql and basket is shipping cost free
        //invoice adress is used as delivery address
        //username in shop differs from PayPal email
        $I->wantToTest('logged in user placing an order successfully with PayPal Express via graphql');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword());

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //query basket payments
        $basketPayments = $this->getBasketPaymentIds($I, $basketId);
        $I->assertArrayHasKey(Fixtures::get('payment_id'), $basketPayments);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId,
            HttpCode::OK,
            EshopRegistry::getConfig()->getShopUrl()
        );
        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $orderDetails = $this->getLatestOrderFromOrderHistory($I);
        $I->assertSame($orderId, $orderDetails['id']);
        $I->assertNotEmpty($orderDetails['invoiceAddress']);
        $I->assertEmpty($orderDetails['deliveryAddress']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function expressCheckoutWithGraphqlDeliveryAdress(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, is logged in via graphql and basket is shipping cost free
        //user login in shop is the same as PP email
        $I->wantToTest('logged in user placing an order successfully with PayPal Express via graphql with delivery address');

        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ],
            [
                'oxusername' => $I->getDemoUserName()
            ]
        );
        $I->loginToGraphQLApi(Fixtures::get('sBuyerLogin'), $I->getExistingUserPassword());

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId,
            HttpCode::OK,
            EshopRegistry::getConfig()->getShopUrl()
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //change invoice city, this has the same effect as user changing delivery address in PP
        $I->updateInDatabase(
            'oxuser',
            [
                'oxcity' => 'Hamburg'
            ],
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $orderDetails = $this->getLatestOrderFromOrderHistory($I);
        $I->assertSame($orderId, $orderDetails['id']);
        $I->assertNotEmpty($orderDetails['invoiceAddress']);
        $I->assertNotEmpty($orderDetails['deliveryAddress']);
        $I->assertSame('Hamburg', $orderDetails['invoiceAddress']['city']);
        $I->assertSame('Freiburg', $orderDetails['deliveryAddress']['city']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function expressCheckoutWithGraphqlDeliveryAdressDifferentPPUserName(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, is logged in via graphql and basket is shipping cost free
        //user login in shop is different from PayPal login
        //use picks different delivery address in PayPal (which we mimick by changing user invoice city after PP approval)
        $I->wantToTest('logged user place order succeeds with PayPal Express via graphql for different PP email');

        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword());

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId,
            HttpCode::OK,
            EshopRegistry::getConfig()->getShopUrl()
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //change invoice city, this has the same effect as user changing delivery address in PP
        $I->updateInDatabase(
            'oxuser',
            [
                'oxcity' => 'Hamburg'
            ],
            [
                'oxusername' => $I->getDemoUserName()
            ]
        );

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $orderDetails = $this->getLatestOrderFromOrderHistory($I);
        $I->assertSame($orderId, $orderDetails['id']);
        $I->assertNotEmpty($orderDetails['invoiceAddress']);
        $I->assertNotEmpty($orderDetails['deliveryAddress']);
        $I->assertSame('Hamburg', $orderDetails['invoiceAddress']['city']);
        $I->assertSame('Freiburg', $orderDetails['deliveryAddress']['city']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function expressCheckoutWithGraphqlShippingCostForLoggedInUser(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, is logged in via graphql and basket is not shipping cost free
        //user login in shop is different from PayPal login
        //invoice address is used for delivery
        //delivery costs come from shop as user is logged in to shop
        $I->wantToTest('logged user place order succeeds with PayPal Express via graphql for delivery costs');

        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword());

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 1);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId,
            HttpCode::OK,
            EshopRegistry::getConfig()->getShopUrl()
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $orderDetails = $this->getLatestOrderFromOrderHistory($I);
        $I->assertSame($orderId, $orderDetails['id']);
        $I->assertNotEmpty($orderDetails['invoiceAddress']);
        $I->assertEmpty($orderDetails['deliveryAddress']);
        $I->assertEquals(Fixtures::get('product')['bruttoprice_single'] + 3.9, $orderDetails['cost']['total']);
    }
}