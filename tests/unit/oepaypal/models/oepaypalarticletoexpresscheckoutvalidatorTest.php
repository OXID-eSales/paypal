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

require_once realpath(".") . '/unit/OxidTestCase.php';
if (!class_exists('oePayPalOxBasket_parent')) {
    class oePayPalOxBasket_parent extends oxBasket
    {
    }
}

class Unit_oePayPal_Models_oePayPalArticleToExpressCheckoutValidatorTest extends OxidTestCase
{

    public function providerSetGetItemToValidate()
    {
        $oItem = new oePayPalArticleToExpressCheckoutCurrentItem();

        return array(
            array($oItem),
            array(null)
        );
    }

    /**
     * Tests setBasket and getBasket, sets basket object and checks if it get correct
     *
     * @param $oItem
     *
     * @dataProvider providerSetGetItemToValidate
     */
    public function testSetGetItemToValidate($oItem)
    {
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutValidator();
        $oArticleToExpressCheckoutValidator->setItemToValidate($oItem);

        $this->assertEquals($oItem, $oArticleToExpressCheckoutValidator->getItemToValidate());
    }

    public function providerSetGetBasket()
    {
        $oOxBasket = new oePayPalOxBasket();

        return array(
            array($oOxBasket),
            array(null)
        );
    }

    /**
     * Tests setBasket and getBasket, sets basket object and checks if it get correct
     *
     * @param $oBasket
     *
     * @dataProvider providerSetGetBasket
     */
    public function testSetGetBasket($oBasket)
    {
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutValidator();
        $oArticleToExpressCheckoutValidator->setBasket($oBasket);

        $this->assertEquals($oBasket, $oArticleToExpressCheckoutValidator->getBasket());
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
     * @param $sBasketProductId
     * @param $aBasketSelectionList
     * @param $aBasketPersistentParam
     *
     * @dataProvider providerIsArticleValid_True
     */
    public function testIsArticleValid_True($sBasketProductId, $aBasketSelectionList, $aBasketPersistentParam)
    {
        $sProductId = 'ProductId';
        $aSelectionList = array('testable' => 'list');
        $aPersistentParam = array('testable' => 'persistent param');
        $iAmount = 1;

        $oBasket = $this->_createBasket($sBasketProductId, $aBasketSelectionList, $aBasketPersistentParam);

        $oArticleToExpressCheckoutCurrentItem = new oePayPalArticleToExpressCheckoutCurrentItem();
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutValidator();

        $oArticleToExpressCheckoutCurrentItem->setPersistParam($aPersistentParam);
        $oArticleToExpressCheckoutCurrentItem->setSelectList($aSelectionList);
        $oArticleToExpressCheckoutCurrentItem->setArticleId($sProductId);
        $oArticleToExpressCheckoutCurrentItem->setArticleAmount($iAmount);

        $oArticleToExpressCheckoutValidator->setBasket($oBasket);
        $oArticleToExpressCheckoutValidator->setItemToValidate($oArticleToExpressCheckoutCurrentItem);

        $this->assertTrue($oArticleToExpressCheckoutValidator->isArticleValid());
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
    public function testIsArticleValid_False($sProductId, $aSelectionList, $aPersistentParam, $iAmount)
    {
        $sBasketProductId = 'ProductId';
        $aBasketSelectionList = array('testable' => 'list');
        $aBasketPersistentParam = array('testable' => 'persistent param');

        $oBasket = $this->_createBasket($sBasketProductId, $aBasketSelectionList, $aBasketPersistentParam);

        $oArticleToExpressCheckoutCurrentItem = new oePayPalArticleToExpressCheckoutCurrentItem();
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutValidator();

        $oArticleToExpressCheckoutCurrentItem->setPersistParam($aPersistentParam);
        $oArticleToExpressCheckoutCurrentItem->setSelectList($aSelectionList);
        $oArticleToExpressCheckoutCurrentItem->setArticleId($sProductId);
        $oArticleToExpressCheckoutCurrentItem->setArticleAmount($iAmount);

        $oArticleToExpressCheckoutValidator->setBasket($oBasket);
        $oArticleToExpressCheckoutValidator->setItemToValidate($oArticleToExpressCheckoutCurrentItem);

        $this->assertFalse($oArticleToExpressCheckoutValidator->isArticleValid());
    }

    /**
     * Function creates mocked basket
     *
     * @param $sBasketProductId
     * @param $aBasketSelectionList
     * @param $aBasketPersistentParam
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createBasket($sBasketProductId, $aBasketSelectionList, $aBasketPersistentParam)
    {
        $aBasketItemsList = array();

        //if $sBasketProductId is null we say that $aBasketItemsList is empty array
        if (!is_null($sBasketProductId)) {
            $oBasketItem = $this->getMock('oxBasketItem', array('getProductId', 'getPersParams', 'getSelList'));
            $oBasketItem->expects($this->any())->method('getProductId')->will($this->returnValue($sBasketProductId));
            $oBasketItem->expects($this->any())->method('getSelList')->will($this->returnValue($aBasketSelectionList));
            $oBasketItem->expects($this->any())->method('getPersParams')->will($this->returnValue($aBasketPersistentParam));

            $aBasketItemsList = array(
                $sBasketProductId => $oBasketItem
            );
        }

        $oBasket = $this->getMock('oePayPalOxBasket', array('getContents'));
        $oBasket->expects($this->any())->method('getContents')->will($this->returnValue($aBasketItemsList));

        return $oBasket;
    }
}
