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

if (!class_exists('oePayPalOxUser_parent')) {
    class oePayPalOxUser_parent extends oxUser
    {
    }
}

/**
 * Testing oePayPalDoExpressCheckoutPaymentRequestBuilder class.
 */
class Unit_oePayPal_Models_PayPalRequest_oePayPalDoExpressCheckoutPaymentRequestBuilderTest extends OxidTestCase
{

    public function providerDoExpressCheckoutPayment()
    {
        $buttonSource = 'OXID_Cart_CommunityECS';

        if ('EE' == $this->getConfig()->getEdition()) {
            $buttonSource = 'OXID_Cart_EnterpriseECS';
        }
        if ('PE' == $this->getConfig()->getEdition()) {
            $buttonSource = 'OXID_Cart_ProfessionalECS';
        }

        $data = array(
            'standard_checkout' => array(oePayPalConfig::OEPAYPAL_ECS, $buttonSource),
            'shortcut'          => array(oePayPalConfig::OEPAYPAL_SHORTCUT, 'Oxid_Cart_ECS_Shortcut')
        );

        return $data;
    }

    /**
     * Test case for  oePayPalDoExpressCheckoutPaymentRequestBuilder::buildRequest.
     *
     * @dataProvider providerDoExpressCheckoutPayment
     *
     * @param int    $trigger      Mark if payment triggered by shortcut or by standard checkout.
     * @param string $buttonSource Expected partnercode/BUTTONSOURCE
     */
    public function testDoExpressCheckoutPayment($trigger, $buttonSource)
    {
        // preparing session, inputs etc.
        $aResult["PAYMENTINFO_0_TRANSACTIONID"] = "321";

        // preparing price
        $oPrice = $this->getMock("oxPrice", array("getBruttoPrice"));
        $oPrice->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $oBasket = $this->getMock("oePayPalOxBasket", array("getPrice"));
        $oBasket->expects($this->once())->method("getPrice")->will($this->returnValue($oPrice));

        // preparing session
        $oSession = $this->getMock("oxSession", array("getBasket"));
        $oSession->expects($this->any())->method("getBasket")->will($this->returnValue($oBasket));
        $oSession->setVariable("oepaypal-token", "111");
        $oSession->setVariable("oepaypal-payerId", "222");
        $oSession->setVariable(oePayPalConfig::OEPAYPAL_TRIGGER_NAME, $trigger);


        // preparing config
        $oPayPalConfig = new oePayPalConfig();

        // preparing order
        $oPayPalOrder = new oxOrder();
        $oPayPalOrder->oxorder__oxordernr = new oxField("123");

        $oUser = new oePayPalOxUser();
        $oUser->oxuser__oxfname = new oxField('firstname');
        $oUser->oxuser__oxlname = new oxField('lastname');
        $oUser->oxuser__oxstreet = new oxField('some street');
        $oUser->oxuser__oxstreetnr = new oxField('47');
        $oUser->oxuser__oxcity = new oxField('some city');
        $oUser->oxuser__oxzip = new oxField('zip');

        $sSubj = sprintf(oxRegistry::getLang()->translateString("OEPAYPAL_ORDER_CONF_SUBJECT"), $oPayPalOrder->oxorder__oxordernr->value);

        $oConfig = $this->getConfig();

        $aExpectedResult = array(
            'TOKEN'                              => '111',
            'PAYERID'                            => '222',
            'PAYMENTREQUEST_0_PAYMENTACTION'     => 'Sale',
            'PAYMENTREQUEST_0_AMT'               => 123,
            'PAYMENTREQUEST_0_CURRENCYCODE'      => "EUR",
            'PAYMENTREQUEST_0_NOTIFYURL'         => $this->getConfig()->getCurrentShopUrl() . "index.php?cl=oePayPalIPNHandler&fnc=handleRequest&shp=" . $oConfig->getShopId(),
            'PAYMENTREQUEST_0_DESC'              => $sSubj,
            'PAYMENTREQUEST_0_CUSTOM'            => $sSubj,
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'firstname lastname',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'some street 47',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'some city',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'zip',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => '',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => '',
        );

        $aExpectedResult['BUTTONSOURCE'] = $buttonSource;

        // testing
        $oBuilder = new oePayPalDoExpressCheckoutPaymentRequestBuilder();
        $oBuilder->setPayPalConfig($oPayPalConfig);
        $oBuilder->setSession($oSession);
        $oBuilder->setBasket($oBasket);
        $oBuilder->setOrder($oPayPalOrder);
        $oBuilder->setTransactionMode('Sale');
        $oBuilder->setUser($oUser);

        $oRequest = $oBuilder->buildRequest();
        $this->assertEquals($aExpectedResult, $oRequest->getData());
    }

    public function testAddAddressParams_SelectedAddressIdNotSet_TakeInfoFromUser()
    {
        $aExpectedParams = array(
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'FirstName LastName',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Street StreetNr',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'City',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'Zip',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => 'PhoneNum',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => null,
        );

        $aUserMethodValues = array(
            'getSelectedAddressId' => null,
        );
        $oUser = $this->_createStub('oxUser', $aUserMethodValues);
        $oUser->oxuser__oxusername = new oxField('test@test.com');
        $oUser->oxuser__oxfname = new oxField('FirstName');
        $oUser->oxuser__oxlname = new oxField('LastName');
        $oUser->oxuser__oxstreet = new oxField('Street');
        $oUser->oxuser__oxstreetnr = new oxField('StreetNr');
        $oUser->oxuser__oxcity = new oxField('City');
        $oUser->oxuser__oxzip = new oxField('Zip');
        $oUser->oxuser__oxfon = new oxField('PhoneNum');
        $oUser->oxuser__oxcity = new oxField('City');

        $oBuilder = new oePayPalDoExpressCheckoutPaymentRequestBuilder();
        $oBuilder->setUser($oUser);
        $oBuilder->addAddressParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getRequest()->getData());
    }

    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    protected function _assertArraysEqual($aExpected, $aResult)
    {
        $this->_assertArraysContains($aExpected, $aResult);
        $this->assertEquals(count($aExpected), count($aResult));
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    protected function _assertArraysContains($aExpected, $aResult)
    {
        foreach ($aExpected as $sKey => $sValue) {
            $this->assertArrayHasKey($sKey, $aResult, "Key not found: $sKey");
            $this->assertEquals($sValue, $aResult[$sKey], "Key '$sKey' value is not equal to '$sValue'");
        }
    }
}