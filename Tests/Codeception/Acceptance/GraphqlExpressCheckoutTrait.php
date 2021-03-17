<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;

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
            query ($basketId: String!, $returnUrl: String!, $cancelUrl: String!) {
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

    protected function removeProductFromBasket(
        AcceptanceTester $I,
        string $basketId,
        string $productId,
        int $amount = 1
    ): array
    {
        $variables = [
            'basketId'  => $basketId,
            'productId' => $productId,
            'amount'    => $amount
        ];

        $mutation = 'mutation ($basketId: String!, $productId: String!, $amount: Int!) {
            basketRemoveProduct(
            basketId: $basketId, 
            productId: $productId, 
            amount: $amount ) {
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

        $mutation = 'mutation ($basketId: String!, $voucherNumber: String!) {
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