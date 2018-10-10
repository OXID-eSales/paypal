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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

use OxidEsales\Eshop\Application\Model\Basket;

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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Article::class);
        $mockBuilder->setMethods(['isVirtualPayPalArticle']);
        $product1 = $mockBuilder->getMock();
        $product1->expects($this->any())->method('isVirtualPayPalArticle')->will($this->returnValue(true));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Article::class);
        $mockBuilder->setMethods(['isVirtualPayPalArticle']);
        $product2 = $mockBuilder->getMock();
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

        $mockBuilder = $this->getMockBuilder(Basket::class);
        $mockBuilder->setMethods(['getBasketArticles']);
        $basket = $mockBuilder->getMock();
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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getNettoPrice', 'getBruttoPrice']);
        $price = $mockBuilder->getMock();
        $price->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($wrappingPriceBrutto));
        $price->expects($this->any())->method('getNettoPrice')->will($this->returnValue($wrappingPriceNetto));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getCosts', 'isCalculationModeNetto']);
        $basket = $mockBuilder->getMock();
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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getNettoPrice', 'getBruttoPrice']);
        $price = $mockBuilder->getMock();
        $price->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($giftCardPriceBrutto));
        $price->expects($this->any())->method('getNettoPrice')->will($this->returnValue($giftCardPriceNetto));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getCosts', 'isCalculationModeNetto']);
        $basket = $mockBuilder->getMock();
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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getNettoPrice', 'getBruttoPrice']);
        $price = $mockBuilder->getMock();
        $price->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($paymentCostsPriceBrutto));
        $price->expects($this->any())->method('getNettoPrice')->will($this->returnValue($paymentCostsPriceNetto));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getCosts', 'isCalculationModeNetto']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxpayment'))->will($this->returnValue($price));
        $basket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($calculationModeNetto));

        $this->assertEquals($paymentPrice, $basket->getPayPalPaymentCosts());
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function getDiscountSumPayPalBasketDataProvider()
    {
        $basketDiscount1 = oxNew(\OxidEsales\Eshop\Core\Price::class);
        $basketDiscount1->setPrice(2);

        $basketDiscount2 = oxNew(\OxidEsales\Eshop\Core\Price::class);
        $basketDiscount2->setPrice(4);

        // vouchers
        $voucher = oxNew(\OxidEsales\Eshop\Application\Model\Voucher::class);

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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getTotalDiscount', 'getPaymentCosts', 'getVouchers']);
        $basket = $mockBuilder->getMock();
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
        $productsPrice = oxNew(\OxidEsales\Eshop\Core\PriceList::class);
        $productsPrice->addToPriceList(oxNew(\OxidEsales\Eshop\Core\Price::class, 15));
        $paymentCost = 3;
        $wrappingCost = 5;

        return array(
            array($productsPrice, 0, 0, 15),
            array($productsPrice, $paymentCost, $wrappingCost, 23),
            array($productsPrice, -1 * $paymentCost, $wrappingCost, 20),
        );
    }

    /**
     * Test case for oePayPalUser::getSumOfCostOfAllItemsPayPalBasket()
     *
     * @dataProvider getSumOfCostOfAllItemsPayPalBasketDataProvider
     */
    public function testGetSumOfCostOfAllItemsPayPalBasket($productsPrice, $paymentCost, $wrappingCost, $result)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getProductsPrice', 'getPayPalPaymentCosts', 'getPayPalWrappingCosts']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getProductsPrice')->will($this->returnValue($productsPrice));
        $basket->expects($this->once())->method('getPayPalPaymentCosts')->will($this->returnValue($paymentCost));
        $basket->expects($this->once())->method('getPayPalWrappingCosts')->will($this->returnValue($wrappingCost));

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
            array(array(1 => 13.32, 12 => 1.69), 1, 2, 3, 21.01),
            array(array(0 => 0), 0, 0, 0, 0),
            array(array(5 => 3.45), 1, 2, 3, 9.45),
            array(array(5 => 3.45), 1, 0, 0, 4.45),
            array(array(5 => 3.45), 0, 2, 0, 5.45),
            array(array(5 => 3.45), 0, 0, 3, 6.45),
            array(array(), 0, 0, 0, 0),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Basket::getPayPalBasketVatValue()
     *
     * @dataProvider getPayPalBasketVatValueDataProvider
     */
    public function testGetPayPalBasketVatValue($productsVat, $wrappingVat, $giftCardVat, $payCostVat, $basketVat)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getProductVats', 'getPayPalWrappingVat', 'getPayPalGiftCardVat', 'getPayPalPayCostVat']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getProductVats')->will($this->returnValue($productsVat));
        $basket->expects($this->once())->method('getPayPalWrappingVat')->will($this->returnValue($wrappingVat));
        $basket->expects($this->once())->method('getPayPalGiftCardVat')->will($this->returnValue($giftCardVat));
        $basket->expects($this->once())->method('getPayPalPayCostVat')->will($this->returnValue($payCostVat));

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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getProductVats']);
        $basket = $mockBuilder->getMock();
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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getVatValue']);
        $price = $mockBuilder->getMock();
        $price->expects($this->any())->method('getVatValue')->will($this->returnValue($costVat));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getCosts']);
        $basket = $mockBuilder->getMock();
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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getVatValue']);
        $price = $mockBuilder->getMock();
        $price->expects($this->any())->method('getVatValue')->will($this->returnValue($costVat));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getCosts']);
        $basket = $mockBuilder->getMock();
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
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getVatValue']);
        $price = $mockBuilder->getMock();
        $price->expects($this->any())->method('getVatValue')->will($this->returnValue($costVat));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getCosts']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method('getCosts')->with($this->equalTo('oxpayment'))->will($this->returnValue($price));

        $this->assertEquals($costVatExpected, $basket->getPayPalPayCostVat(), 'PayCost VAT SUM is different than expected.');
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testIsFractionQuantityItemsPresentWhenFractionQuantityArticlePresent()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Article::class);
        $mockBuilder->setMethods(['getAmount']);
        $article = $mockBuilder->getMock();
        $article->expects($this->any())->method('getAmount')->will($this->returnValue(5.6));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getContents']);
        /** @var \OxidEsales\PayPalModule\Model\Basket $basket */
        $basket = $mockBuilder->getMock();
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array($article)));

        $this->assertTrue($basket->isFractionQuantityItemsPresent());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPalWhenNoFractionQuantityArticlesArePresent()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Article::class);
        $mockBuilder->setMethods(['getAmount']);
        $article = $mockBuilder->getMock();
        $article->expects($this->any())->method('getAmount')->will($this->returnValue(5));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getContents']);
        /** @var \OxidEsales\PayPalModule\Model\Basket $basket */
        $basket = $mockBuilder->getMock();
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array($article)));

        $this->assertFalse($basket->isFractionQuantityItemsPresent());
    }

    /**
     * Test case for ViewConfig::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPalWhenBasketIsEmpty()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Basket::class);
        $mockBuilder->setMethods(['getContents']);
        /** @var \OxidEsales\PayPalModule\Model\Basket $basket */
        $basket  = $mockBuilder->getMock();
        $basket->expects($this->any())->method('getContents')->will($this->returnValue(array()));

        $this->assertFalse($basket->isFractionQuantityItemsPresent());
    }
}
