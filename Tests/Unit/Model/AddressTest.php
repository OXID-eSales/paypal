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

/**
 * Testing oxAccessRightException class.
 */
class AddressTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tear down the fixture.
     *
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->getSession()->setVariable('deladrid', null);
        $delete = 'TRUNCATE TABLE `oxaddress`';
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($delete);
    }

    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();

        // fix for state ID compatability between editions
        $sqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) " .
                     "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sqlState);
    }

    /**
     * Prepare PayPal response data array
     *
     * @return array
     */
    protected function getPayPalData()
    {
        $payPalData = array();
        $payPalData['PAYMENTREQUEST_0_SHIPTONAME'] = 'testName testSurname';
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetName str. 12';
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET2'] = 'testStreeName2 str. 123';
        $payPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCity';
        $payPalData['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = 'US';
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTATE'] = 'SS';
        $payPalData['PAYMENTREQUEST_0_SHIPTOZIP'] = 'testZip';
        $payPalData['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = 'testPhoneNum';
        
        return $payPalData;
    }


    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Creating new address
     */
    public function testCreatePayPalAddress()
    {
        $payPalData = $this->getPayPalData();
        $expressCheckoutResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $expressCheckoutResponse->setData($payPalData);

        $payPalOxAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $payPalOxAddress->createPayPalAddress($expressCheckoutResponse, 'testUserId');
        $addressId = $this->getSession()->getVariable('deladrid');

        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $address->load($addressId);

        $this->assertEquals('testUserId', $address->oxaddress__oxuserid->value);
        $this->assertEquals('testName', $address->oxaddress__oxfname->value);
        $this->assertEquals('testSurname', $address->oxaddress__oxlname->value);
        $this->assertEquals('testStreetName str.', $address->oxaddress__oxstreet->value);
        $this->assertEquals('12', $address->oxaddress__oxstreetnr->value);
        $this->assertEquals('testStreeName2 str. 123', $address->oxaddress__oxaddinfo->value);
        $this->assertEquals('testCity', $address->oxaddress__oxcity->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $address->oxaddress__oxcountryid->value);
        $this->assertEquals('333', $address->oxaddress__oxstateid->value);
        $this->assertEquals('testZip', $address->oxaddress__oxzip->value);
        $this->assertEquals('testPhoneNum', $address->oxaddress__oxfon->value);

        $this->assertEquals($this->getSession()->getVariable('deladrid'), $addressId);

        // street no in first position
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = '12 testStreetNameNext str.';
        $expressCheckoutResponse->setData($payPalData);

        $payPalAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $payPalAddress->createPayPalAddress($expressCheckoutResponse, 'testUserId');
        $this->assertEquals('testStreetNameNext str.', $payPalAddress->oxaddress__oxstreet->value);
        $this->assertEquals('12', $payPalAddress->oxaddress__oxstreetnr->value);
    }

    /**
     * Test case for Address::createPayPalAddress()
     * Testing if address is save without checking if required fields are not empty.
     * This is not needed as we assume that PayPal data is correct.
     */
    public function testCreatePayPalAddressFail()
    {
        $payPalData = $this->getPayPalData();
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = '';
        $expressCheckoutResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $expressCheckoutResponse->setData($payPalData);
        $payPalAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);

        // checking if required field exists
        $reqFields = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam("aMustFillFields");
        $this->assertTrue(in_array("oxaddress__oxstreet", $reqFields));

        $payPalAddress->createPayPalAddress($expressCheckoutResponse, 'testUserId');

        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $address->load($payPalAddress->getId());

        $this->assertEquals('testName', $address->oxaddress__oxfname->value);
        $this->assertEquals("", $address->oxaddress__oxstreet->value);
    }

    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Not creating if exist
     */
    public function testCreatePayPalAddressIfExist()
    {
        //creating existing address
        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $address->oxaddress__oxuserid = new \OxidEsales\Eshop\Core\Field('testUserId');
        $address->oxaddress__oxfname = new \OxidEsales\Eshop\Core\Field('testName');
        $address->oxaddress__oxlname = new \OxidEsales\Eshop\Core\Field('testSurname');
        $address->oxaddress__oxstreet = new \OxidEsales\Eshop\Core\Field('testStreetName str.');
        $address->oxaddress__oxstreetnr = new \OxidEsales\Eshop\Core\Field('12');
        $address->oxaddress__oxcity = new \OxidEsales\Eshop\Core\Field('testCity');
        $address->oxaddress__oxstateid = new \OxidEsales\Eshop\Core\Field('333');
        $address->oxaddress__oxzip = new \OxidEsales\Eshop\Core\Field('testZip');
        $address->oxaddress__oxfon = new \OxidEsales\Eshop\Core\Field('testPhoneNum');
        $address->oxaddress__oxcountryid = new \OxidEsales\Eshop\Core\Field('8f241f11096877ac0.98748826');
        $address->save();
        $addressId = $address->getId();

        $this->getSession()->setVariable('deladrid', null);

        $sQ = "SELECT COUNT(*) FROM `oxaddress`";
        $addressCount = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQ);

        // preparing data fo new address - the same
        $payPalData = $this->getPayPalData();
        $expressCheckoutResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $expressCheckoutResponse->setData($payPalData);

        $payPalOxAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $payPalOxAddress->createPayPalAddress($expressCheckoutResponse, 'testUserId');

        $addressCountAfter = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQ);
        // skips the same address
        $this->assertEquals($addressCount, $addressCountAfter);

        // sets existing address id
        $this->assertEquals($this->getSession()->getVariable('deladrid'), $addressId);
    }

    /**
     * Data provider for testCreatePayPalAddress_splittingAddress()
     *
     * @return array
     */
    public function createPayPalAddress_splittingAddress_dataProvider()
    {
        $address["addr"][] = "4 Street Name ";
        $address["addr"][] = " 4a Street Name";
        $address["addr"][] = "4a-5    Street Name";
        $address["addr"][] = "4a-5 11 Street Name";
        $address["addr"][] = "Street Name 4";
        $address["addr"][] = "Street Name   4a";
        $address["addr"][] = "Street Name  4a-5  ";
        $address["addr"][] = "Street Name 11 4a-5";
        $address["addr"][] = " Street Name ";
        $address["addr"][] = "bertoldstr.48";
        $address["addr"][] = "Street Name   4 a";

        $address["ress"][] = array("Street Name", "4");
        $address["ress"][] = array("Street Name", "4a");
        $address["ress"][] = array("Street Name", "4a-5");
        $address["ress"][] = array("11 Street Name", "4a-5");
        $address["ress"][] = array("Street Name", "4");
        $address["ress"][] = array("Street Name", "4a");
        $address["ress"][] = array("Street Name", "4a-5");
        $address["ress"][] = array("Street Name 11", "4a-5");
        $address["ress"][] = array("Street Name", "");
        $address["ress"][] = array("bertoldstr.48", "");
        $address["ress"][] = array("Street Name", "4 a");

        foreach ($address["addr"] as $key => $value) {
            $ret[] = array($value, $address["ress"][$key]);
        }

        return $ret;
    }


    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Creating new address
     *
     * @dataProvider createPayPalAddress_splittingAddress_dataProvider
     */
    public function testCreatePayPalAddress_splittingAddress($address, $result)
    {
        $payPalData = $this->getPayPalData();
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = $address;
        $expressCheckoutResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $expressCheckoutResponse->setData($payPalData);

        $payPalOxAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $payPalOxAddress->createPayPalAddress($expressCheckoutResponse, 'testUserId');
        $addressId = $payPalOxAddress->getId();

        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $address->load($addressId);

        $this->assertEquals($result[0], $address->oxaddress__oxstreet->value);
        $this->assertEquals($result[1], $address->oxaddress__oxstreetnr->value);
    }

    /**
     * Data provider for testCreatePayPalAddress_splittingUserName()
     *
     * @return array
     */
    public function createPayPalAddress_splittingUserName_dataProvider()
    {
        $address["name"][] = "Firstname Lastname";
        $address["name"][] = "Firstname Lastname Lastname2";
        $address["name"][] = "Firstname Lastname Lastname2 Lastname3";
        $address["name"][] = "Firstname";

        $address["res"][] = array("Firstname", "Lastname");
        $address["res"][] = array("Firstname", "Lastname Lastname2");
        $address["res"][] = array("Firstname", "Lastname Lastname2 Lastname3");
        $address["res"][] = array("Firstname", "");

        foreach ($address["name"] as $key => $value) {
            $ret[] = array($value, $address["res"][$key]);
        }

        return $ret;
    }


    /**
     * Test case for oePayPalAddress::createPayPalAddress()
     * Creating new address
     *
     * @dataProvider createPayPalAddress_splittingUserName_dataProvider
     */
    public function testCreatePayPalAddress_splittingUserName($name, $result)
    {
        $payPalData = $this->getPayPalData();
        $payPalData['PAYMENTREQUEST_0_SHIPTONAME'] = $name;
        $expressCheckoutResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $expressCheckoutResponse->setData($payPalData);

        $payPalOxAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $payPalOxAddress->createPayPalAddress($expressCheckoutResponse, 'testUserId');
        $addressId = $payPalOxAddress->getId();

        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $address->load($addressId);

        $this->assertEquals($result[0], $address->oxaddress__oxfname->value);
        $this->assertEquals($result[1], $address->oxaddress__oxlname->value);
    }
}
