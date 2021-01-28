<?php

declare(strict_types=1);

namespace OxidEsales\PayPalModule\GraphQL\DataType;

use OxidEsales\GraphQL\Storefront\Basket\DataType\Basket as BasketDataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;

/**
 * @ExtendType(class=BasketDataType::class)
 */
final class BasketExtendType
{
    /**
     * @Field()
     */
    public function paypalToken(BasketDataType $basket): string
    {
        return $basket->getEshopModel()->getFieldData('OEPAYPAL_PAYMENT_TOKEN');
    }
}
