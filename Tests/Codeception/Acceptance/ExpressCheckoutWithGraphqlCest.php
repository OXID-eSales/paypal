<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\GraphQL\Storefront\Basket\Exception\BasketAccessForbidden;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\Facts\Facts;
use Codeception\Util\Fixtures;
use Codeception\Scenario;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use OxidEsales\PayPalModule\GraphQL\Exception\BasketValidation;

class ExpressCheckoutWithGraphqlCest
{
    private const EXPIRED_TOKEN = 'EC-20P17490LV1421614';

    private const VOUCHER_NUMBER = 'ppgvoucher1';

    use GraphqlCheckoutTrait;
    use GraphqlExpressCheckoutTrait;

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        if (!($I->checkGraphBaseActive() && $I->checkGraphStorefrontActive())) {
            $I->markTestSkipped('GraphQL modules are not active');
        }

        $I->updateConfigInDatabase('blPerfNoBasketSaving', false);
        $I->updateConfigInDatabase('blCalculateDelCostIfNotLoggedIn', false);
        $I->updateConfigInDatabase('iVoucherTimeout', 10800, 'int'); // matches default value

        $I->activateFlowTheme();
        $I->clearShopCache();
        $I->setPayPalSettingsData();
        $I->updateConfigInDatabase('blUseStock', false);

        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentMethod'));
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentCountry'));
        $I->updateInDatabase('oxuser', Fixtures::get('adminData'), ['OXUSERNAME' => 'admin']);
        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername' => $I->getDemoUserName(),
                'oxcity'     => 'Freiburg',
                'oxstreet'   => 'Hauptstr.',
                'oxstreetnr' => '13',
                'oxzip'      => '79098',
                'oxfname'    => 'Marc',
                'oxlname'    => 'Muster'
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

        $this->deactivateDiscount($I);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function expressCheckoutWithGraphql(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, is logged in via graphql and basket is shipping cost free
        //invoice address is used as delivery address
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
            $basketId
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
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function expressCheckoutWithGraphqlSetShippingAndPaymentAndDeliveryAdress(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, is logged in via graphql and basket is shipping cost free
        //invoice address is used as delivery address
        //username in shop differs from PayPal email
        //basket comes with delivery and payment method (non paypal) already set, which gets overwritten with paypal
        //basket also comes with delivery address
        $I->wantToTest('logged in user placing an order successfully with PayPal Express via graphql');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword());

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);
        $this->setBasketDeliveryAddress($I, $basketId, $this->createDeliveryAddress($I));
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id_other'));

        //query basket payments
        $basketPayments = $this->getBasketPaymentIds($I, $basketId);
        $I->assertArrayHasKey(Fixtures::get('payment_id'), $basketPayments);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
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
        $I->assertNotEmpty($orderDetails['deliveryAddress']);
        $I->assertNotEquals($orderDetails['invoiceAddress']['lastName'], $orderDetails['deliveryAddress']['lastName']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function expressCheckoutWithGraphqlDeliveryAddress(AcceptanceTester $I)
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
            $basketId
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
    public function expressCheckoutWithGraphqlDeliveryAddressDifferentPPUserName(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, is logged in via graphql and basket is shipping cost free
        //user login in shop is different from PayPal login
        //user picks different delivery address in PayPal (which we mimic by changing user invoice city after PP approval)
        $I->wantToTest('logged user place order succeeds with PayPal Express via graphql for different PP email');

        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword());

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
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
            $basketId
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

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousUserSameExistsInShop(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, but is NOT logged in via graphql (has an anonymous token)
        //basket is shipping cost free
        //user login and invoice data in shop is exact same as PayPal details
        //invoice address is used for delivery
        //User will be matched in BeforePlaceOrder to his actual account. We need to deal with the case
        //that the token still holds the temporary user id but we need to use the existing one
        $I->wantToTest('anonymous existing user place order succeeds with PayPal Express via graphql');

        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin'),
                'oxfname'    => Fixtures::get('sBuyerFirstName'),
                'oxlname'    => Fixtures::get('sBuyerLastName'),
                'oxstreet'   => 'ESpachstr.',
                'oxstreetnr' => '1',
                'oxzip'      => '79111',
                'oxcity'     => 'Freiburg'
            ],
            [
                'oxusername' => $I->getDemoUserName()
            ]
        );

        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousUserSameExistsInShopDifferentInvoice(AcceptanceTester $I)
    {
        //user exists in shop, has password in shop, but is NOT logged in via graphql
        //basket is shipping cost free
        //user login (email) is the same in shop and PayPal
        //invoice data in shop differs from PayPal details
        //invoice address is used for delivery

        $I->wantToTest('anonymous existing user place order data mismatch with PayPal Express via graphql');

        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin'),
                'oxfname'    => Fixtures::get('sBuyerFirstName'),
                'oxlname'    => Fixtures::get('sBuyerLastName')
            ],
            [
                'oxusername' => $I->getDemoUserName()
            ]
        );

        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result = $this->placeOrder($I, $basketId);

        $I->assertEquals('OEPAYPAL_ERROR_USER_ADDRESS', $result['errors'][0]['debugMessage']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousNewUser(AcceptanceTester $I)
    {
        //user does not exist in shop and needs to be created during checkout
        //basket is shipping cost free
        $I->wantToTest('anonymous new user place order succeeds with PayPal Express via graphql');

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $I->seeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousNewUserNewToken(AcceptanceTester $I)
    {
        //user does not exist in shop and needs to be created during checkout
        //basket is shipping cost free
        $I->wantToTest('anonymous new user place order succeeds with PayPal Express via graphql');

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $basketWithItems = $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 5);
        $itemId = $basketWithItems[0]['id'];
        $this->removeItemFromBasket($I, $basketId, $itemId, 1);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //try to place the order with a new anonymous token
        $I->anonymousLoginToGraphQLApi();
        $result = $this->placeOrder($I, $basketId);

        $expectedException = BasketAccessForbidden::byAuthenticatedUser();
        $I->assertEquals(
            $expectedException->getMessage(),
            $result['errors'][0]['message']
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     * @group paypal_graphql_callback
     */
    public function expressCheckoutWithGraphqlAnonymousShippingCostCallback(AcceptanceTester $I)
    {
        //user does not exist in shop and needs to be created during checkout
        //basket is not shipping cost free, so callback is needed
        $I->wantToTest('anonymous new user place order succeeds with callback PayPal Express via graphql');

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 1);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $I->seeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     * @group paypal_graphql_callback
     */
    public function expressCheckoutWithGraphqlAnonymousUSBuyer(AcceptanceTester $I)
    {
        //user does not exist in shop and needs to be created during checkout
        //basket is not shipping cost free, so callback is needed
        $I->wantToTest('anonymous US buyer has no shipping method with callback PayPal Express via graphql');

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerUSLogin')
            ]
        );

        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 1);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->loginToPayPal(Fixtures::get('sBuyerUSLogin'), Fixtures::get('sBuyerPassword'));

        $I->seeInSource("doesn't ship to this location. Please use a different address");
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousExistingUser(AcceptanceTester $I)
    {
        //Case existing user with anonymous token creates basket, approves payment
        //then logs in via graphql api -> he will not be able to access the anonymous basket
        //unless we start matching the basket (anonymous) userid to oxuser.OEPAYPAL_ANON_USERID
        //TODO: decide how to handle this

        $I->wantToTest('anonymous existing user token switch with PayPal Express via graphql');

        $I->updateInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin'),
                'oxfname'    => Fixtures::get('sBuyerFirstName'),
                'oxlname'    => Fixtures::get('sBuyerLastName'),
                'oxstreet'   => 'ESpachstr.',
                'oxstreetnr' => '1',
                'oxzip'      => '79111',
                'oxcity'     => 'Freiburg'
            ],
            [
                'oxusername' => $I->getDemoUserName()
            ]
        );

        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //User gets a non anonymous token to place the order
        $I->loginToGraphQLApi(Fixtures::get('sBuyerLogin'), $I->getExistingUserPassword());
        $result = $this->placeOrder($I, $basketId);

        $expectedException = BasketAccessForbidden::byAuthenticatedUser();
        $I->assertEquals(
            $expectedException->getMessage(),
            $result['errors'][0]['message']
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousNewUserVoucher(AcceptanceTester $I)
    {
        //user does not exist in shop and needs to be created during checkout
        //basket is shipping cost free
        $I->wantToTest('anonymous new user place order with voucher succeeds with PayPal Express via graphql');

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $this->prepareVoucherSeries($I);
        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //add voucher, remove voucher, add voucher
        $this->addVoucherToBasket($I, $basketId, self::VOUCHER_NUMBER);
        $this->removeVoucherFromBasket($I, $basketId, self::VOUCHER_NUMBER);
        $this->addVoucherToBasket($I, $basketId, self::VOUCHER_NUMBER);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $I->seeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $I->seeInDatabase(
            'oxorder',
            [
                'oxid' => $orderId,
                'oxtotalordersum' => (4 * Fixtures::get('product')['bruttoprice_single']) - 10
            ]
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousNewUserDiscountOk(AcceptanceTester $I)
    {
        //user does not exist in shop and needs to be created during checkout
        //basket is shipping cost free
        $I->wantToTest('anonymous new user place order with discount with PayPal Express via graphql');

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $this->prepareDiscount($I);
        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));
        
        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $I->seeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $I->seeInDatabase(
            'oxorder',
            [
                'oxid' => $orderId,
                'oxtotalordersum' => 80
            ]
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousNewUserDiscountTimedOut(AcceptanceTester $I)
    {
        //user does not exist in shop and needs to be created during checkout
        //basket is shipping cost free
        $I->wantToTest('anonymous new user place order with (deactivated) discount with PayPal Express via graphql');

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin')
            ]
        );

        $this->prepareDiscount($I);
        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //discount timed out
        $this->deactivateDiscount($I);

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $exception = BasketValidation::basketChange($basketId);
        $I->assertEquals($exception->getMessage(), $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     */
    public function expressCheckoutWithGraphqlAnonymousNoInvoiceUser(AcceptanceTester $I): void
    {
        //case user was registered via graphql but has no invoice address set
        //user is anonymous during PP Express checkout
        //cart is shipping cost free
        $I->wantToTest('anonymous existing no invoice user places order with PayPal Express via graphql');

        //register customer via graphql, he's in 'oxidnotyetordered' group. Username must be same as PayPal email.
        $username = Fixtures::get('sBuyerLogin');
        $password = $I->getExistingUserPassword();
        $userId   = $this->registerCustomer($I, $username, $password)['id'];

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin'),
                'oxfname'    => Fixtures::get('sBuyerFirstName')
            ]
        );

        //get anonymous token
        $I->anonymousLoginToGraphQLApi();

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //NOTE regarding shipping id: we have no logged in user, so during SetExpressCheckoutRequestBuilder::addBasketParams()
        //the call to $basket->getShippingId() returns 'oxidstandard' which is working with PayPal even if
        //callback cannot be reached.

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //Our user's invoice address is missing in shop and will be set from PayPal details
        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $I->seeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin'),
                'oxfname'    => Fixtures::get('sBuyerFirstName'),
                'oxlname'    => Fixtures::get('sBuyerLastName'),
                'oxstreet'   => 'ESpachstr.',
                'oxstreetnr' => '1',
                'oxzip'      => '79111',
                'oxcity'     => 'Freiburg',
            ]
        );

        //clean up
        $I->deleteFromDatabase(
            'oxuser',
            [
                'oxid' => $userId
            ]
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     * @group paypal_graphql_anonymous
     * @group paypal_graphql_callback
     */
    public function expressCheckoutWithGraphqlLoggedNoInvoiceUser(AcceptanceTester $I): void
    {
        //case user was registered via graphql but has no invoice address set
        //user is regularly logged in to graphql API during PP Express checkout
        //cart is shipping cost free
        $I->wantToTest('logged existing no invoice user places order with PayPal Express via graphql');

        //register customer via graphql, he's in 'oxidnotyetordered' group. Username must be same as PayPal email.
        $username     = Fixtures::get('sBuyerLogin');
        $password     = 'useruser';
        $userId = $this->registerCustomer($I, $username, $password)['id'];

        $I->dontSeeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin'),
                'oxfname'    => Fixtures::get('sBuyerFirstName')
            ]
        );

        //get token
        $I->loginToGraphQLApi($username, $password);

        //prepare basket
        $basketId = $this->createBasket($I, 'pp_express_cart');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //NOTE regarding shipping id: we have no logged in user, so during SetExpressCheckoutRequestBuilder::addBasketParams()
        //the call to $basket->getShippingId() returns '#1' which is only working with PayPal if the callback is accessible.
        //Even in case the basket would be shipping cost free.

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        $I->amOnUrl($approvalDetails['data']['paypalExpressApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlExpressPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //Our user's invoice address is missing in shop and will be set from PayPal details
        //place the order
        $result = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);

        $I->seeInDatabase(
            'oxuser',
            [
                'oxusername' => Fixtures::get('sBuyerLogin'),
                'oxfname'    => Fixtures::get('sBuyerFirstName')
            ]
        );

        //clean up
        $I->deleteFromDatabase(
            'oxuser',
            [
                'oxid' => $userId
            ]
        );
    }

    private function prepareVoucherSeries(AcceptanceTester $I): void
    {
        $facts = new Facts();

        if ($facts->isEnterprise()) {
            $I->haveInDatabase('oxvoucherseries', Fixtures::get('oxvoucherseries_ee'));
            $I->haveInDatabase('oxvoucherseries2shop', Fixtures::get('oxvoucherseries2shop'));
        } else {
            $I->haveInDatabase('oxvoucherseries', Fixtures::get('oxvoucherseries'));
        }

        $I->haveInDatabase('oxvouchers', Fixtures::get('oxvouchers'));
    }

    private function prepareDiscount(AcceptanceTester $I): void
    {
        $facts = new Facts();

        if ($facts->isEnterprise()) {
            $I->haveInDatabase('oxdiscount', Fixtures::get('oxdiscount_ee'));
            $I->haveInDatabase('oxdiscount2shop', Fixtures::get('oxdiscount2shop'));
        } else {
            $I->haveInDatabase('oxdiscount', Fixtures::get('oxdiscount'));
        }

        $I->haveInDatabase('oxobject2discount', Fixtures::get('oxobject2discount'));
    }

    private function deactivateDiscount(AcceptanceTester $I)
    {
        $I->updateInDatabase(
            'oxdiscount',
            [
                'oxactive'     => 0,
                'oxactivefrom' => '2020-12-01 00:00:00',
                'oxactiveto'   => '2020-12-31 00:00:00'
            ],
            ['oxid' => 'ppgdiscount']
        );
    }
}