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
 * Testing oePayPalBasketValidator class.
 */
class Unit_oePayPal_Models_oePayPalOutOfStockValidatorTest extends OxidTestCase
{
    public function providerSetGetBasket()
    {
        $oOxBasket = new oePayPalOxBasket();

        return array(
            array($oOxBasket),
            array(null)
        );
    }

    /**
     * @param $oBasket
     *
     * @dataProvider providerSetGetBasket
     */
    public function testSetGetBasket($oBasket)
    {
        $oBasketValidator = new oePayPalOutOfStockValidator();
        $oBasketValidator->setBasket($oBasket);

        $this->assertEquals($oBasket, $oBasketValidator->getBasket());
    }

    /**
     */
    public function testSetGetEmptyStockLevel()
    {
        $oBasketValidator = new oePayPalOutOfStockValidator();
        $oBasketValidator->setEmptyStockLevel(10);

        $this->assertEquals(10, $oBasketValidator->getEmptyStockLevel());
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
    public function testHasOutOfStockArticles($sProductId, $iBasketItemAmount, $iStockAmount, $iStockEmptyLevel, $blExpectedResult)
    {
        $oBasket = $this->_createBasket($sProductId, $iBasketItemAmount, $iStockAmount);

        $oBasketValidator = new oePayPalOutOfStockValidator();

        $oBasketValidator->setBasket($oBasket);
        $oBasketValidator->setEmptyStockLevel($iStockEmptyLevel);

        $this->assertEquals($blExpectedResult, $oBasketValidator->hasOutOfStockArticles());
    }


    /**
     * Function creates mocked basket
     *
     * @param $sProductId
     * @param $iBasketAmount
     * @param $iStockAmount
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createBasket($sProductId, $iBasketAmount, $iStockAmount)
    {
        $oArticle = $this->getMock('oxArticle', array('getStockAmount'));
        $oArticle->expects($this->any())->method('getStockAmount')->will($this->returnValue($iStockAmount));

        $oBasketItem = $this->getMock('oxBasketItem', array('getProductId', 'getAmount', 'getArticle'));
        $oBasketItem->expects($this->any())->method('getProductId')->will($this->returnValue($sProductId));
        $oBasketItem->expects($this->any())->method('getAmount')->will($this->returnValue($iBasketAmount));
        $oBasketItem->expects($this->any())->method('getArticle')->will($this->returnValue($oArticle));

        $aBasketItemsList = array(
            $sProductId => $oBasketItem
        );

        $oBasket = $this->getMock('oePayPalOxBasket', array('getContents'));
        $oBasket->expects($this->any())->method('getContents')->will($this->returnValue($aBasketItemsList));

        return $oBasket;
    }
}