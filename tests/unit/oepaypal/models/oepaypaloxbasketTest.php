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
 * @copyright (C) OXID eSales AG 2003-2014
 */

if (!class_exists('oePayPalOxBasket_parent')) {
    class oePayPalOxBasket_parent extends oxBasket
    {
    }
}

/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalOxBasketTest extends OxidTestCase
{
    /**
     * Test data provider
     *
     * @return array
     */
    public function isVirtualPayPalBasketDataProvider()
    {
        $oProduct1 = $this->getMock('oxArticle', array('isVirtualPayPalArticle'));
        $oProduct1->expects($this->any())->method('isVirtualPayPalArticle')->will($this->returnValue(true));

        $oProduct2 = $this->getMock('oxArticle', array('isVirtualPayPalArticle'));
        $oProduct2->expects($this->any())->method('isVirtualPayPalArticle')->will($this->returnValue(false));

        return array(
            array($oProduct1, $oProduct2, false),
            array($oProduct1, $oProduct1, true),
        );
    }

    /**
     * Test case for oePayPalUser::isVirtualPayPalArticle()
     *
     * @dataProvider isVirtualPayPalBasketDataProvider
     */
    public function testIsVirtualPayPalBasket($oProduct1, $oProduct2, $blResult)
    {
        $aProducts = array($oProduct1, $oProduct2);

        $oBasket = $this->getMock('oePayPalOxBasket', array('getBasketArticles'));
        $oBasket->expects($this->once())->method('getBasketArticles')->will($this->returnValue($aProducts));

        $this->assertEquals($blResult, $oBasket->isVirtualPayPalBasket());
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
    public function testGetPayPalWrappingCosts($blCalculationModeNetto, $dWrappingPriceBrutto, $dWrappingPriceNetto, $dWrappingPriceExpect)
    {
        $oPrice = $this->getMock('oxPrice', array('getNettoPrice', 'getBruttoPrice'));
        $oPrice->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($dWrappingPriceBrutto));
        $oPrice->expects($this->any())->method('getNettoPrice')->will($this->returnValue($dWrappingPriceNetto));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts', 'isCalculationModeNetto'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxwrapping'))->will($this->returnValue($oPrice));
        $oBasket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($blCalculationModeNetto));

        $this->assertEquals($dWrappingPriceExpect, $oBasket->getPayPalWrappingCosts());
    }

    /**
     * Test case for oePayPalUser::getPayPalGiftCardCosts()
     *
     * @dataProvider getPayPalAdditionalCostsDataProvider
     */
    public function testGetPayPalGiftCardCosts($blCalculationModeNetto, $dGiftCardPriceBrutto, $dGiftCardPriceNetto, $dGiftCardPrice)
    {
        $oPrice = $this->getMock('oxPrice', array('getNettoPrice', 'getBruttoPrice'));
        $oPrice->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($dGiftCardPriceBrutto));
        $oPrice->expects($this->any())->method('getNettoPrice')->will($this->returnValue($dGiftCardPriceNetto));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts', 'isCalculationModeNetto'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxgiftcard'))->will($this->returnValue($oPrice));
        $oBasket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($blCalculationModeNetto));

        $this->assertEquals($dGiftCardPrice, $oBasket->getPayPalGiftCardCosts());
    }

    /**
     * Test case for oePayPalUser::getPayPalPaymentCosts()
     *
     * @dataProvider getPayPalAdditionalCostsDataProvider
     */
    public function testGetPayPalPaymentCosts($blCalculationModeNetto, $dPaymentCostsPriceBrutto, $dPaymentCostsPriceNetto, $dPaymentPrice)
    {
        $oPrice = $this->getMock('oxPrice', array('getNettoPrice', 'getBruttoPrice'));
        $oPrice->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($dPaymentCostsPriceBrutto));
        $oPrice->expects($this->any())->method('getNettoPrice')->will($this->returnValue($dPaymentCostsPriceNetto));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts', 'isCalculationModeNetto'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxpayment'))->will($this->returnValue($oPrice));
        $oBasket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($blCalculationModeNetto));

        $this->assertEquals($dPaymentPrice, $oBasket->getPayPalPaymentCosts());
    }

    /**
     * Test case for oePayPalUser::getPayPalTsProtectionCosts()
     *
     * @dataProvider getPayPalAdditionalCostsDataProvider
     */
    public function testGetPayPalTsProtectionCosts($blCalculationModeNetto, $dPaymentCostsPriceBrutto, $dPaymentCostsPriceNetto, $dPaymentPrice)
    {
        $oPrice = $this->getMock('oxPrice', array('getNettoPrice', 'getBruttoPrice'));
        $oPrice->expects($this->any())->method('getBruttoPrice')->will($this->returnValue($dPaymentCostsPriceBrutto));
        $oPrice->expects($this->any())->method('getNettoPrice')->will($this->returnValue($dPaymentCostsPriceNetto));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts', 'isCalculationModeNetto'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxtsprotection'))->will($this->returnValue($oPrice));
        $oBasket->expects($this->once())->method('isCalculationModeNetto')->will($this->returnValue($blCalculationModeNetto));

        $this->assertEquals($dPaymentPrice, $oBasket->getPayPalTsProtectionCosts());
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function getDiscountSumPayPalBasketDataProvider()
    {
        $oBasketDiscount1 = new oxPrice();
        $oBasketDiscount1->setPrice(2);

        $oBasketDiscount2 = new oxPrice();
        $oBasketDiscount2->setPrice(4);

        // vouchers
        $oVoucher = new oxVoucher();

        $VoucherDiscount1 = $oVoucher->getSimpleVoucher();
        $VoucherDiscount1->dVoucherdiscount = 6;

        $VoucherDiscount2 = $oVoucher->getSimpleVoucher();
        $VoucherDiscount2->dVoucherdiscount = 8;

        $dPaymentCost1 = 7;
        $dPaymentCost2 = -7;

        return array(
            array(0, array(), 0, 0),
            array(0, array(), $dPaymentCost1, 0),
            array($oBasketDiscount1, array($VoucherDiscount1, $VoucherDiscount2), $dPaymentCost1, 16),
            array($oBasketDiscount2, array($VoucherDiscount1, $VoucherDiscount2), $dPaymentCost2, 25),
        );
    }

    /**
     * Test case for oePayPalUser::getDiscountSumPayPalBasket()
     *
     * @dataProvider getDiscountSumPayPalBasketDataProvider
     */
    public function testGetDiscountSumPayPalBasket($oDiscount, $aVouchers, $dPaymentCost, $dResult)
    {
        $oBasket = $this->getMock('oePayPalOxBasket', array('getTotalDiscount', 'getPaymentCosts', 'getVouchers'));
        $oBasket->expects($this->once())->method('getTotalDiscount')->will($this->returnValue($oDiscount));
        $oBasket->expects($this->once())->method('getVouchers')->will($this->returnValue($aVouchers));
        $oBasket->expects($this->once())->method('getPaymentCosts')->will($this->returnValue($dPaymentCost));

        $this->assertEquals($dResult, $oBasket->getDiscountSumPayPalBasket());
    }


    /**
     * Test data provider
     *
     * @return array
     */
    public function getSumOfCostOfAllItemsPayPalBasketDataProvider()
    {
        // discounts
        $oProductsPrice = new oxPriceList();
        $oProductsPrice->addToPriceList(new oxPrice(15));
        $dPaymentCost = 3;
        $dWrappingCost = 5;

        return array(
            array($oProductsPrice, 0, 0, 0, 15),
            array($oProductsPrice, $dPaymentCost, $dWrappingCost, 1, 24),
            array($oProductsPrice, -1 * $dPaymentCost, $dWrappingCost, 9.45, 29.45),
        );
    }

    /**
     * Test case for oePayPalUser::getSumOfCostOfAllItemsPayPalBasket()
     *
     * @dataProvider getSumOfCostOfAllItemsPayPalBasketDataProvider
     */
    public function testGetSumOfCostOfAllItemsPayPalBasket($oProductsPrice, $dPaymentCost, $dWrappingCost, $dTsProtectionCost, $dResult)
    {
        $oBasket = $this->getMock('oePayPalOxBasket', array('getProductsPrice', 'getPayPalPaymentCosts', 'getPayPalWrappingCosts', 'getPayPalTsProtectionCosts'));
        $oBasket->expects($this->once())->method('getProductsPrice')->will($this->returnValue($oProductsPrice));
        $oBasket->expects($this->once())->method('getPayPalPaymentCosts')->will($this->returnValue($dPaymentCost));
        $oBasket->expects($this->once())->method('getPayPalWrappingCosts')->will($this->returnValue($dWrappingCost));
        $oBasket->expects($this->once())->method('getPayPalTsProtectionCosts')->will($this->returnValue($dTsProtectionCost));

        $this->assertEquals($dResult, $oBasket->getSumOfCostOfAllItemsPayPalBasket());
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
     * Test case for oePayPalOxBasket::getPayPalBasketVatValue()
     *
     * @dataProvider getPayPalBasketVatValueDataProvider
     */
    public function testGetPayPalBasketVatValue($aProductsVat, $flWrappingVat, $flGiftCardVat, $flPayCostVat, $flTsProtectionVat, $flBasketVat)
    {
        $oBasket = $this->getMock('oePayPalOxBasket', array('getProductVats', 'getPayPalWrappingVat', 'getPayPalGiftCardVat', 'getPayPalPayCostVat', 'getPayPalTsProtectionCostVat'));
        $oBasket->expects($this->once())->method('getProductVats')->will($this->returnValue($aProductsVat));
        $oBasket->expects($this->once())->method('getPayPalWrappingVat')->will($this->returnValue($flWrappingVat));
        $oBasket->expects($this->once())->method('getPayPalGiftCardVat')->will($this->returnValue($flGiftCardVat));
        $oBasket->expects($this->once())->method('getPayPalPayCostVat')->will($this->returnValue($flPayCostVat));
        $oBasket->expects($this->once())->method('getPayPalTsProtectionCostVat')->will($this->returnValue($flTsProtectionVat));

        // Rounding because of PHPunit bug: Failed asserting that <double:21.01> matches expected <double:21.01>.
        $this->assertEquals($flBasketVat, round($oBasket->getPayPalBasketVatValue(), 2), 'Basket VAT do not match SUM of products VAT.');
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
     * Test case for oePayPalOxBasket::testGetPayPalProductVat()
     *
     * @dataProvider getPayPalProductVatDataProvider
     */
    public function testGetPayPalProductVat($aProductsVat, $flBasketVat)
    {
        $oBasket = $this->getMock('oePayPalOxBasket', array('getProductVats'));
        $oBasket->expects($this->once())->method('getProductVats')->will($this->returnValue($aProductsVat));

        $this->assertEquals($flBasketVat, $oBasket->getPayPalBasketVatValue(), 'Products VAT SUM is different than expected.');
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
     * Test case for oePayPalOxBasket::GetPayPalWrappingVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalWrappingVat($dCostVat, $dCostVatExpected)
    {
        $oPrice = $this->getMock('oxPrice', array('getVatValue'));
        $oPrice->expects($this->any())->method('getVatValue')->will($this->returnValue($dCostVat));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxwrapping'))->will($this->returnValue($oPrice));

        $this->assertEquals($dCostVatExpected, $oBasket->getPayPalWrappingVat(), 'Wrapping VAT SUM is different than expected.');
    }

    /**
     * Test case for oePayPalOxBasket::GetPayPalGiftCardVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalGiftCardVat($dCostVat, $dCostVatExpected)
    {
        $oPrice = $this->getMock('oxPrice', array('getVatValue'));
        $oPrice->expects($this->any())->method('getVatValue')->will($this->returnValue($dCostVat));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxgiftcard'))->will($this->returnValue($oPrice));

        $this->assertEquals($dCostVatExpected, $oBasket->getPayPalGiftCardVat(), 'GiftCard VAT SUM is different than expected.');
    }

    /**
     * Test case for oePayPalOxBasket::GetPayPalPayCostVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalPayCostVat($dCostVat, $dCostVatExpected)
    {
        $oPrice = $this->getMock('oxPrice', array('getVatValue'));
        $oPrice->expects($this->any())->method('getVatValue')->will($this->returnValue($dCostVat));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxpayment'))->will($this->returnValue($oPrice));

        $this->assertEquals($dCostVatExpected, $oBasket->getPayPalPayCostVat(), 'PayCost VAT SUM is different than expected.');
    }

    /**
     * Test case for oePayPalOxBasket::getPayPalTsProtectionCostVat()
     *
     * @dataProvider getPayPalAdditionalCostsVatDataProvider
     */
    public function testGetPayPalTsProtectionCostVat($dCostVat, $dCostVatExpected)
    {
        $oPrice = $this->getMock('oxPrice', array('getVatValue'));
        $oPrice->expects($this->any())->method('getVatValue')->will($this->returnValue($dCostVat));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getCosts'));
        $oBasket->expects($this->once())->method('getCosts')->with($this->equalTo('oxtsprotection'))->will($this->returnValue($oPrice));

        $this->assertEquals($dCostVatExpected, $oBasket->getPayPalTsProtectionCostVat(), 'Trusted shops VAT SUM is different than expected.');
    }

    /**
     * Test case for oePayPalOxViewConfig::sendOrderInfoToPayPal()
     *
     * @return null
     */
    public function testIsFractionQuantityItemsPresentWhenFractionQuantityArticlePresent()
    {
        $oArticle = $this->getMock('oxArticle', array('getAmount'));
        $oArticle->expects($this->any())->method('getAmount')->will($this->returnValue(5.6));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getContents'));
        $oBasket->expects($this->any())->method('getContents')->will($this->returnValue(array($oArticle)));

        /** @var oePayPalOxBasket $oBasket */
        $this->assertTrue($oBasket->isFractionQuantityItemsPresent());
    }

    /**
     * Test case for oePayPalOxViewConfig::sendOrderInfoToPayPal()
     *
     * @return null
     */
    public function testSendOrderInfoToPayPalWhenNoFractionQuantityArticlesArePresent()
    {
        $oArticle = $this->getMock('oxArticle', array('getAmount'));
        $oArticle->expects($this->any())->method('getAmount')->will($this->returnValue(5));

        $oBasket = $this->getMock('oePayPalOxBasket', array('getContents'));
        $oBasket->expects($this->any())->method('getContents')->will($this->returnValue(array($oArticle)));

        /** @var oePayPalOxBasket $oBasket */
        $this->assertFalse($oBasket->isFractionQuantityItemsPresent());
    }

    /**
     * Test case for oePayPalOxViewConfig::sendOrderInfoToPayPal()
     *
     * @return null
     */
    public function testSendOrderInfoToPayPalWhenBasketIsEmpty()
    {
        $oBasket = $this->getMock('oePayPalOxBasket', array('getContents'));
        $oBasket->expects($this->any())->method('getContents')->will($this->returnValue(array()));

        /** @var oePayPalOxBasket $oBasket */
        $this->assertFalse($oBasket->isFractionQuantityItemsPresent());
    }
}
