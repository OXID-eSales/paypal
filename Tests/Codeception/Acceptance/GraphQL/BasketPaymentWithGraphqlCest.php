<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance\GraphQL;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\PayPalModule\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use Codeception\Util\Fixtures;

/**
 * @group oepaypal
 * @group oepaypal_graphql
 * @group oepaypal_graphql_basketpayment
 */
class BasketPaymentWithGraphqlCest extends BaseCest
{
    use GraphqlCheckoutTrait;
    use GraphqlExpressCheckoutTrait;

    public function _before(AcceptanceTester $I): void
    {
        if (!($I->checkGraphBaseActive() && $I->checkGraphStorefrontActive())) {
            $I->markTestSkipped('GraphQL modules are not active');
        }

        parent::_before($I);

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

        $I->updateConfigInDatabase('blPerfNoBasketSaving', false, 'bool');
        $this->enablePayments();
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->enablePayments();

        parent::_after($I);
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function testPaypalBasketPayments(AcceptanceTester $I): void
    {
        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare standard basket
        $basketId = $this->createBasket($I, 'pp_cart');
        $result = $this->getBasketPaymentIds($I, $basketId);

        $I->assertSame(
            $this->getPaymentsArray(true),
            $result
        );

        //prepare pp express basket
        $basketId = $this->prepareExpressBasket($I, 'pp_express_cart');
        $result = $this->getBasketPaymentIds($I, $basketId);

        $I->assertSame(
            $this->getPaymentsArray(true),
            $result
        );
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function testBasketPaymentsStandardPaymentTurnedOff(AcceptanceTester $I): void
    {
        $this->disableStandardPayment();

        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare standard basket
        $basketId = $this->createBasket($I, 'pp_cart_no_standart_payment');
        $result = $this->getBasketPaymentIds($I, $basketId);

        $I->assertSame(
            $this->getPaymentsArray(),
            $result
        );

        //prepare pp express basket
        $basketId = $this->prepareExpressBasket($I, 'pp_express_cart_no_standart_payment');
        $result = $this->getBasketPaymentIds($I, $basketId);

        $I->assertSame(
            $this->getPaymentsArray(true),
            $result
        );

        $this->enableStandardPayment();
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function testBasketPaymentsExpressPaymentTurnedOff(AcceptanceTester $I): void
    {
        $this->disableExpressPayment();

        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword());

        //prepare standard basket
        $basketId = $this->createBasket($I, 'pp_cart_no_express_payment');
        $result = $this->getBasketPaymentIds($I, $basketId);

        $I->assertSame(
            $this->getPaymentsArray(true),
            $result
        );

        //prepare pp express basket
        $result = $this->prepareExpressBasket($I, 'pp_express_cart_no_express_payments');

        $I->assertSame("Payment method 'oxidpaypal' is unavailable!", $result);

        $this->enableExpressPayment();
    }

    /**
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_checkout
     * @group paypal_graphql
     * @group paypal_graphql_express
     */
    public function testBasketPaymentsTurnedOff(AcceptanceTester $I): void
    {
        $this->disablePayments();

        $I->loginToGraphQLApi($I->getDemoUserName(), $I->getExistingUserPassword(), 0);

        //prepare standard basket
        $basketId = $this->createBasket($I, 'pp_cart_no_pp_payments');
        $result = $this->getBasketPaymentIds($I, $basketId);

        $I->assertSame(
            $this->getPaymentsArray(),
            $result
        );

        //prepare pp express basket
        $result = $this->prepareExpressBasket($I, 'pp_express_cart_no_express_payments');

        $I->assertSame("Payment method 'oxidpaypal' is unavailable!", $result);

        $this->enablePayments();
    }

    private function prepareExpressBasket(AcceptanceTester $I, string $basketTitle): string
    {
        $basketId = $this->createBasket($I, $basketTitle);
        $this->addProductToBasket($I, $basketId, Fixtures::get('product')['id'], 4);

        //Enable pp express process
        $result = $this->paypalExpressApprovalProcess(
            $I,
            $basketId
        );

        if(isset($result['errors'])) {
            return $result['errors'][0]['message'];
        } else {
            return $basketId;
        }
    }

    private function getPaymentsArray(bool $includePaypal = false): array
    {
        $availablePayments = [
            'oxidinvoice'    => 'oxidinvoice',
            'oxidpayadvance' => 'oxidpayadvance',
            'oxiddebitnote'  => 'oxiddebitnote',
            'oxidcashondel'  => 'oxidcashondel',
        ];

        if ($includePaypal === true) {
            $availablePayments = ['oxidpaypal' => 'oxidpaypal'] + $availablePayments;
        }

        return $availablePayments;
    }

    private function enablePayments(): void
    {
        $this->enableExpressPayment();
        $this->enableStandardPayment();
    }

    private function disablePayments(): void
    {
        $this->disableExpressPayment();
        $this->disableStandardPayment();
    }

    private function enableStandardPayment(): void
    {
        Registry::getConfig()->saveShopConfVar('bool', 'blOEPayPalStandardCheckout', true, null, 'module:oepaypal');
    }

    private function enableExpressPayment(): void
    {
        Registry::getConfig()->saveShopConfVar('bool', 'blOEPayPalExpressCheckout', true, null, 'module:oepaypal');
    }

    private function disableStandardPayment(): void
    {
        Registry::getConfig()->saveShopConfVar('bool', 'blOEPayPalStandardCheckout', false, null, 'module:oepaypal');
    }

    private function disableExpressPayment(): void
    {
        Registry::getConfig()->saveShopConfVar('bool', 'blOEPayPalExpressCheckout', false, null, 'module:oepaypal');
    }
}
