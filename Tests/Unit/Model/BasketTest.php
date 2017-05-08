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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oxAccessRightException class.
 */
class BasketTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Test data provider
     *
     * @return array
     */
    public function isVirtualPayPalBasketDataProvider()
    {
        $product1 = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('isVirtualPayPalArticle'));
        $product1->expects($this->any())->method('isVirtualPayPalArticle')->will($this->returnValue(true));

        $product2 = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('isVirtualPayPalArticle'));
        $product2->expects($this->any())->method('isVirtualPayPalArticle')->will($this->returnValue(false));

        return array(
            array($product1, $product2, false),
            array($product1, $product1, true),
        );
    }

    /**
     * Test case for oePayPalUser::isVirtualPayPalArticle()
     *
     * @dataProvider isVirtualPayPalBasketDataProvider
     */
    public function testIsVirtualPayPalBasket($product1, $product2, $result)
    {
        $products = array($product1, $product2);

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getBasketArticles'));
        $basket->expects($this->once())->method('getBasketArticles')->will($this->returnValue($products));

        $this->assertEquals($result, $basket->isVirtualPayPalBasket());
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function getPayPalAdditionalCostsDataProvider()
    {
        return array(
            array(true, 17.6, 15.14, 15.14),
            array(false, 17.6, 15.14, 17.6),
            array(true, 0, 0, 0),
            array(false, 0, 0, 0),
        );
    }

    /**
     * Test case for oePayPalUser::getPayPalWrappingCosts()
     *
     * @dataProvider getPayPalAdditionalCostsDataProvider
     */
    public function testGetPayPalWrappingCosts($calculationModeNetto, $wrappingPriceBrutto, $wrappingPriceNetto, $wrappingPriceExpect)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getNettoPrice', 'getBruttoPrice'));
        $price->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($wrappingPriceBrutto));
        $price->expects($this->any())->method('getNettoPrice')->will($this->returnValue($wrappingPriceNetto));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts', 'isCalculationModeNetto'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxwrapping'))->will($this->returnValue($price));
        $basket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($calculationModeNetto));

        $this->assertEquals($wrappingPriceExpect, $basket->getPayPalWrappingCosts());
    }

    /**
     * Test case for oePayPalUser::getPayPalGiftCardCosts()
     *
     * @dataProvider getPayPalAdditionalCostsDataProvider
     */
    public function testGetPayPalGiftCardCosts($calculationModeNetto, $giftCardPriceBrutto, $giftCardPriceNetto, $giftCardPrice)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getNettoPrice', 'getBruttoPrice'));
        $price->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($giftCardPriceBrutto));
        $price->expects($this->any())->method('getNettoPrice')->will($this->returnValue($giftCardPriceNetto));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts', 'isCalculationModeNetto'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxgiftcard'))->will($this->returnValue($price));
        $basket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($calculationModeNetto));

        $this->assertEquals($giftCardPrice, $basket->getPayPalGiftCardCosts());
    }

    /**
     * Test case for oePayPalUser::getPayPalPaymentCosts()
     *
     * @dataProvider getPayPalAdditionalCostsDataProvider
     */
    public function testGetPayPalPaymentCosts($calculationModeNetto, $paymentCostsPriceBrutto, $paymentCostsPriceNetto, $paymentPrice)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getNettoPrice', 'getBruttoPrice'));
        $price->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($paymentCostsPriceBrutto));
        $price->expects($this->any())->method('getNettoPrice')->will($this->returnValue($paymentCostsPriceNetto));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts', 'isCalculationModeNetto'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxpayment'))->will($this->returnValue($price));
        $basket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($calculationModeNetto));

        $this->assertEquals($paymentPrice, $basket->getPayPalPaymentCosts());
    }

    /**
     * Test case for oePayPalUser::getPayPalTsProtectionCosts()
     *
     * @dataProvider getPayPalAdditionalCostsDataProvider
     */
    public function testGetPayPalTsProtectionCosts($calculationModeNetto, $paymentCostsPriceBrutto, $paymentCostsPriceNetto, $paymentPrice)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getNettoPrice', 'getBruttoPrice'));
        $price->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($paymentCostsPriceBrutto));
        $price->expects($this->any())->method('getNettoPrice')->will($this->returnValue($paymentCostsPriceNetto));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts', 'isCalculationModeNetto'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxtsprotection'))->will($this->returnValue($price));
        $basket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($calculationModeNetto));

        $this->assertEquals($paymentPrice, $basket->getPayPalTsProtectionCosts());
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function getDiscountSumPayPalBasketDataProvider()
    {
        $basketDiscount1 = new \OxidEsales\Eshop\Core\Price();
        $basketDiscount1->setPrice(2);

        $basketDiscount2 = new \OxidEsales\Eshop\Core\Price();
        $basketDiscount2->setPrice(4);

        // vouchers
        $voucher = new \OxidEsales\Eshop\Application\Model\Voucher();

        $VoucherDiscount1 = $voucher->getSimpleVoucher();
        $VoucherDiscount1->dVoucherdiscount = 6;

        $VoucherDiscount2 = $voucher->getSimpleVoucher();
        $VoucherDiscount2->dVoucherdiscount = 8;

        $paymentCost1 = 7;
        $paymentCost2 = -7;

        return array(
            array(0, array(), 0, 0),
            array(0, array(), $paymentCost1, 0),
            array($basketDiscount1, array($VoucherDiscount1, $VoucherDiscount2), $paymentCost1, 16),
            array($basketDiscount2, array($VoucherDiscount1, $VoucherDiscount2), $paymentCost2, 25),
        );
    }

    /**
     * Test case for oePayPalUser::getDiscountSumPayPalBasket()
     *
     * @dataProvider getDiscountSumPayPalBasketDataProvider
     */
    public function testGetDiscountSumPayPalBasket($discount, $vouchers, $paymentCost, $result)
    {
        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getTotalDiscount', 'getPaymentCosts', 'getVouchers'));
        $basket->expects($this->once())->method('getTotalDiscount')->will($this->returnValue($discount));
        $basket->expects($this->once())->method('getVouchers')->will($this->returnValue($vouchers));
        $basket->expects($this->once())->method('getPaymentCosts')->will($this->returnValue($paymentCost));

        $this->assertEquals($result, $basket->getDiscountSumPayPalBasket());
    }


    /**
     * Test data provider
     *
     * @return array
     */
    public function getSumOfCostOfAllItemsPayPalBasketDataProvider()
    {
        // discounts
        $productsPrice = new \OxidEsales\Eshop\Core\PriceList();
        $productsPrice->addToPriceList(new \OxidEsales\Eshop\Core\Price(15));
        $paymentCost = 3;
        $wrappingCost = 5;

        return array(
            array($productsPrice, 0, 0, 0, 15),
            array($productsPrice, $paymentCost, $wrappingCost, 1, 24),
            array($productsPrice, -1 * $paymentCost, $wrappingCost, 9.45, 29.45),
        );
    }

    /**
     * Test case for oePayPalUser::getSumOfCostOfAllItemsPayPalBasket()
     *
     * @dataProvider getSumOfCostOfAllItemsPayPalBasketDataProvider
     */
    public function testGetSumOfCostOfAllItemsPayPalBasket($productsPrice, $paymentCost, $wrappingCost, $tsProtectionCost, $result)
    {
        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getProductsPrice', 'getPayPalPaymentCosts', 'getPayPalWrappingCosts', 'getPayPalTsProtectionCosts'));
        $basket->expects($this->once())->method('getProductsPrice')->will($this->returnValue($productsPrice));
        $basket->expects($this->once())->method('getPayPalPaymentCosts')->will($this->returnValue($paymentCost));
        $basket->expects($this->once())->method('getPayPalWrappingCosts')->will($this->returnValue($wrappingCost));
        $basket->expects($this->once())->method('getPayPalTsProtectionCosts')->will($this->returnValue($tsProtectionCost));

        $this->assertEquals($result, $basket->getSumOfCostOfAllItemsPayPalBasket());
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function getPayPalBasketVatValueDataProvider()
    {
        return array(
            array(array(1 => 13.32, 12 => 1.69), 1, 2, 3, 0.37, 21.38),
            array(array(0 => 0), 0, 0, 0, 0, 0),
            array(array(5 => 3.45), 1, 2, 3, 0.1, 9.55),
            array(array(5 => 3.45), 1, 0, 0, 0, 4.45),
            array(array(5 => 3.45), 0, 2, 0, 1, 6.45),
            array(array(5 => 3.45), 0, 0, 3, 0.99, 7.44),
            array(array(), 0, 0, 0, 0, 0),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Basket::getPayPalBasketVatValue()
     *
     * @dataProvider getPayPalBasketVatValueDataProvider
     */
    public function testGetPayPalBasketVatValue($productsVat, $wrappingVat, $giftCardVat, $payCostVat, $tsProtectionVat, $basketVat)
    {
        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getProductVats', 'getPayPalWrappingVat', 'getPayPalGiftCardVat', 'getPayPalPayCostVat', 'getPayPalTsProtectionCostVat'));
        $basket->expects($this->once())->method('getProductVats')->will($this->returnValue($productsVat));
        $basket->expects($this->once())->method('getPayPalWrappingVat')->will($this->returnValue($wrappingVat));
        $basket->expects($this->once())->method('getPayPalGiftCardVat')->will($this->returnValue($giftCardVat));
        $basket->expects($this->once())->method('getPayPalPayCostVat')->will($this->returnValue($payCostVat));
        $basket->expects($this->once())->method('getPayPalTsProtectionCostVat')->will($this->returnValue($tsProtectionVat));

        // Rounding because of PHPunit bug: Failed asserting that <double:21.01> matches expected <double:21.01>.
        $this->assertEquals($basketVat, round($basket->getPayPalBasketVatValue(), 2), 'Basket VAT do not match SUM of products VAT.');
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function getPayPalProductVatDataProvider()
    {
        return array(
            array(array(1 => 13.32, 12 => 1.69), 1426 => 15.01),
            array(array(0, 0), 0),
            array(array(5 => 3.45), 3.45),
            array(array(), 0),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Basket::testGetPayPalProductVat()
     *
     * @dataProvider getPayPalProductVatDataProvider
     */
    public function testGetPayPalProductVat($productsVat, $basketVat)
    {
        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getProductVats'));
        $basket->expects($this->once())->method('getProductVats')->will($this->returnValue($productsVat));

        $this->assertEquals($basketVat, $basket->getPayPalBasketVatValue(), 'Products VAT SUM is different than expected.');
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function getPayPalAdditionalCostsVatDataProvider()
    {
        return array(
            array(10, 10),
            array(0, 0),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Basket::GetPayPalWrappingVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalWrappingVat($costVat, $costVatExpected)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getVatValue'));
        $price->expects($this->any())->method('getVatValue')->will($this->returnValue($costVat));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxwrapping'))->will($this->returnValue($price));

        $this->assertEquals($costVatExpected, $basket->getPayPalWrappingVat(), 'Wrapping VAT SUM is different than expected.');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Basket::GetPayPalGiftCardVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalGiftCardVat($costVat, $costVatExpected)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getVatValue'));
        $price->expects($this->any())->method('getVatValue')->will($this->returnValue($costVat));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxgiftcard'))->will($this->returnValue($price));

        $this->assertEquals($costVatExpected, $basket->getPayPalGiftCardVat(), 'GiftCard VAT SUM is different than expected.');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Basket::GetPayPalPayCostVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalPayCostVat($costVat, $costVatExpected)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getVatValue'));
        $price->expects($this->any())->method('getVatValue')->will($this->returnValue($costVat));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxpayment'))->will($this->returnValue($price));

        $this->assertEquals($costVatExpected, $basket->getPayPalPayCostVat(), 'PayCost VAT SUM is different than expected.');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Basket::getPayPalTsProtectionCostVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalTsProtectionCostVat($costVat, $costVatExpected)
    {
        $price = $this->getMock(\OxidEsales\Eshop\Core\Price::class, array('getVatValue'));
        $price->expects($this->any())->method('getVatValue')->will($this->returnValue($costVat));

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getCosts'));
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxtsprotection'))->will($this->returnValue($price));

        $this->assertEquals($costVatExpected, $basket->getPayPalTsProtectionCostVat(), 'Trusted shops VAT SUM is different than expected.');
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testIsFractionQuantityItemsPresentWhenFractionQuantityArticlePresent()
    {
        $article = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('getAmount'));
        $article->expects($this->any())->method('getAmount')->will($this->returnValue(5.6));

        /** @var \OxidEsales\PayPalModule\Model\Basket $basket */
        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getContents'));
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array($article)));

        $this->assertTrue($basket->isFractionQuantityItemsPresent());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPalWhenNoFractionQuantityArticlesArePresent()
    {
        $article = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('getAmount'));
        $article->expects($this->any())->method('getAmount')->will($this->returnValue(5));

        /** @var \OxidEsales\PayPalModule\Model\Basket $basket */
        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getContents'));
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array($article)));

        $this->assertFalse($basket->isFractionQuantityItemsPresent());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPalWhenBasketIsEmpty()
    {
        /** @var \OxidEsales\PayPalModule\Model\Basket $basket */
        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getContents'));
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array()));

        $this->assertFalse($basket->isFractionQuantityItemsPresent());
    }
}
