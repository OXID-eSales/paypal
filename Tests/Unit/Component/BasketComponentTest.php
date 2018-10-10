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

namespace OxidEsales\PayPalModule\Tests\Unit\Component;

use OxidEsales\Eshop\Application\Component\BasketComponent;

class BasketComponentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function providerActionExpressCheckoutFromDetailsPage()
    {
        $url = $this->getConfig()->getCurrentShopUrl(false) . 'index.php?cl=start';

        return array(
            // Article valid
            array(true, 1, 'oepaypalexpresscheckoutdispatcher?fnc=setExpressCheckout&displayCartInPayPal=0&oePayPalCancelURL=' . urlencode($url)),
            // Article not valid
            array(false, 1, null),
            // Article not not valid- amount is zero
            array(false, 0, 'start?')
        );
    }

    /**
     * Checks if action returns correct url when article is valid and is not valid
     *
     * @param $isArticleValid
     * @param $expectedUrl
     * @param $articleAmount
     *
     * @dataProvider providerActionExpressCheckoutFromDetailsPage
     */
    public function testActionExpressCheckoutFromDetailsPage($isArticleValid, $articleAmount, $expectedUrl)
    {
        $this->getConfig()->setConfigParam('blSeoMode', false);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutValidator::class);
        $mockBuilder->setMethods(['isArticleValid']);
        $validator = $mockBuilder->getMock();
        $validator->expects($this->any())->method('isArticleValid')->will($this->returnValue($isArticleValid));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem::class);
        $mockBuilder->setMethods(['getArticleAmount']);
        $currentItem = $mockBuilder->getMock();
        $currentItem->expects($this->any())->method('getArticleAmount')->will($this->returnValue($articleAmount));

        /** @var BasketComponent $cmpBasket */
        $mockBuilder = $this->getMockBuilder(BasketComponent::class);
        $mockBuilder->setMethods(['getValidator', 'getCurrentArticle']);
        $cmpBasket = $mockBuilder->getMock();
        $cmpBasket->expects($this->any())->method('getValidator')->will($this->returnValue($validator));
        $cmpBasket->expects($this->any())->method('getCurrentArticle')->will($this->returnValue($currentItem));

        $this->assertEquals($expectedUrl, $cmpBasket->actionExpressCheckoutFromDetailsPage());
    }

    /**
     * Checks if action returns correct url with cancel URL
     */
    public function testActionExpressCheckoutFromDetailsPage_CheckCancelUrl()
    {
        $this->getConfig()->setConfigParam('blSeoMode', false);

        $url = $this->getConfig()->getCurrentShopUrl(false) . 'index.php?cl=start';
        $cancelURL = urlencode($url);
        $expectedURL = 'oepaypalexpresscheckoutdispatcher?fnc=setExpressCheckout&displayCartInPayPal=0&oePayPalCancelURL=' . $cancelURL;

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutValidator::class);
        $mockBuilder->setMethods(['isArticleValid']);
        $validator = $mockBuilder->getMock();
        $validator->expects($this->any())->method('isArticleValid')->will($this->returnValue(true));

        /** @var BasketComponent $cmpBasket */
        $mockBuilder = $this->getMockBuilder(BasketComponent::class);
        $mockBuilder->setMethods(['getValidator']);
        $cmpBasket = $mockBuilder->getMock();
        $cmpBasket->expects($this->any())->method('getValidator')->will($this->returnValue($validator));

        $this->assertEquals($expectedURL, $cmpBasket->actionExpressCheckoutFromDetailsPage());
    }

    /**
     * Checks if action returns correct URL part
     */
    public function testActionNotAddToBasketAndGoToCheckout()
    {
        $this->getConfig()->setConfigParam('blSeoMode', false);

        $url = $this->getConfig()->getCurrentShopUrl(false) . 'index.php?cl=start';
        $cancelURL = urlencode($url);
        $expectedUrl = 'oepaypalexpresscheckoutdispatcher?fnc=setExpressCheckout&displayCartInPayPal=0&oePayPalCancelURL=' . $cancelURL;

        $cmpBasket = oxNew(BasketComponent::class);

        $this->assertEquals($expectedUrl, $cmpBasket->actionNotAddToBasketAndGoToCheckout());
    }
}
