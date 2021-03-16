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

trait GraphqlExpressCheckoutTrait
{
    protected function paypalExpressApprovalProcess(
        AcceptanceTester $I,
        string $basketId,
        int $status = HttpCode::OK
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

        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        return $result;
    }

    protected function removeProductFromBasket(
        AcceptanceTester $I,
        string $basketId,
        string $productId,
        int $amount = 1,
        int $status = HttpCode::OK
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

        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        return $result;
    }

    protected function addVoucherToBasket(
        AcceptanceTester $I,
        string $basketId,
        string $voucherNumber,
        int $status = HttpCode::OK
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

        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        return $result;
    }

    protected function removeVoucherFromBasket(
        AcceptanceTester $I,
        string $basketId,
        string $voucherId,
        int $status = HttpCode::OK
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

        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        return $result;
    }
}