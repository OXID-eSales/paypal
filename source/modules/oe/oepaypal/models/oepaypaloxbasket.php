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
 * @copyright (C) OXID eSales AG 2003-2013
 */

/**
 * PayPal oxBasket class
 */
class oePayPalOxBasket extends oePayPalOxBasket_parent
{
    /**
     * Checks if products in basket ar virtual and does not require real delivery.
     * Returns TRUE if virtual
     *
     * @return bool
     */
    public function isVirtualPayPalBasket()
    {
        $blVirtual = true;

        $aProducts = $this->getBasketArticles();
        foreach ( $aProducts as $oProduct ) {
            if ( !$oProduct->isVirtualPayPalArticle() ) {
                $blVirtual = false;
                break;
            }
        }

        return $blVirtual;
    }

    /**
     * Returns wrapping cost value
     *
     * @return double
     */
    public function getPayPalWrappingCosts()
    {
        $dWrappingPrice = 0;

        $oWrappingCost = $this->getCosts( 'oxwrapping' );
        if ( $oWrappingCost ) {
            $dWrappingPrice = $this->isCalculationModeNetto() ? $oWrappingCost->getNettoPrice() : $oWrappingCost->getBruttoPrice();
        }

        return $dWrappingPrice;
    }

    /**
     * Returns greeting card cost value
     *
     * @return double
     */
    public function getPayPalGiftCardCosts()
    {
        $dGiftCardPrice = 0;

        $oGiftCardCost = $this->getCosts( 'oxgiftcard' );
        if ( $oGiftCardCost ) {
            $dGiftCardPrice = $this->isCalculationModeNetto() ? $oGiftCardCost->getNettoPrice() : $oGiftCardCost->getBruttoPrice();
        }

        return $dGiftCardPrice;
    }

    /**
     * Returns payment costs netto or brutto value
     * @return double
     */
    public function getPayPalPaymentCosts()
    {
        $dPaymentCost = 0;

        $oPaymentCost = $this->getCosts( 'oxpayment' );
        if ( $oPaymentCost ) {
            $dPaymentCost = $this->isCalculationModeNetto() ? $oPaymentCost->getNettoPrice() : $oPaymentCost->getBruttoPrice();
        }

        return $dPaymentCost;
    }

    /**
     * Returns Trusted shops costs netto or brutto value
     * @return double
     */
    public function getPayPalTsProtectionCosts()
    {
        $dTsPaymentCost = 0;

        $oTsPaymentCost = $this->getCosts( 'oxtsprotection' );
        if ( $oTsPaymentCost ) {
            $dTsPaymentCost = $this->isCalculationModeNetto() ? $oTsPaymentCost->getNettoPrice() : $oTsPaymentCost->getBruttoPrice();
        }

        return $dTsPaymentCost;
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
        $dDiscount = 0;

        $oTotalDiscount = $this->getTotalDiscount();

        if ( $oTotalDiscount )  {
            $dDiscount += $oTotalDiscount->getBruttoPrice();
        }

        //if payment costs are negative, adding them to discount
        if ( ( $dCosts = $this->getPaymentCosts() ) < 0 ) {
            $dDiscount += ( $dCosts * -1);
        }

        // vouchers..
        $aVouchers = (array) $this->getVouchers();
        foreach ( $aVouchers as $oVoucher ) {
            $dDiscount += round( $oVoucher->dVoucherdiscount, 2 );
        }

        return $dDiscount;
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
        $dAllCosts = $this->getProductsPrice()->getSum( $this->isCalculationModeNetto() );

        //adding to additional costs only if payment is > 0
        if ( ( $dCosts = $this->getPayPalPaymentCosts() ) > 0) {
            $dAllCosts += $dCosts;
        }

        // wrapping costs
        $dAllCosts += $this->getPayPalWrappingCosts();

        // greeting card costs
        $dAllCosts += $this->getPayPalGiftCardCosts();

        // Trusted shops protection cost
        $dAllCosts += $this->getPayPalTsProtectionCosts();

        return $dAllCosts;
    }

    /**
     * Returns absolute VAT value
     * @return float
     */
    public function getPayPalBasketVatValue()
    {
        $flBasketVatValue = 0;
        $flBasketVatValue += $this->getPayPalProductVat();
        $flBasketVatValue += $this->getPayPalWrappingVat();
        $flBasketVatValue += $this->getPayPalGiftCardVat();
        $flBasketVatValue += $this->getPayPalPayCostVat();
        $flBasketVatValue += $this->getPayPalTsProtectionCostVat();

        return $flBasketVatValue;
    }

    /**
     * Return products VAT.
     * @return double
     */
    public function getPayPalProductVat()
    {
        $aProductVatValue  = $this->getProductVats( false );
        $dProductVatValue = array_sum( $aProductVatValue );
        return $dProductVatValue;
    }

    /**
     * Return wrapping VAT.
     * @return double
     */
    public function getPayPalWrappingVat()
    {
        $dWrappingVat = 0;

        $oWrapping = $this->getCosts( 'oxwrapping' );
        if ( $oWrapping && $oWrapping->getVatValue() ) {
            $dWrappingVat = $oWrapping->getVatValue();
        }

        return $dWrappingVat;
    }

    /**
     * Return gift card VAT.
     * @return double
     */
    public function getPayPalGiftCardVat()
    {
        $dGiftCardVat = 0;

        $oGiftCard = $this->getCosts( 'oxgiftcard' );
        if ( $oGiftCard && $oGiftCard->getVatValue() ) {
            $dGiftCardVat = $oGiftCard->getVatValue();
        }

        return $dGiftCardVat;
    }

    /**
     * Return payment VAT.
     * @return double
     */
    public function getPayPalPayCostVat()
    {
        $dPayVAT = 0;

        $oPaymentCost = $this->getCosts( 'oxpayment' );
        if ( $oPaymentCost && $oPaymentCost->getVatValue()) {
            $dPayVAT = $oPaymentCost->getVatValue();
        }
        return $dPayVAT;
    }

    /**
     * Return payment VAT.
     * @return double
     */
    public function getPayPalTsProtectionCostVat()
    {
        $dVAT = 0;
        $oCost = $this->getCosts( 'oxtsprotection' );
        if ( $oCost && $oCost->getVatValue()) {
            $dVAT = $oCost->getVatValue();
        }
        return $dVAT;
    }

}
