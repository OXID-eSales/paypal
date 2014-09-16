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

require_once realpath(".") . '/unit/OxidTestCase.php';

class Unit_oePayPal_Models_oePayPalArticleToExpressCheckoutCurrentItemTest extends OxidTestCase
{
    /**
     * Tests setter and getter.
     */
    public function testSetGetArticleId()
    {
        $sArticleId = 'this is product id';
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutCurrentItem();
        $oArticleToExpressCheckoutValidator->setArticleId($sArticleId);

        $this->assertEquals($sArticleId, $oArticleToExpressCheckoutValidator->getArticleId());
    }

    /**
     * Tests setter and getter.
     */
    public function testSetGeSelectList()
    {
        $aSelectList = array('testable' => 'selection list');
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutCurrentItem();
        $oArticleToExpressCheckoutValidator->setSelectList($aSelectList);

        $this->assertEquals($aSelectList, $oArticleToExpressCheckoutValidator->getSelectList());
    }

    /**
     * Tests setter and getter.
     */
    public function testSetGetPersistParam()
    {
        $aPersistentParam = array('testable' => 'persistent param');
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutCurrentItem();
        $oArticleToExpressCheckoutValidator->setPersistParam($aPersistentParam);

        $this->assertEquals($aPersistentParam, $oArticleToExpressCheckoutValidator->getPersistParam());
    }

    /**
     * Tests setter and getter.
     */
    public function testSetGetArticleAmount()
    {
        $iAmount = 5;
        $oArticleToExpressCheckoutValidator = new oePayPalArticleToExpressCheckoutCurrentItem();
        $oArticleToExpressCheckoutValidator->setArticleAmount($iAmount);

        $this->assertEquals($iAmount, $oArticleToExpressCheckoutValidator->getArticleAmount());
    }
}
