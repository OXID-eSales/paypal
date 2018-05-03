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

class ArticleToExpressCheckoutCurrentItemTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tests setter and getter.
     */
    public function testSetGetArticleId()
    {
        $articleId = 'this is product id';
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem();
        $articleToExpressCheckoutValidator->setArticleId($articleId);

        $this->assertEquals($articleId, $articleToExpressCheckoutValidator->getArticleId());
    }

    /**
     * Tests setter and getter.
     */
    public function testSetGeSelectList()
    {
        $selectList = array('testable' => 'selection list');
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem();
        $articleToExpressCheckoutValidator->setSelectList($selectList);

        $this->assertEquals($selectList, $articleToExpressCheckoutValidator->getSelectList());
    }

    /**
     * Tests setter and getter.
     */
    public function testSetGetPersistParam()
    {
        $persistentParam = array('testable' => 'persistent param');
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem();
        $articleToExpressCheckoutValidator->setPersistParam($persistentParam);

        $this->assertEquals($persistentParam, $articleToExpressCheckoutValidator->getPersistParam());
    }

    /**
     * Tests setter and getter.
     */
    public function testSetGetArticleAmount()
    {
        $amount = 5;
        $articleToExpressCheckoutValidator = new \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem();
        $articleToExpressCheckoutValidator->setArticleAmount($amount);

        $this->assertEquals($amount, $articleToExpressCheckoutValidator->getArticleAmount());
    }
}
