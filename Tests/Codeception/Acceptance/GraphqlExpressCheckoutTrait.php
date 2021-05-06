<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use TheCodingMachine\GraphQLite\Types\ID;

trait GraphqlExpressCheckoutTrait
{
    protected function paypalExpressApprovalProcess(
        AcceptanceTester $I,
        string $basketId
    ): array
    {
        $variables = [
            'basketId' => $basketId,
            'returnUrl' => EshopRegistry::getConfig()->getShopUrl(),
            'cancelUrl' => EshopRegistry::getConfig()->getShopUrl()
        ];

        $mutation = '
            query ($basketId: ID!, $returnUrl: String!, $cancelUrl: String!) {
                paypalExpressApprovalProcess(
                    basketId: $basketId,
                    returnUrl: $returnUrl,
                    cancelUrl: $cancelUrl,
                    displayBasketInPayPal: true
                ) {
                   token
                   communicationUrl
                }
            }
        ';

        return $this->getGQLResponse($I, $mutation, $variables);
    }

    protected function removeItemFromBasket(
        AcceptanceTester $I,
        string $basketId,
        string $itemId,
        int $amount = 1
    ): array
    {
        $variables = [
            'basketId' => $basketId,
            'itemId'   => $itemId,
            'amount'   => $amount
        ];

        $mutation = 'mutation ($basketId: ID!, $itemId: ID!, $amount: Int!) {
            basketRemoveItem(
                basketId: $basketId, 
                itemId: $itemId, 
                amount: $amount
            ) {
                id
            }
        }';

        return $this->getGQLResponse($I, $mutation, $variables);
    }

    protected function addVoucherToBasket(
        AcceptanceTester $I,
        string $basketId,
        string $voucherNumber
    ) : array
    {
        $variables = [
            'basketId'      => $basketId,
            'voucherNumber' => $voucherNumber
        ];

        $mutation = 'mutation ($basketId: ID!, $voucherNumber: String!) {
            basketAddVoucher (
                basketId: $basketId,
                voucherNumber: $voucherNumber
            ) {
                id
            }
        }';

        return $this->getGQLResponse($I, $mutation, $variables);
    }

    protected function removeVoucherFromBasket(
        AcceptanceTester $I,
        string $basketId,
        string $voucherId
    ) : array
    {
        $variables = [
            'basketId'  => $basketId,
            'voucherId' => $voucherId
        ];

        $mutation = 'mutation ($basketId: String!, $voucherId: String!) {
            basketRemoveVoucher (
                basketId: $basketId,
                voucherId: $voucherId
            ) {
                id
            }
        }';

        return $this->getGQLResponse($I, $mutation, $variables);
    }
}