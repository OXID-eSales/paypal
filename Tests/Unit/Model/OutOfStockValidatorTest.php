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
 * Testing oePayPalBasketValidator class.
 */
class OutOfStockValidatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function providerSetGetBasket()
    {
        $oxBasket = new \OxidEsales\PayPalModule\Model\Basket();

        return array(
            array($oxBasket),
            array(null)
        );
    }

    /**
     * @param $basket
     *
     * @dataProvider providerSetGetBasket
     */
    public function testSetGetBasket($basket)
    {
        $basketValidator = new \OxidEsales\PayPalModule\Model\OutOfStockValidator();
        $basketValidator->setBasket($basket);

        $this->assertEquals($basket, $basketValidator->getBasket());
    }

    /**
     */
    public function testSetGetEmptyStockLevel()
    {
        $basketValidator = new \OxidEsales\PayPalModule\Model\OutOfStockValidator();
        $basketValidator->setEmptyStockLevel(10);

        $this->assertEquals(10, $basketValidator->getEmptyStockLevel());
    }


    /**
     * Data provider for testHasOutOfStockItems
     */
    public function providerHasOutOfStockItems()
    {
        return array(
            // Basket Article ID, Basket item amount, Stock Amount, Stock empty level, expected result
            array('ProductId5', 5, 5, 0, false),
            array('ProductId5', 5, 5, 1, true),
            array('ProductId5', 6, 5, 0, true),
            array('ProductId3', 2, 3, 2, true),
            array('ProductId2', 2, 2, 1, true),
            array('ProductId1', 2, 2, 0, false),
        );
    }

    /**
     * @dataProvider providerHasOutOfStockItems
     */
    public function testHasOutOfStockArticles($productId, $basketItemAmount, $stockAmount, $stockEmptyLevel, $expectedResult)
    {
        $basket = $this->createBasket($productId, $basketItemAmount, $stockAmount);

        $basketValidator = new \OxidEsales\PayPalModule\Model\OutOfStockValidator();

        $basketValidator->setBasket($basket);
        $basketValidator->setEmptyStockLevel($stockEmptyLevel);

        $this->assertEquals($expectedResult, $basketValidator->hasOutOfStockArticles());
    }


    /**
     * Function creates mocked basket
     *
     * @param $productId
     * @param $basketAmount
     * @param $stockAmount
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createBasket($productId, $basketAmount, $stockAmount)
    {
        $article = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('getStockAmount'));
        $article->expects($this->any())->method('getStockAmount')->will($this->returnValue($stockAmount));

        $basketItem = $this->getMock(\OxidEsales\Eshop\Application\Model\BasketItem::class, array('getProductId', 'getAmount', 'getArticle'));
        $basketItem->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $basketItem->expects($this->any())->method('getAmount')->will($this->returnValue($basketAmount));
        $basketItem->expects($this->any())->method('getArticle')->will($this->returnValue($article));

        $basketItemsList = array(
            $productId => $basketItem
        );

        $basket = $this->getMock(\OxidEsales\PayPalModule\Model\Basket::class, array('getContents'));
        $basket->expects($this->any())->method('getContents')->will($this->returnValue($basketItemsList));

        return $basket;
    }
}