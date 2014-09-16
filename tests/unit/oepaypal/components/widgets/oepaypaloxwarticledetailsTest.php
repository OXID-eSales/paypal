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
require_once realpath(".") . '/unit/test_config.inc.php';

if (!class_exists('oePayPalOxwArticleDetails_parent')) {
    class oePayPalOxwArticleDetails_parent extends Details
    {
    }
}

class Unit_oePayPal_Components_Widgets_oePayPalOxwArticleDetailsTest extends OxidTestCase
{

    protected function _createDetailsMock($oArticleInfo, $blShowECSPopUp = 0)
    {
        $oBasketComponent = $this->getMock('BasketComponent', array('getCurrentArticleInfo', 'shopECSPopUp'));
        $oBasketComponent->expects($this->any())->method('getCurrentArticleInfo')->will($this->returnValue($oArticleInfo));
        $oBasketComponent->expects($this->any())->method('shopECSPopUp')->will($this->returnValue($blShowECSPopUp));

        $oDetails = $this->getMock('oePayPalOxwArticleDetails', array('_oePayPalGetRequest', 'getComponent'));
        $oDetails->expects($this->any())->method('getComponent')->will($this->returnValue($oBasketComponent));

        return $oDetails;
    }

    public function providerOePayPalGetArticleAmount()
    {
        return array(
            // Given amount 5
            array(array('am' => 5), 5),
            // Not given any amount
            array('', 1)
        );
    }

    /**
     * Tests if returns correct amount
     *
     * @param $oArticleInfo
     * @param $blExpectedResult
     *
     * @dataProvider providerOePayPalGetArticleAmount
     */
    public function testOePayPalGetArticleAmount($oArticleInfo, $blExpectedResult)
    {
        $oDetails = $this->_createDetailsMock($oArticleInfo);

        $this->assertEquals($blExpectedResult, $oDetails->oePayPalGetArticleAmount());
    }

    public function providerOePayPalShowECSPopup()
    {
        return array(
            array(true, true),
            array(false, false),
            array('', false),
        );
    }

    /**
     * Tests if function gets parameter and returns correct result
     *
     * @param $blShowPopUp
     * @param $blExpectedResult
     *
     * @dataProvider providerOePayPalShowECSPopup
     */
    public function testOePayPalShowECSPopup($blShowPopUp, $blExpectedResult)
    {
        $oDetails = $this->_createDetailsMock(array(), $blShowPopUp);

        $this->assertEquals($blExpectedResult, $oDetails->oePayPalShowECSPopup());
    }

    public function providerOePayPalGetPersistentParam()
    {
        return array(
            array(array('persparam' => array('details' => 'aa')), 'aa'),
            array(array('persparam' => null), null),
        );
    }

    /**
     * Tests if function returns correct persistent param
     *
     * @param $oArticleInfo
     * @param $blExpectedResult
     *
     * @dataProvider providerOePayPalGetPersistentParam
     */
    public function testOePayPalGetPersistentParam($oArticleInfo, $blExpectedResult)
    {
        $oDetails = $this->_createDetailsMock($oArticleInfo);

        $this->assertEquals($blExpectedResult, $oDetails->oePayPalGetPersistentParam());
    }

    public function providerOePayPalGetSelection()
    {
        return array(
            array(array('sel' => array(1, 0)), array(1, 0)),
            array(array('sel' => null), null),
        );
    }

    /**
     * Tests if returns correct selection lists values
     *
     * @param $oArticleInfo
     * @param $blExpectedResult
     *
     * @dataProvider providerOePayPalGetSelection
     */
    public function testOePayPalGetSelection($oArticleInfo, $blExpectedResult)
    {
        $oDetails = $this->_createDetailsMock($oArticleInfo);

        $this->assertEquals($blExpectedResult, $oDetails->oePayPalGetSelection());
    }
}