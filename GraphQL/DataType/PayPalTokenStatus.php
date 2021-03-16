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

namespace OxidEsales\PayPalModule\GraphQL\DataType;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
final class PayPalTokenStatus
{
    /** @var string */
    private $token;

    /** @var bool */
    private $status;

    /** @var string */
    private $payerId;

    public function __construct(string $token, bool $status, string $payerId)
    {
        $this->token = $token;
        $this->status = $status;
        $this->payerId = $payerId;
    }

    /**
     * @Field()
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @Field()
     */
    public function isTokenApproved(): bool
    {
        return $this->status;
    }

    // for internal user only
    public function getPayerId(): string
    {
        return $this->payerId;
    }
}
