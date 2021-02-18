<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use Codeception\Util\HttpCode;
use TheCodingMachine\GraphQLite\Types\ID;

trait GraphqlCheckoutTrait
{
    protected function getGQLResponse(
        AcceptanceTester $I,
        string $query,
        array $variables = [],
        int $status = HttpCode::OK
    ): array {
        $I->sendGQLQuery($query, $variables);
        $I->seeResponseCodeIs($status);
        $I->seeResponseIsJson();

        return $I->grabJsonResponseAsArray();
    }

    protected function createBasket(AcceptanceTester $I, string $basketTitle): string
    {
        $variables = [
            'title' => $basketTitle,
        ];

        $query = '
            mutation ($title: String!){
                basketCreate(basket: {title: $title}) {
                    id
                }
            }
        ';
        $result = $this->getGQLResponse($I, $query, $variables);

        return $result['data']['basketCreate']['id'];
    }

    protected function addProductToBasket(AcceptanceTester $I, string $basketId, string $productId, float $amount): array
    {
        $variables = [
            'basketId'  => $basketId,
            'productId' => $productId,
            'amount'    => $amount,
        ];

        $mutation = '
            mutation ($basketId: String!, $productId: String!, $amount: Float! ) {
                basketAddProduct(
                    basketId: $basketId,
                    productId: $productId,
                    amount: $amount
                ) {
                    id
                    items {
                        product {
                            id
                        }
                        amount
                    }
                }
            }
        ';

        $result = $this->getGQLResponse($I, $mutation, $variables);

        return $result['data']['basketAddProduct']['items'];
    }

    protected function setBasketDeliveryMethod(
        AcceptanceTester $I,
        string $basketId,
        string $deliverySetId,
        int $status = HttpCode::OK
    ): string {
        $variables = [
            'basketId'   => new ID($basketId),
            'deliveryId' => new ID($deliverySetId),
        ];

        $mutation = '
            mutation ($basketId: ID!, $deliveryId: ID!) {
                basketSetDeliveryMethod(
                    basketId: $basketId,
                    deliveryMethodId: $deliveryId
                    ) {
                    deliveryMethod {
                        id
                    }
                }
            }
        ';
        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        if ($status === HttpCode::BAD_REQUEST) {
            return (string) $result['errors'][0]['message'];
        }

        return (string) $result['data']['basketSetDeliveryMethod']['deliveryMethod']['id'];
    }

    protected function setBasketPaymentMethod(AcceptanceTester $I, string $basketId, string $paymentId): string
    {
        $variables = [
            'basketId'  => new ID($basketId),
            'paymentId' => new ID($paymentId),
        ];

        $mutation = '
            mutation ($basketId: ID!, $paymentId: ID!) {
                basketSetPayment(
                    basketId: $basketId,
                    paymentId: $paymentId
                    ) {
                    id
                }
            }
        ';
        $result = $this->getGQLResponse($I, $mutation, $variables);

        return $result['data']['basketSetPayment']['id'];
    }

    protected function placeOrder(AcceptanceTester $I, string $basketId, int $status = HttpCode::OK, ?bool $termsAndConditions = null): array
    {
        //now actually place the order
        $variables = [
            'basketId'                  => new ID($basketId),
            'confirmTermsAndConditions' => $termsAndConditions,
        ];

        $mutation = '
            mutation ($basketId: ID!, $confirmTermsAndConditions: Boolean) {
                placeOrder(
                    basketId: $basketId
                    confirmTermsAndConditions: $confirmTermsAndConditions
                ) {
                    id
                    orderNumber
                }
            }
        ';

        return $this->getGQLResponse($I, $mutation, $variables, $status);
    }

    protected function paypalApprovalProcess(AcceptanceTester $I, string $basketId, int $status = HttpCode::OK): array
    {
        $variables = [
            'basketId' => $basketId,
            'returnUrl' => EshopRegistry::getConfig()->getShopUrl()
        ];

        $mutation = '
            query ($basketId: String!, $returnUrl: String!) {
                paypalApprovalProcess(
                    basketId: $basketId,
                    returnUrl: $returnUrl,
                    cancelUrl: ""
                    displayBasketInPayPal: true
                ) {
                   token
                   communicationUrl
                }
            }
        ';

        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        return $result;
    }

    protected function paypalTokenStatus(AcceptanceTester $I, string $token, int $status = HttpCode::OK): array
    {
        $variables = [
            'token' => $token
        ];

        $mutation = '
            query ($token: String!) {
                paypalTokenStatus(
                    paypalToken: $token
                ) {
                   token
                   status
                }
            }
        ';

        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        return $result;
    }

    protected function createDeliveryAddress(AcceptanceTester $I, string $countryId = 'a7c40f631fc920687.20179984'): string
    {
        $variables = [
            'countryId' => new ID($countryId),
        ];

        $mutation = 'mutation ($countryId: ID!) {
                customerDeliveryAddressAdd(deliveryAddress: {
                    salutation: "MRS",
                    firstName: "Marlene",
                    lastName: "Musterlich",
                    additionalInfo: "protected delivery",
                    street: "Bertoldstrasse",
                    streetNumber: "48",
                    zipCode: "79098",
                    city: "Freiburg",
                    countryId: $countryId}
                    ){
                       id
                    }
                }
            ';

        $result = $this->getGQLResponse($I, $mutation, $variables);

        return $result['data']['customerDeliveryAddressAdd']['id'];
    }

    protected function setBasketDeliveryAddress(
        AcceptanceTester $I,
        string $basketId,
        string $deliveryAddressId
    ): array
    {
        $variables = [
            'basketId'          => $basketId,
            'deliveryAddressId' => $deliveryAddressId,
        ];

        $mutation = '
            mutation ($basketId: String!, $deliveryAddressId: String!) {
                basketSetDeliveryAddress(basketId: $basketId, deliveryAddressId: $deliveryAddressId) {
                    deliveryAddress {
                        id
                    }
                }
            }';

        return $this->getGQLResponse($I, $mutation, $variables);
    }

    protected function getLatestOrderFromOrderHistory(AcceptanceTester $I): array
    {
        $mutation = '
            query {
                customer {
                    id
                    orders(
                        pagination: {limit: 1, offset: 0}
                    ){
                        id
                        orderNumber
                        invoiceNumber
                        invoiced
                        cancelled
                        ordered
                        paid
                        updated
                        cost {
                            total
                            voucher
                            discount
                        }
                        vouchers {
                            id
                        }
                        invoiceAddress {
                            firstName
                            lastName
                            street
                        }
                        deliveryAddress {
                            firstName
                            lastName
                            street
                            country {
                                id
                            }
                        }
                    }
                }
            }
        ';

        $result = $this->getGQLResponse($I, $mutation);

        return $result['data']['customer']['orders'][0];
    }

    protected function getBasketPaymentIds(AcceptanceTester $I, string $basketId): array
    {
        $variables = [
            'basketId' => new ID($basketId)
        ];

        $query = 'query ($basketId: ID!) {
                     basketPayments(basketId:$basketId){
                         id
                     }
                }';

        $raw = $this->getGQLResponse($I, $query, $variables);

        $result = [];
        foreach ($raw['data']['basketPayments'] as $sub) {
            $result[$sub['id']] = $sub['id'];
        }

        return $result;
    }
}