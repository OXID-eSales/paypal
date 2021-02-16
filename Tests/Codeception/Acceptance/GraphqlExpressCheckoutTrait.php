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
            'returnUrl' => EshopRegistry::getConfig()->getShopUrl()
        ];

        $mutation = '
            query ($basketId: String!, $returnUrl: String!) {
                paypalExpressApprovalProcess(
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
}