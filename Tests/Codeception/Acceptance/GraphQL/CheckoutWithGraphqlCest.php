<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance\GraphQL;

use OxidEsales\GraphQL\Storefront\Basket\Exception\BasketAccessForbidden;
use OxidEsales\GraphQL\Storefront\Country\Exception\CountryNotFound;
use OxidEsales\PayPalModule\GraphQL\Exception\BasketCommunication;
use OxidEsales\PayPalModule\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use Codeception\Util\Fixtures;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use OxidEsales\PayPalModule\GraphQL\Exception\PaymentValidation;
use OxidEsales\PayPalModule\GraphQL\Exception\BasketValidation;

/**
 * @group oepaypal
 * @group oepaypal_graphql
 * @group oepaypal_graphql_checkout
 */
class CheckoutWithGraphqlCest extends BaseCest
{
    private const EXPIRED_TOKEN = 'EC-20P17490LV1421614';

    use GraphqlCheckoutTrait;

    public function _before(AcceptanceTester $I): void
    {
        if (!($I->checkGraphBaseActive() && $I->checkGraphStorefrontActive())) {
            $I->markTestSkipped('GraphQL modules are not active');
        }

        parent::_before($I);

        $I->updateConfigInDatabase('blPerfNoBasketSaving', false, 'bool');

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

        //query basket payments
        $basketPayments = $this->getBasketPaymentIds($I, $basketId);
        $I->assertArrayHasKey(Fixtures::get('payment_id'), $basketPayments);

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

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
    public function checkoutWithGraphqlNewUserWithoutInvoiceAddress(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order with PayPal via graphql for newly registered user without invoice address');

        //register customer via graphql, he's in 'oxidnotyetordered' group.
        $username     = 'newPayPalUser@oxid-esales.com';
        $password     = 'useruser';
        $this->registerCustomer($I, $username, $password);

        //log in to graphql
        $I->loginToGraphQLApi($username, $password);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 2);

        //this is as far as we get in this case because of missing invoice country
        $shippingId = Fixtures::get('shipping')['standard'];
        $result = $this->setBasketDeliveryMethod($I, $basketId, $shippingId);

        $expectedException = CountryNotFound::byId('');
        $I->assertStringContainsString($expectedException->getMessage(), $result);
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
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

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
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        //change basket contents
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 1);

        //place the order
        $result = $this->placeOrder($I, $basketId);

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
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        //We change delivery address to country (Belgium) which is not assigned to oxidstandard delivery set.
        $this->setBasketDeliveryAddress($I, $basketId, $this->createDeliveryAddress($I, 'a7c40f632e04633c9.47194042'));

        //place the order
        $result = $this->placeOrder($I, $basketId);
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
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        //change delivery address to one where country is assigned to oxidpaypal payment method.
        $this->setBasketDeliveryAddress($I, $basketId, $this->createDeliveryAddress($I));

        //place the order
        $result = $this->placeOrder($I, $basketId);

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
        $loginPage->loginToPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        //placing the order fails
        $result  = $this->placeOrder($I, $basketId);

        $expectedException = BasketCommunication::notConfirmed($basketId);
        $I->assertEquals($expectedException->getMessage(), $result['errors'][0]['message']);
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
        $result  = $this->placeOrder($I, $basketId);

        $expectedException = BasketCommunication::notConfirmed($basketId);
        $I->assertEquals($expectedException->getMessage(), $result['errors'][0]['message']);
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
        $result  = $this->placeOrder($I, $basketId);

        $expectedException = BasketCommunication::notStarted($basketId);
        $I->assertEquals($expectedException->getMessage(), $result['errors'][0]['message']);
    }

    /**
     * @group paypal_external
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlEmptyBasket(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order fails with PayPal via graphql with empty basket');
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');

        //Get token and approval url, make customer approve the payment
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);

        $expectedException = PaymentValidation::paymentMethodIsNotPaypal();
        $I->assertEquals($expectedException->getMessage(), $approvalDetails['errors'][0]['message']);
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
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);
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
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

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
        $result = $this->placeOrder($I, $basketId);

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
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);

        $expectedException = PaymentValidation::paymentMethodIsNotPaypal();
        $I->assertEquals($expectedException->getMessage(), $approvalDetails['errors'][0]['message']);
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

        $result = $this->paypalTokenStatus($I, self::EXPIRED_TOKEN);

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
        $approvalDetails = $this->paypalApprovalProcess($I, $basketId);

        //token is valid but not yet approved
        $result = $this->paypalTokenStatus($I, $approvalDetails['data']['paypalApprovalProcess']['token']);
        $I->assertFalse($result['data']['paypalTokenStatus']['tokenApproved']);

        //make customer login to paypal but cancel
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->loginToPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        //token is not approved
        $result = $this->paypalTokenStatus($I, $approvalDetails['data']['paypalApprovalProcess']['token']);
        $I->assertFalse($result['data']['paypalTokenStatus']['tokenApproved']);

        //make customer approve the payment
        $I->amOnUrl($approvalDetails['data']['paypalApprovalProcess']['communicationUrl']);
        $loginPage = new PayPalLogin($I);
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        //token is approved
        $result = $this->paypalTokenStatus($I, $approvalDetails['data']['paypalApprovalProcess']['token']);
        $I->assertTrue($result['data']['paypalTokenStatus']['tokenApproved']);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     */
    public function checkoutWithGraphqlPlaceOrderWhichDoesNotBelongToYou(AcceptanceTester $I)
    {
        $I->wantToTest('placing an order with PayPal via graphql which does not belong to you');
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
        $loginPage->approveGraphqlStandardPayPal($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

        $I->logoutFromGraphQLApi();
        $I->haveInDatabase('oxuser', $I->getExistingUserData());
        $I->haveInDatabase('oxobject2group', Fixtures::get('usergroups'));
        $I->loginToGraphQLApi($I->getExistingUserName(), $I->getExistingUserPassword(), 0);

        //place the order
        $result = $this->placeOrder($I, $basketId);
        $I->assertStringContainsString(
            BasketAccessForbidden::byAuthenticatedUser()->getMessage(),
            $result['errors'][0]['message']
        );

        $I->logoutFromGraphQLApi();
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        $I->assertNotEmpty($orderId);
    }
}