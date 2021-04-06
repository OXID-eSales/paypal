<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\GraphQL\Service;

use OxidEsales\GraphQL\Storefront\Basket\DataType\Basket as BasketDataType;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

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

    /**
     * @Field()
     */
    public function paypalServiceType(BasketDataType $basket): int
    {
        return (int) $basket->getEshopModel()->getFieldData('OEPAYPAL_SERVICE_TYPE');
    }
}
