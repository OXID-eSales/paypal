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

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal basket class
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Basket
 */
class Basket extends Basket_parent
{
    /**
     * Checks if products in basket ar virtual and does not require real delivery.
     * Returns TRUE if virtual
     *
     * @return bool
     */
    public function isVirtualPayPalBasket()
    {
        $isVirtual = true;

        $products = $this->getBasketArticles();
        foreach ($products as $product) {
            if (!$product->isVirtualPayPalArticle()) {
                $isVirtual = false;
                break;
            }
        }

        return $isVirtual;
    }

    /**
     * Checks if fraction quantity items (with 1.3 amount) exists in basket.
     *
     * @return bool
     */
    public function isFractionQuantityItemsPresent()
    {
        $fractionItemsPresent = false;

        foreach ($this->getContents() as $basketItem) {
            $amount = $basketItem->getAmount();
            if ((int) $amount != $amount) {
                $fractionItemsPresent = true;
                break;
            }
        }

        return $fractionItemsPresent;
    }

    /**
     * Returns wrapping cost value
     *
     * @return double
     */
    public function getPayPalWrappingCosts()
    {
        $amount = 0.0;

        $wrappingCost = $this->getCosts('oxwrapping');
        if ($wrappingCost) {
            $amount = $this->isCalculationModeNetto() ? $wrappingCost->getNettoPrice() : $wrappingCost->getBruttoPrice();
        }

        return $amount;
    }

    /**
     * Returns greeting card cost value
     *
     * @return double
     */
    public function getPayPalGiftCardCosts()
    {
        $amount = 0.0;

        $giftCardCost = $this->getCosts('oxgiftcard');
        if ($giftCardCost) {
            $amount = $this->isCalculationModeNetto() ? $giftCardCost->getNettoPrice() : $giftCardCost->getBruttoPrice();
        }

        return $amount;
    }

    /**
     * Returns payment costs netto or brutto value.
     *
     * @return double
     */
    public function getPayPalPaymentCosts()
    {
        $amount = 0.0;

        $paymentCost = $this->getCosts('oxpayment');
        if ($paymentCost) {
            $amount = $this->isCalculationModeNetto() ? $paymentCost->getNettoPrice() : $paymentCost->getBruttoPrice();
        }

        return $amount;
    }
    
    /**
     * Collects all basket discounts (basket, payment and vouchers)
     * and returns sum of collected discounts.
     *
     * @return double
     */
    public function getDiscountSumPayPalBasket()
    {
        // collect discounts
        $discount = 0.0;

        $totalDiscount = $this->getTotalDiscount();

        if ($totalDiscount) {
            $discount += $totalDiscount->getBruttoPrice();
        }

        //if payment costs are negative, adding them to discount
        if (($costs = $this->getPaymentCosts()) < 0) {
            $discount += ($costs * -1);
        }

        // vouchers..
        $vouchers = (array) $this->getVouchers();
        foreach ($vouchers as $voucher) {
            $discount += round($voucher->dVoucherdiscount, 2);
        }

        return $discount;
    }

    /**
     * Calculates basket costs (payment, GiftCard and gift card)
     * and returns sum of all costs.
     *
     * @return double
     */
    public function getSumOfCostOfAllItemsPayPalBasket()
    {
        // basket items sum
        $allCosts = $this->getProductsPrice()->getSum($this->isCalculationModeNetto());

        //adding to additional costs only if payment is > 0
        if (($costs = $this->getPayPalPaymentCosts()) > 0) {
            $allCosts += $costs;
        }

        // wrapping costs
        $allCosts += $this->getPayPalWrappingCosts();

        // greeting card costs
        $allCosts += $this->getPayPalGiftCardCosts();

        return $allCosts;
    }

    /**
     * Returns absolute VAT value.
     *
     * @return float
     */
    public function getPayPalBasketVatValue()
    {
        $basketVatValue = 0;
        $basketVatValue += $this->getPayPalProductVat();
        $basketVatValue += $this->getPayPalWrappingVat();
        $basketVatValue += $this->getPayPalGiftCardVat();
        $basketVatValue += $this->getPayPalPayCostVat();

        if ($this->getDeliveryCosts() < round($this->getDeliveryCosts(), 2)) {
            return floor($basketVatValue * 100) / 100;
        }

        return $basketVatValue;
    }

    /**
     * Return products VAT.
     *
     * @return double
     */
    public function getPayPalProductVat()
    {
        $productVatList = $this->getProductVats(false);
        $productVatSum = array_sum($productVatList);

        return $productVatSum;
    }

    /**
     * Return wrapping VAT.
     *
     * @return double
     */
    public function getPayPalWrappingVat()
    {
        $wrappingVat = 0.0;

        $wrapping = $this->getCosts('oxwrapping');
        if ($wrapping && $wrapping->getVatValue()) {
            $wrappingVat = $wrapping->getVatValue();
        }

        return $wrappingVat;
    }

    /**
     * Return gift card VAT.
     *
     * @return double
     */
    public function getPayPalGiftCardVat()
    {
        $giftCardVat = 0.0;

        $giftCard = $this->getCosts('oxgiftcard');
        if ($giftCard && $giftCard->getVatValue()) {
            $giftCardVat = $giftCard->getVatValue();
        }

        return $giftCardVat;
    }

    /**
     * Return payment VAT.
     *
     * @return double
     */
    public function getPayPalPayCostVat()
    {
        $paymentVAT = 0.0;

        $paymentCost = $this->getCosts('oxpayment');
        if ($paymentCost && $paymentCost->getVatValue()) {
            $paymentVAT = $paymentCost->getVatValue();
        }

        return $paymentVAT;
    }
}
