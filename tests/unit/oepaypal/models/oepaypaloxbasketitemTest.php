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

if ( ! class_exists('oePayPalOxBasketItem_parent')) {
    class oePayPalOxBasketItem_parent extends oxBasketItem
    {
    }
}

/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalOxBasketItemTest extends OxidTestCase
{
    /**
     * Testing oePayPalOxBasketItem::getArticle - general usage
     *
     * @return array
     */
    public function testGetArticle_not_DoExpressCheckout()
    {
        $oArticle = $this->getMock( "oxArticle", array("isVisible", "isBuyable", "load") );
        $oArticle->expects( $this->once() )->method( "isVisible" )->will( $this->returnValue(true) );
        $oArticle->expects( $this->once() )->method( "isBuyable" )->will( $this->returnValue(true) );
        $oArticle->expects( $this->once() )->method( "load" )->will( $this->returnValue(true) );

        oxTestModules::addModuleObject( 'oxArticle', $oArticle );

        $oBasketItem = new oePayPalOxBasketItem();
        $oBasketItem->getArticle( true, "1126" );
    }

    /**
     * Testing oePayPalOxBasketItem::getArticle - when method "doExpressCheckoutPayment" is executed
     *
     * @return array
     */
    public function testGetArticle_doExpressCheckout()
    {
        // no checking of article validation should be performed
        $oArticle = $this->getMock( "oxArticle", array("isVisible", "isBuyable", "load") );
        $oArticle->expects( $this->never() )->method( "isVisible" );
        $oArticle->expects( $this->never() )->method( "isBuyable" );
        $oArticle->expects( $this->once() )->method( "load" )->will( $this->returnValue(true) );

        oxTestModules::addModuleObject( 'oxArticle', $oArticle );

        $oView = $this->getMock( "oxView", array("getFncName") );
        $oView->expects( $this->once() )->method( "getFncName" )->will( $this->returnValue("doExpressCheckoutPayment") );

        $oConfig = $this->getMock( "oxConfig", array("getActiveView") );
        $oConfig->expects( $this->once() )->method( "getActiveView" )->will( $this->returnValue($oView) );

        $oBasketItem = $this->getMock( "oePayPalOxBasketItem", array("getConfig") );
        $oBasketItem->expects( $this->once() )->method( "getConfig" )->will( $this->returnValue($oConfig) );

        $oBasketItem->getArticle( true, "1126" );
    }

}
