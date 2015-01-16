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


if (!class_exists('oePayPalOxAddress_parent')) {
    class oePayPalOxAddress_parent extends oxAddress
    {
    }
}

/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalOxAddressTest extends OxidTestCase
{
    /**
     * Tear down the fixture.
     *
     */
    protected function tearDown()
    {
        $this->getSession()->setVariable('deladrid', null);
        $sDelete = 'TRUNCATE TABLE `oxaddress`';
        oxDb::getDb()->execute($sDelete);
    }

    /**
     * Set up
     */
    protected function setUp()
    {
        // fix for state ID compatability between editions
        $sSqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) " .
                     "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        oxDb::getDb()->execute($sSqlState);
    }

    /**
     * Prepare PayPal response data array
     *
     * @return array
     */
    protected function _getPayPalData()
    {
        $aPayPalData = array();
        $aPayPalData['PAYMENTREQUEST_0_SHIPTONAME'] = 'testName testSurname';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetName str. 12';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET2'] = 'testStreeName2 str. 123';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCity';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = 'US';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTATE'] = 'SS';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOZIP'] = 'testZip';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = 'testPhoneNum';

        /*        $oPayPalData = $this->getMock( 'oePayPalResponseGetExpressCheckoutDetails', array( 'getShipToName', 'getShipToStreet',
                    'getShipToStreet2', 'getShipToCity', 'getShipToCountryCode', 'getShipToState', 'getShipToZip', 'getShipToPhoneNumber' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToName' )->will( $this->returnValue( 'testName testSurname' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToStreet' )->will( $this->returnValue( 'testStreetName str. 12' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToStreet2' )->will( $this->returnValue( 'testStreeName2 str. 123' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToCity' )->will( $this->returnValue( 'testCity' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToCountryCode' )->will( $this->returnValue( 'US' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToState' )->will( $this->returnValue( 'SS' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToZip' )->will( $this->returnValue( 'testZip' ) );
                $oPayPalData->expects( $this->any() )->method( 'getShipToPhoneNumber' )->will( $this->returnValue( 'testPhoneNum' ) );*/        return $aPayPalData;
    }


    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Creating new address
     *
     * @return null
     */
    public function testCreatePayPalAddress()
    {
        $aPayPalData = $this->_getPayPalData();
        $oExpressCheckoutResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oExpressCheckoutResponse->setData($aPayPalData);

        $oPayPalOxAddress = new oePayPalOxAddress();
        $oPayPalOxAddress->createPayPalAddress($oExpressCheckoutResponse, 'testUserId');
        //$sAddressId = $oPayPalOxAddress->getId();
        $sAddressId = $this->getSession()->getVar('deladrid');

        $oAddress = new oxAddress();
        $oAddress->load($sAddressId);

        $this->assertEquals('testUserId', $oAddress->oxaddress__oxuserid->value);
        $this->assertEquals('testName', $oAddress->oxaddress__oxfname->value);
        $this->assertEquals('testSurname', $oAddress->oxaddress__oxlname->value);
        $this->assertEquals('testStreetName str.', $oAddress->oxaddress__oxstreet->value);
        $this->assertEquals('12', $oAddress->oxaddress__oxstreetnr->value);
        $this->assertEquals('testStreeName2 str. 123', $oAddress->oxaddress__oxaddinfo->value);
        $this->assertEquals('testCity', $oAddress->oxaddress__oxcity->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $oAddress->oxaddress__oxcountryid->value);
        $this->assertEquals('333', $oAddress->oxaddress__oxstateid->value);
        $this->assertEquals('testZip', $oAddress->oxaddress__oxzip->value);
        $this->assertEquals('testPhoneNum', $oAddress->oxaddress__oxfon->value);

        $this->assertEquals($this->getSession()->getVariable('deladrid'), $sAddressId);

        // street no in first position
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = '12 testStreetNameNext str.';
        $oExpressCheckoutResponse->setData($aPayPalData);

        $oPayPalAddress = new oePayPalOxAddress();
        $oPayPalAddress->createPayPalAddress($oExpressCheckoutResponse, 'testUserId');
        $this->assertEquals('testStreetNameNext str.', $oPayPalAddress->oxaddress__oxstreet->value);
        $this->assertEquals('12', $oPayPalAddress->oxaddress__oxstreetnr->value);
    }

    /**
     * Test case for oePayPalOxAddress::createPayPalAddress()
     * Testing if address is save without checking if required fields are not empty.
     * This is not needed as we assume that PayPal data is correct.
     *
     *
     * @return null
     */
    public function testCreatePayPalAddressFail()
    {
        $aPayPalData = $this->_getPayPalData();
        $oPayPalData = new oePayPalResponseGetExpressCheckoutDetails();
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = '';
        $oPayPalData->setData($aPayPalData);
        $oPayPalAddress = new oePayPalOxAddress();

        // checking if required field exists
        $aReqFields = $oPayPalAddress->getConfig()->getConfigParam("aMustFillFields");
        $this->assertTrue(in_array("oxaddress__oxstreet", $aReqFields));

        $oPayPalAddress->createPayPalAddress($oPayPalData, 'testUserId');

        $oAddress = new oxAddress();
        $oAddress->load($oPayPalAddress->getId());

        $this->assertEquals('testName', $oAddress->oxaddress__oxfname->value);
        $this->assertEquals("", $oAddress->oxaddress__oxstreet->value);
    }

    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Not creating if exist
     *
     * @return null
     */
    public function testCreatePayPalAddressIfExist()
    {
        //creating existing address
        $oAddress = new oxAddress();
        $oAddress->oxaddress__oxuserid = new oxField('testUserId');
        $oAddress->oxaddress__oxfname = new oxField('testName');
        $oAddress->oxaddress__oxlname = new oxField('testSurname');
        $oAddress->oxaddress__oxstreet = new oxField('testStreetName str.');
        $oAddress->oxaddress__oxstreetnr = new oxField('12');
        $oAddress->oxaddress__oxcity = new oxField('testCity');
        $oAddress->oxaddress__oxstateid = new oxField('333');
        $oAddress->oxaddress__oxzip = new oxField('testZip');
        $oAddress->oxaddress__oxfon = new oxField('testPhoneNum');
        $oAddress->oxaddress__oxcountryid = new oxField('8f241f11096877ac0.98748826');
        $oAddress->save();
        $sAddressId = $oAddress->getId();

        $this->getSession()->setVariable('deladrid', null);

        $sQ = "SELECT COUNT(*) FROM `oxaddress`";
        $iAddressCount = oxDb::getDb()->getOne($sQ);

        // preparing data fo new address - the same
        $aPayPalData = $this->_getPayPalData();
        $oPayPalData = new oePayPalResponseGetExpressCheckoutDetails();
        $oPayPalData->setData($aPayPalData);

        $oPayPalOxAddress = new oePayPalOxAddress();
        $oPayPalOxAddress->createPayPalAddress($oPayPalData, 'testUserId');

        $iAddressCountAfter = oxDb::getDb()->getOne($sQ);
        // skips the same address
        $this->assertEquals($iAddressCount, $iAddressCountAfter);

        // sets existing address id
        $this->assertEquals($this->getSession()->getVariable('deladrid'), $sAddressId);
    }

    /**
     * Data provider for testCreatePayPalAddress_splittingAddress()
     *
     * @return array
     */
    public function createPayPalAddress_splittingAddress_dataProvider()
    {
        $aAddress["addr"][] = "4 Street Name ";
        $aAddress["addr"][] = " 4a Street Name";
        $aAddress["addr"][] = "4a-5    Street Name";
        $aAddress["addr"][] = "4a-5 11 Street Name";
        $aAddress["addr"][] = "Street Name 4";
        $aAddress["addr"][] = "Street Name   4a";
        $aAddress["addr"][] = "Street Name  4a-5  ";
        $aAddress["addr"][] = "Street Name 11 4a-5";
        $aAddress["addr"][] = " Street Name ";
        $aAddress["addr"][] = "bertoldstr.48";
        $aAddress["addr"][] = "Street Name   4 a";

        $aAddress["ress"][] = array("Street Name", "4");
        $aAddress["ress"][] = array("Street Name", "4a");
        $aAddress["ress"][] = array("Street Name", "4a-5");
        $aAddress["ress"][] = array("11 Street Name", "4a-5");
        $aAddress["ress"][] = array("Street Name", "4");
        $aAddress["ress"][] = array("Street Name", "4a");
        $aAddress["ress"][] = array("Street Name", "4a-5");
        $aAddress["ress"][] = array("Street Name 11", "4a-5");
        $aAddress["ress"][] = array("Street Name", "");
        $aAddress["ress"][] = array("bertoldstr.48", "");
        $aAddress["ress"][] = array("Street Name", "4 a");

        foreach ($aAddress["addr"] as $sKey => $sValue) {
            $aRet[] = array($sValue, $aAddress["ress"][$sKey]);
        }

        return $aRet;
    }


    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Creating new address
     *
     * @dataProvider createPayPalAddress_splittingAddress_dataProvider
     *
     * @return null
     */
    public function testCreatePayPalAddress_splittingAddress($sAddress, $aResult)
    {
        $aPayPalData = $this->_getPayPalData();
        $oPayPalData = new oePayPalResponseGetExpressCheckoutDetails();
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = $sAddress;
        $oPayPalData->setData($aPayPalData);

        $oPayPalOxAddress = new oePayPalOxAddress();
        $oPayPalOxAddress->createPayPalAddress($oPayPalData, 'testUserId');
        $sAddressId = $oPayPalOxAddress->getId();

        $oAddress = new oxAddress();
        $oAddress->load($sAddressId);

        $this->assertEquals($aResult[0], $oAddress->oxaddress__oxstreet->value);
        $this->assertEquals($aResult[1], $oAddress->oxaddress__oxstreetnr->value);
    }

    /**
     * Data provider for testCreatePayPalAddress_splittingUserName()
     *
     * @return array
     */
    public function createPayPalAddress_splittingUserName_dataProvider()
    {
        $aAddress["name"][] = "Firstname Lastname";
        $aAddress["name"][] = "Firstname Lastname Lastname2";
        $aAddress["name"][] = "Firstname Lastname Lastname2 Lastname3";
        $aAddress["name"][] = "Firstname";

        $aAddress["res"][] = array("Firstname", "Lastname");
        $aAddress["res"][] = array("Firstname", "Lastname Lastname2");
        $aAddress["res"][] = array("Firstname", "Lastname Lastname2 Lastname3");
        $aAddress["res"][] = array("Firstname", "");

        foreach ($aAddress["name"] as $sKey => $sValue) {
            $aRet[] = array($sValue, $aAddress["res"][$sKey]);
        }

        return $aRet;
    }


    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Creating new address
     *
     * @dataProvider createPayPalAddress_splittingUserName_dataProvider
     *
     * @return null
     */
    public function testCreatePayPalAddress_splittingUserName($sName, $aResult)
    {
        $aPayPalData = $this->_getPayPalData();
        $oPayPalData = new oePayPalResponseGetExpressCheckoutDetails();
        $aPayPalData['PAYMENTREQUEST_0_SHIPTONAME'] = $sName;
        $oPayPalData->setData($aPayPalData);

        $oPayPalOxAddress = new oePayPalOxAddress();
        $oPayPalOxAddress->createPayPalAddress($oPayPalData, 'testUserId');
        $sAddressId = $oPayPalOxAddress->getId();

        $oAddress = new oxAddress();
        $oAddress->load($sAddressId);

        $this->assertEquals($aResult[0], $oAddress->oxaddress__oxfname->value);
        $this->assertEquals($aResult[1], $oAddress->oxaddress__oxlname->value);
    }
}
