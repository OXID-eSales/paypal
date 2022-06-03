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

namespace OxidEsales\PayPalModule\GraphQL\Subscriber;

use OxidEsales\PayPalModule\GraphQL\Exception\GraphQLServiceNotFound;
use Symfony\Contracts\EventDispatcher\Event;
use OxidEsales\EshopCommunity\Internal\Framework\Event\AbstractShopAwareEventSubscriber;
use OxidEsales\PayPalModule\GraphQL\Service\BeforePlaceOrder as BeforePlaceOrderService;

class BeforePlaceOrder extends AbstractShopAwareEventSubscriber
{
    /** @var BeforePlaceOrderService */
    private $beforePlaceOrderService;

    public function __construct(BeforePlaceOrderService $beforePlaceOrderService)
    {
        $this->beforePlaceOrderService = $beforePlaceOrderService;
    }

    public function handle(Event $event): Event
    {
        $this->validateState();

        $this->beforePlaceOrderService->handle($event);
        
        return $event;
    }

    public static function getSubscribedEvents()
    {
        return [
            'OxidEsales\GraphQL\Storefront\Basket\Event\BeforePlaceOrder' => 'handle'
        ];
    }

    protected function validateState(): void
    {
        if (is_null($this->beforePlaceOrderService)) {
            throw GraphQLServiceNotFound::byServiceName(BeforePlaceOrderService::class);
        }
    }
}