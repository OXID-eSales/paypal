<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\GraphQL\Service;

use OxidEsales\GraphQL\Base\Framework\NamespaceMapperInterface;

final class NamespaceMapper implements NamespaceMapperInterface
{
    public function getControllerNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\PayPalModule\\GraphQL\\Controller' => __DIR__ . '/../Controller/',
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\PayPalModule\\GraphQL\\DataType' => __DIR__ . '/../DataType/',
//            '\\OxidEsales\\PayPalModule\\GraphQL\\Service' => __DIR__ . '/../Service/',
//            '\\OxidEsales\\PayPalModule\\GraphQL\\Infrastructure' => __DIR__ . '/../DataType/',
        ];
    }
}
