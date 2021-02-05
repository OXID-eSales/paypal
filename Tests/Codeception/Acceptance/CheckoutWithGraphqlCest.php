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
use OxidEsales\PayPalModule\GraphQL\Exception\WrongPaymentMethod;
use OxidEsales\PayPalModule\GraphQL\Exception\BasketValidation;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\GraphQL\Storefront\Basket\Exception\PlaceOrder;

class CheckoutWithGraphqlCest
{
    private const EXPIRED_TOKEN = 'EC-20P17490LV1421614';

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
     * @group wip
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

        //query basket payments
        $basketPayments = $this->getBasketPaymentIds($I, $basketId);
        $I->assertArrayHasKey(Fixtures::get('payment_id'), $basketPayments);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlUseDeliveryAddress(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order successfully with PayPal via graphql using a delivery address');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_two');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryAddress($I, $basketId, $this->createDeliveryAddress($I));
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //place the order
        $result  = $this->placeOrder($I, $basketId);

        $orderId = $result['data']['placeOrder']['id'];
        $I->assertNotEmpty($orderId);

        $orderDetails = $this->getLatestOrderFromOrderHistory($I);
        $I->assertSame($orderId, $orderDetails['id']);
        $I->assertNotEmpty($orderDetails['invoiceAddress']);
        $I->assertNotEmpty($orderDetails['deliveryAddress']);
        $I->assertNotEquals($orderDetails['invoiceAddress']['lastName'], $orderDetails['deliveryAddress']['lastName']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlChangeBasketContentsAfterApproval(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order with PayPal via graphql fails if basket contents was changed after PP approval');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //change basket contents
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 1);

        //place the order
        $result = $this->placeOrder($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);

        $expectedException = BasketValidation::basketChange($basketId);
        $I->assertStringContainsString($expectedException->getMessage(), $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlChangeDeliveryAddressToNonPayPalCountryAfterApproval(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order with PayPal via graphql fails if delivery address was changed after PP approval to unsupported country');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //We change delivery address to country (Belgium) which is not assigned to oxidstandard delivery set.
        $this->setBasketDeliveryAddress($I, $basketId, $this->createDeliveryAddress($I, 'a7c40f632e04633c9.47194042'));

        //place the order
        $result = $this->placeOrder($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);
        $expectedException = BasketValidation::basketAddressChange($basketId);
        $I->assertStringContainsString($expectedException->getMessage(), $result['errors'][0]['message']);

        //PayPal check runs before shop check. Here's what shop would find:
        //$I->assertStringContainsString("Delivery set 'oxidstandard' is unavailable!", $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlChangeDeliveryAddressAfterApproval(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order with PayPal via graphql fails if delivery address was changed after PP approval');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //change delivery address to one where country is assigned to oxidpaypal payment method.
        $this->setBasketDeliveryAddress($I, $basketId, $this->createDeliveryAddress($I));

        //place the order
        $result = $this->placeOrder($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);

        $expectedException = BasketValidation::basketAddressChange($basketId);
        $I->assertStringContainsString($expectedException->getMessage(), $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlFailsForNotFinishedPayPalApproval(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order with PayPal via graphql for not finished PP approval');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer log in to PayPal but not yet approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->loginToPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //placing the order fails
        $result  = $this->placeOrder($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);

        $expectedMessage = sprintf("PayPal communication for basket %s is not confirmed", $basketId);
        $I->assertEquals($expectedMessage, $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlNotConfirmed(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql not confirmed');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url but do not have customer approve the transaction
        $this->paypalApprovalProcess($I, $basketId);

        //placing the order fails
        $result  = $this->placeOrder($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);

        $expectedMessage = sprintf("PayPal communication for basket %s is not confirmed", $basketId);
        $I->assertEquals($expectedMessage, $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlNotStarted(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql not started');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //placing the order fails
        $result  = $this->placeOrder($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);

        $expectedMessage = sprintf("PayPal communication for basket %s is not started", $basketId);
        $I->assertEquals($expectedMessage, $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlEmptyBasket(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql not started');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);

        $expectedMessage = (new WrongPaymentMethod())->getMessage();
        $I->assertEquals($expectedMessage, $approvalDetails['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlEmptyBasketDeliverySet(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql with empty basket');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId, HttpCode::BAD_REQUEST);
        $I->assertStringContainsString(
            //TODO: use Codeception Translator when it is possible to switch the language:
            // to German 'OEPAYPAL_RESPONSE_FROM_PAYPAL'
            'Fehlermeldung von PayPal',
            $approvalDetails['errors'][0]['debugMessage']
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlOtherPaymentMethod(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql and payment method changed after token');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //change payment method
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id_other'));

        //place the order
        //TODO: should the token be reset when the payment method is changed to non-paypal?
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlExpiredToken(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql and expired token');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //we just set the token manually
        $I->updateInDatabase('oxuserbaskets',
            ['OEPAYPAL_PAYMENT_TOKEN' => self::EXPIRED_TOKEN],
            ['OXID' => $basketId]
        );

        //place the order
        $result  = $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        $I->assertStringContainsString(
        //TODO: use Codeception Translator when it is possible to switch the language:
        // to German 'OEPAYPAL_RESPONSE_FROM_PAYPAL'
            'Fehlermeldung von PayPal',
            $result['errors'][0]['debugMessage']
        );
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlNoPaymentMethodSet(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql and payment method not set');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId, HttpCode::INTERNAL_SERVER_ERROR);

        $expectedMessage = (new WrongPaymentMethod())->getMessage();
        $I->assertEquals($expectedMessage, $approvalDetails['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlGetTokenStatusExpired(AcceptanceTester $I)
    {
        $I->wantToTest('get token status for PayPal via graphql for expired token');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        $result = $this->paypalTokenStatus($I, self::EXPIRED_TOKEN, HttpCode::BAD_REQUEST);

        $I->assertStringContainsString(
        //TODO: use Codeception Translator when it is possible to switch the language:
        // to German 'OEPAYPAL_RESPONSE_FROM_PAYPAL'
            'Fehlermeldung von PayPal',
            $result['errors'][0]['debugMessage']
        );
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlGetTokenStatusForValidToken(AcceptanceTester $I)
    {
        $I->wantToTest('get token status for PayPal via graphql for valid token');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);
        $this->setBasketDeliveryMethod($I, $basketId, Fixtures::get('shipping')['standard']);
        $this->setBasketPaymentMethod($I, $basketId, Fixtures::get('payment_id'));

        //Get token and approval url
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId, HttpCode::OK);

        //token is valid but not yet approved
        $result = $this->paypalTokenStatus($I, $approvalDetails['data']['paypalApprovalProcess']['token']);
        $I->assertFalse($result['data']['paypalTokenStatus']['status']);

        //make customer login to paypal but cancel
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->loginToPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //token is not approved
        $result = $this->paypalTokenStatus($I, $approvalDetails['data']['paypalApprovalProcess']['token']);
        $I->assertFalse($result['data']['paypalTokenStatus']['status']);

        //make customer approve the payment
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal(Fixtures::get('sBuyerLogin'), Fixtures::get('sBuyerPassword'));

        //token is approved
        $result = $this->paypalTokenStatus($I, $approvalDetails['data']['paypalApprovalProcess']['token']);
        $I->assertTrue($result['data']['paypalTokenStatus']['status']);
    }
}