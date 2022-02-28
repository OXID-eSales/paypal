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

final class PermissionProvider
{
    public function getPermissions(): array
    {
        return [
            'oxidcustomer' => [
                'PAYPAL_EXPRESS_APPROVAL',
                'PAYPAL_TOKEN_STATUS'
            ],
            'oxidnotyetordered' => [
                'PAYPAL_EXPRESS_APPROVAL',
                'PAYPAL_TOKEN_STATUS'
            ],
            'oxidanonymous' => [
                'CREATE_BASKET',
                'VIEW_BASKET',
                'ADD_PRODUCT_TO_BASKET',
                'REMOVE_BASKET_PRODUCT',
                'ADD_VOUCHER',
                'REMOVE_VOUCHER',
                'PLACE_ORDER',
                'PAYPAL_EXPRESS_APPROVAL',
                'PAYPAL_TOKEN_STATUS'
            ],
        ];
    }
}
