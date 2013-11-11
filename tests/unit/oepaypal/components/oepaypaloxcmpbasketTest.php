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

require_once realpath( "." ).'/unit/OxidTestCase.php';

class Unit_oePayPal_Components_oepaypaloxcmpBasketTest extends OxidTestCase
{

    public function providerActionExpressCheckoutFromDetailsPage()
    {
        return array(
            // Article valid
            array( true, 'oePayPalExpressCheckoutDispatcher&fnc=setExpressCheckout&displayCartInPayPal=0&oePayPalCancelURL=', 1 ),
            // Article not valid
            array( false, 'start?&showECSPopup=1&ECSArticle=&displayCartInPayPal=0', 1 ),
            // Article not not valid- amount is zero
            array( false, 'start?', 0 )
        );
    }

    /**
     * Checks if action returns correct url when article is valid and is not valid
     * @param $blIsArticleValid
     * @param $sExpectedUrl
     * @param $iArticleAmount
     * @dataProvider providerActionExpressCheckoutFromDetailsPage
     */
    public function testActionExpressCheckoutFromDetailsPage( $blIsArticleValid, $sExpectedUrl, $iArticleAmount )
    {
        $oValidator = $this->getMock( 'oePayPalArticleToExpressCheckoutValidator', array( 'isArticleValid' ) );
        $oValidator->expects( $this->any() )->method( 'isArticleValid' )->will( $this->returnValue( $blIsArticleValid ) );

        $oCurrentItem = $this->getMock( 'oePayPalArticleToExpressCheckoutCurrentItem', array( 'getArticleAmount' ) );
        $oCurrentItem->expects( $this->any() )->method( 'getArticleAmount' )->will( $this->returnValue( $iArticleAmount ) );

        $oCmpBasket = $this->getMock( 'oePayPalOxcmp_Basket', array( '_getValidator', '_getCurrentArticle' ) );
        $oCmpBasket->expects( $this->any() )->method( '_getValidator' )->will( $this->returnValue( $oValidator ) );
        $oCmpBasket->expects( $this->any() )->method( '_getCurrentArticle' )->will( $this->returnValue( $oCurrentItem ) );

        $this->assertEquals( $sExpectedUrl, $oCmpBasket->actionExpressCheckoutFromDetailsPage() );
    }

    /**
     * Checks if action returns correct url with cancel URL
     */
    public function testActionExpressCheckoutFromDetailsPage_CheckCancelUrl()
    {
        $sURL = 'http://oxidesales.com/index.php?showECSPopup=1';
        $sCancelURL = urlencode( 'http://oxidesales.com/index.php?showECSPopup=0' );
        $sExpectedURL = 'oePayPalExpressCheckoutDispatcher&fnc=setExpressCheckout&displayCartInPayPal=0&oePayPalCancelURL=' . $sCancelURL;

        $oValidator = $this->getMock( 'oePayPalArticleToExpressCheckoutValidator', array( 'isArticleValid' ) );
        $oValidator->expects( $this->any() )->method( 'isArticleValid' )->will( $this->returnValue( true ) );

        $oRequest = $this->getMock( 'oePayPalRequest', array( 'getPostParameter' ) );
        $oRequest->expects( $this->any() )->method( 'getPostParameter' )->will( $this->returnValue( $sURL ) );

        $oCmpBasket = $this->getMock( 'oePayPalOxcmp_Basket', array( '_getRequest', '_getValidator' ) );
        $oCmpBasket->expects( $this->any() )->method( '_getRequest' )->will( $this->returnValue( $oRequest ) );
        $oCmpBasket->expects( $this->any() )->method( '_getValidator' )->will( $this->returnValue( $oValidator ) );

        $this->assertEquals( $sExpectedURL, $oCmpBasket->actionExpressCheckoutFromDetailsPage() );
    }

    /**
     * Checks if action returns correct URL part
     */
    public function testActionNotAddToBasketAndGoToCheckout()
    {
        $oCmpBasket = new oePayPalOxcmp_Basket();
        $sExpectedUrl = 'oePayPalExpressCheckoutDispatcher&fnc=setExpressCheckout&displayCartInPayPal=0&oePayPalCancelURL=';

        $this->assertEquals( $sExpectedUrl, $oCmpBasket->actionNotAddToBasketAndGoToCheckout() );
    }
}
