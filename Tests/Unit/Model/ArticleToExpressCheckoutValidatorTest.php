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

class ArticleToExpressCheckoutValidatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function providerSetGetItemToValidate()
    {
        $item = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem();

        return array(
            array($item),
            array(null)
        );
    }

    /**
     * Tests setBasket and getBasket, sets basket object and checks if it get correct
     *
     * @param $item
     *
     * @dataProvider providerSetGetItemToValidate
     */
    public function testSetGetItemToValidate($item)
    {
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutValidator();
        $articleToExpressCheckoutValidator->setItemToValidate($item);

        $this->assertEquals($item, $articleToExpressCheckoutValidator->getItemToValidate());
    }

    public function providerSetGetBasket()
    {
        $oxBasket = oxNew(Basket::class);

        return array(
            array($oxBasket),
            array(null)
        );
    }

    /**
     * Tests setBasket and getBasket, sets basket object and checks if it get correct
     *
     * @param $basket
     *
     * @dataProvider providerSetGetBasket
     */
    public function testSetGetBasket($basket)
    {
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutValidator();
        $articleToExpressCheckoutValidator->setBasket($basket);

        $this->assertEquals($basket, $articleToExpressCheckoutValidator->getBasket());
    }

    public function providerIsArticleValid_True()
    {
        return array(
            array(null, null, null),
            array('ProductId', array('testable' => 'list'), null),
            array('ProductId', null, null),
            array(null, null, 'persistent param')
        );
    }

    /**
     * Checks if item is same in given basket
     *
     * @param $basketProductId
     * @param $basketSelectionList
     * @param $basketPersistentParam
     *
     * @dataProvider providerIsArticleValid_True
     */
    public function testIsArticleValid_True($basketProductId, $basketSelectionList, $basketPersistentParam)
    {
        $productId = 'ProductId';
        $selectionList = array('testable' => 'list');
        $persistentParam = array('testable' => 'persistent param');
        $amount = 1;

        $basket = $this->createBasket($basketProductId, $basketSelectionList, $basketPersistentParam);

        $articleToExpressCheckoutCurrentItem = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem();
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutValidator();

        $articleToExpressCheckoutCurrentItem->setPersistParam($persistentParam);
        $articleToExpressCheckoutCurrentItem->setSelectList($selectionList);
        $articleToExpressCheckoutCurrentItem->setArticleId($productId);
        $articleToExpressCheckoutCurrentItem->setArticleAmount($amount);

        $articleToExpressCheckoutValidator->setBasket($basket);
        $articleToExpressCheckoutValidator->setItemToValidate($articleToExpressCheckoutCurrentItem);

        $this->assertTrue($articleToExpressCheckoutValidator->isArticleValid());
    }

    public function providerIsArticleValid_False()
    {
        return array(
            // Same article
            array('ProductId', array('testable' => 'list'), array('testable' => 'persistent param'), 1),
            // Article amount is 0
            array('ProductId', null, null, 0),
            array('ProductId', null, null, null)
        );
    }

    /**
     * Checks if item is same in given basket, if so, item is not valid
     *
     * @dataProvider providerIsArticleValid_False
     */
    public function testIsArticleValid_False($productId, $selectionList, $persistentParam, $amount)
    {
        $basketProductId = 'ProductId';
        $basketSelectionList = array('testable' => 'list');
        $basketPersistentParam = array('testable' => 'persistent param');

        $basket = $this->createBasket($basketProductId, $basketSelectionList, $basketPersistentParam);

        $articleToExpressCheckoutCurrentItem = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem();
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutValidator();

        $articleToExpressCheckoutCurrentItem->setPersistParam($persistentParam);
        $articleToExpressCheckoutCurrentItem->setSelectList($selectionList);
        $articleToExpressCheckoutCurrentItem->setArticleId($productId);
        $articleToExpressCheckoutCurrentItem->setArticleAmount($amount);

        $articleToExpressCheckoutValidator->setBasket($basket);
        $articleToExpressCheckoutValidator->setItemToValidate($articleToExpressCheckoutCurrentItem);

        $this->assertFalse($articleToExpressCheckoutValidator->isArticleValid());
    }

    /**
     * Function creates mocked basket
     *
     * @param $basketProductId
     * @param $basketSelectionList
     * @param $basketPersistentParam
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createBasket($basketProductId, $basketSelectionList, $basketPersistentParam)
    {
        $basketItemsList = array();

        //if $basketProductId is null we say that $basketItemsList is empty array
        if (!is_null($basketProductId)) {
            $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\BasketItem::class);
            $mockBuilder->setMethods(['getProductId', 'getPersParams', 'getSelList']);
            $basketItem = $mockBuilder->getMock();
            $basketItem->expects($this->any())->method('getProductId')->will($this->returnValue($basketProductId));
            $basketItem->expects($this->any())->method('getSelList')->will($this->returnValue($basketSelectionList));
            $basketItem->expects($this->any())->method('getPersParams')->will($this->returnValue($basketPersistentParam));

            $basketItemsList = array(
                $basketProductId => $basketItem
            );
        }

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(['getContents']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->any())->method('getContents')->will($this->returnValue($basketItemsList));

        return $basket;
    }
}
