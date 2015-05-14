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

class Unit_oePayPal_Controllers_oePayPalStandardDispatcherTest extends OxidTestCase
{
    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();
        // fix for state ID compatability between editions
        $sSqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) " .
                     "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        oxDb::getDb()->execute($sSqlState);
    }

    public function testSetGetPayPalCheckoutService()
    {
        $oPayPalCheckoutService = new oePayPalService();

        $oPayPalStandardDispatcher = new oePayPalStandardDispatcher();
        $oPayPalStandardDispatcher->setPayPalCheckoutService($oPayPalCheckoutService);

        $this->assertEquals($oPayPalCheckoutService, $oPayPalStandardDispatcher->getPayPalCheckoutService(), 'Getter should return what is set in setter.');
    }

    public function testGetPayPalCheckoutService()
    {
        $oPayPalStandardDispatcher = new oePayPalStandardDispatcher();

        $this->assertTrue($oPayPalStandardDispatcher->getPayPalCheckoutService() instanceof oePayPalService, 'Getter should create correct type of object.');
    }

    /**
     * Test case for oepaypalstandarddispatcher::getExpressCheckoutDetails()
     *
     * @return null
     */
    public function testGetExpressCheckoutDetails()
    {
        // preparing session, inputs etc.
        $this->getSession()->setVariable("oepaypal-token", "111");
        $aDetails["PAYERID"] = "111";
        $oDetails = new oePayPalResponseGetExpressCheckoutDetails();
        $oDetails->setData($aDetails);

        // preparing config
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("finalizeOrderOnPayPalSide"));
        $oPayPalConfig->expects($this->once())->method("finalizeOrderOnPayPalSide")->will($this->returnValue(true));

        // preparing service
        $oPayPalService = $this->getMock("oePayPalService", array("getExpressCheckoutDetails"));
        $oPayPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->returnValue($oDetails));

        // preparing
        $oDispatcher = $this->getMock("oepaypalstandarddispatcher", array("getPayPalCheckoutService", "getPayPalConfig"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("getPayPalConfig")->will($this->returnValue($oPayPalConfig));

        // testing
        $this->assertEquals("order?fnc=execute", $oDispatcher->getExpressCheckoutDetails());
        $this->assertEquals("111", $this->getSession()->getVariable("oepaypal-payerId"));
    }

    /**
     * Test case for oepaypalstandarddispatcher::getExpressCheckoutDetails()
     *
     * @return null
     */
    public function testGetExpressCheckoutDetailsError()
    {
        $oExcp = new oxException();

        // preparing config
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("finalizeOrderOnPayPalSide"));
        $oPayPalConfig->expects($this->never())->method("finalizeOrderOnPayPalSide");

        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("getExpressCheckoutDetails"));
        $oPayPalService->expects($this->once())->method("getExpressCheckoutDetails")->will($this->throwException($oExcp));

        // preparing utils view
        $oUtilsView = $this->getMock("oxUtilsView", array("addErrorToDisplay"));
        $oUtilsView->expects($this->once())->method("addErrorToDisplay")->with($this->equalTo($oExcp));

        // preparing
        $oDispatcher = $this->getMock("oepaypalstandarddispatcher", array("getPayPalCheckoutService", "getPayPalConfig", "_getUtilsView"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("_getUtilsView")->will($this->returnValue($oUtilsView));
        $oDispatcher->expects($this->never())->method("getPayPalConfig");

        // testing
        $this->assertEquals("payment", $oDispatcher->getExpressCheckoutDetails());
    }

    /**
     * Test case for oepaypaldispatcher::setExpressCheckout()
     * Main functionality
     *
     * @return null
     */
    public function testSetExpressCheckout_onSuccess()
    {
        $oResult = new oePayPalResponseSetExpressCheckout();
        $oResult->setData(array('TOKEN' => 'token'));

        $sUrl = "https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=token";

        //utils
        $oUtils = $this->getMock("oxUtils", array("redirect"));
        $oUtils->expects($this->once())->method("redirect")->with($this->equalTo($sUrl), $this->equalTo(false));

        //config
        $oPayPalConfig = $this->getMock("oePayPalConfig", array("getPayPalCommunicationUrl"));
        $oPayPalConfig->expects($this->once())->method("getPayPalCommunicationUrl")->with($this->equalTo($oResult->getToken()))->will($this->returnValue($sUrl));

        // preparing service
        $oPayPalService = $this->getMock("oePayPalService", array("setExpressCheckout", "setParameter"));
        $oPayPalService->expects($this->once())->method("setExpressCheckout")->will($this->returnValue($oResult));

        // preparing
        $oDispatcher = $this->getMock(
            "oepaypalstandarddispatcher", array("getPayPalCheckoutService"
                                                , '_getUtils', 'getPayPalConfig')
        );

        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->any())->method("getPayPalConfig")->will($this->returnValue($oPayPalConfig));
        $oDispatcher->expects($this->once())->method("_getUtils")->will($this->returnValue($oUtils));

        // testing
        $oDispatcher->setExpressCheckout();
    }

    /**
     * Test case for oepaypaldispatcher::setExpressCheckout()
     * On error
     *
     * @return null
     */
    public function testSetExpressCheckout_onError()
    {
        $oExcp = new oxException();

        $oPayPalConfig = $this->getMock("oePayPalConfig", array("getPaypalUrl"));
        $oPayPalConfig->expects($this->never())->method("getPaypalUrl");

        // preparing paypal service
        $oPayPalService = $this->getMock("oePayPalService", array("setExpressCheckout"));
        $oPayPalService->expects($this->once())->method("setExpressCheckout")->will($this->throwException($oExcp));

        // preparing utils view
        $oUtilsView = $this->getMock("oxUtilsView", array("addErrorToDisplay"));
        $oUtilsView->expects($this->once())->method("addErrorToDisplay")->with($this->equalTo($oExcp));

        // preparing
        $oDispatcher = $this->getMock("oepaypalstandarddispatcher", array("getPayPalCheckoutService", "_getUtilsView"));
        $oDispatcher->expects($this->once())->method("getPayPalCheckoutService")->will($this->returnValue($oPayPalService));
        $oDispatcher->expects($this->once())->method("_getUtilsView")->will($this->returnValue($oUtilsView));

        // testing
        $this->assertEquals("basket", $oDispatcher->setExpressCheckout());
    }
}