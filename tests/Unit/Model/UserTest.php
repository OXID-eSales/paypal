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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oxAccessRightException class.
 */
class UserTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        $sDelete = 'TRUNCATE TABLE `oxuser`';
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sDelete);
    }

    /**
     * Set up
     */
    protected function setUp()
    {
        // fix for state ID compatability between editions
        $sSqlState = "REPLACE INTO `oxstates` (`OXID`, `OXCOUNTRYID`, `OXTITLE`, `OXISOALPHA2`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXTIMESTAMP`) " .
                     "VALUES ('333', '8f241f11096877ac0.98748826', 'USA last state', 'SS', 'USA last state', '', '', CURRENT_TIMESTAMP);";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sSqlState);
    }

    /**
     * Prepare PayPal response data array
     *
     * @return array
     */
    protected function _getPayPalData()
    {
        $aPayPalData = array();
        $aPayPalData['EMAIL'] = 'test@test.mail';
        $aPayPalData['FIRSTNAME'] = 'testFirstName';
        $aPayPalData['LASTNAME'] = 'testLastName';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTONAME'] = 'testFirstName testLastName';
        $aPayPalData['PHONENUM'] = 'testPhone';
        $aPayPalData['SALUTATION'] = 'testSalutation';
        $aPayPalData['BUSINESS'] = 'testBusiness';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetName str. 12';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET2'] = 'testCompany';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCity';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = 'US';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTATE'] = 'SS';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOZIP'] = 'testZip';
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = 'testPhoneNum';

        return $aPayPalData;
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::createPayPalUser()
     * Creating user
     */
    public function testCreatePayPalUser()
    {
        $aPayPalData = $this->_getPayPalData();
        $oDetails = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $oDetails->setData($aPayPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $oPayPalUser = $this->getMock(\OxidEsales\PayPalModule\Model\User::class, array('_setAutoGroups'));
        $oPayPalUser->expects($this->once())->method('_setAutoGroups')->with($this->equalTo("8f241f11096877ac0.98748826"));
        $oPayPalUser->createPayPalUser($oDetails);
        $sUserId = $oPayPalUser->getId();

        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->load($sUserId);

        $this->assertEquals(1, $oUser->oxuser__oxactive->value);
        $this->assertEquals('test@test.mail', $oUser->oxuser__oxusername->value);
        $this->assertEquals('testFirstName', $oUser->oxuser__oxfname->value);
        $this->assertEquals('testLastName', $oUser->oxuser__oxlname->value);
        $this->assertEquals('testPhoneNum', $oUser->oxuser__oxfon->value);
        $this->assertEquals('testSalutation', $oUser->oxuser__oxsal->value);
        $this->assertEquals('testBusiness', $oUser->oxuser__oxcompany->value);
        $this->assertEquals('testStreetName str.', $oUser->oxuser__oxstreet->value);
        $this->assertEquals('12', $oUser->oxuser__oxstreetnr->value);
        $this->assertEquals('testCity', $oUser->oxuser__oxcity->value);
        $this->assertEquals('testZip', $oUser->oxuser__oxzip->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $oUser->oxuser__oxcountryid->value);
        $this->assertEquals('333', $oUser->oxuser__oxstateid->value);
        $this->assertEquals('testCompany', $oUser->oxuser__oxaddinfo->value);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::createPayPalUser()
     * Creating user
     */
    public function testCreatePayPalUser_streetName()
    {
        // streetnr in firt position
        $aPayPalData = $this->_getPayPalData();
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = '12 testStreetName str.';
        $oDetails = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $oDetails->setData($aPayPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $oPayPalUser = new \OxidEsales\PayPalModule\Model\User();
        $oPayPalUser->createPayPalUser($oDetails);
        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->load($oPayPalUser->getId());

        $this->assertEquals('testStreetName str.', $oUser->oxuser__oxstreet->value);
        $this->assertEquals('12', $oUser->oxuser__oxstreetnr->value);
    }


    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::createPayPalUser()
     * Returning id if exist, not creating
     */
    public function testCreatePayPalAddressIfExist()
    {
        //creating existing user
        $aPayPalData = $this->_getPayPalData();
        $oDetails = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $oDetails->setData($aPayPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $oPayPalOxUser = new \OxidEsales\PayPalModule\Model\User();
        $oPayPalOxUser->createPayPalUser($oDetails);

        $sQ = "SELECT COUNT(*) FROM `oxuser`";
        $iAddressCount = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQ);

        // prepareing data fo new address - the same
        $oPayPalOxUser = new \OxidEsales\PayPalModule\Model\User();
        $oPayPalOxUser->createPayPalUser($oDetails);

        $iAddressCountAfter = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQ);

        // skips the same address
        $this->assertEquals($iAddressCount, $iAddressCountAfter);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSamePayPalUser()
    {
        $aPayPalData = $this->_getPayPalData();
        $oDetails = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $oDetails->setData($aPayPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $oUser = new \OxidEsales\PayPalModule\Model\User();
        $oUser->createPayPalUser($oDetails);
        $this->assertTrue($oUser->isSamePayPalUser($oDetails));

        $aPayPalData = $this->_getPayPalData();
        $aPayPalData['FIRSTNAME'] = 'testFirstNameBla';
        $oDetails->setData($aPayPalData);
        $this->assertFalse($oUser->isSamePayPalUser($oDetails));

        $aPayPalData = $this->_getPayPalData();
        $aPayPalData['LASTNAME'] = 'testFirstNameBla';
        $oDetails->setData($aPayPalData);
        $this->assertFalse($oUser->isSamePayPalUser($oDetails));

        $aPayPalData = $this->_getPayPalData();
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetNameBla str. 12';
        $oDetails->setData($aPayPalData);
        $this->assertFalse($oUser->isSamePayPalUser($oDetails));

        $aPayPalData = $this->_getPayPalData();
        $aPayPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCitybla';
        $oDetails->setData($aPayPalData);
        $this->assertFalse($oUser->isSamePayPalUser($oDetails));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSamePayPalUser_decoding_html()
    {
        $aPayPalData = $this->_getPayPalData();
        $oDetails = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $oDetails->setData($aPayPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $oUser = new \OxidEsales\PayPalModule\Model\User();
        $oUser->createPayPalUser($oDetails);

        // by default single quote ' will be convrted to &#039;
        $oUser->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field("test'FName");
        $oUser->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field("test'LName");

        $aPayPalData["FIRSTNAME"] = "test'FName";
        $aPayPalData["LASTNAME"] = "test'LName";
        $oDetails->setData($aPayPalData);
        $this->assertTrue($oUser->isSamePayPalUser($oDetails));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSameAddressPayPalUser()
    {
        $aPayPalData = $this->_getPayPalData();
        $oDetails = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $oDetails->setData($aPayPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $oUser = new \OxidEsales\PayPalModule\Model\User();
        $oUser->createPayPalUser($oDetails);
        $this->assertTrue($oUser->isSamePayPalUser($oDetails));

        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetNameBla str. 12';
        $oDetails->setData($aPayPalData);
        $this->assertFalse($oUser->isSamePayPalUser($oDetails));

        $aPayPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCitybla';
        $oDetails->setData($aPayPalData);
        $this->assertFalse($oUser->isSamePayPalUser($oDetails));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSameAddressPayPalUser_decoding_html()
    {
        $aPayPalData = $this->_getPayPalData();
        $oDetails = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $oDetails->setData($aPayPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $oUser = new \OxidEsales\PayPalModule\Model\User();
        $oUser->createPayPalUser($oDetails);

        // by default single quote ' will be convrted to &#039;
        $oUser->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field("test'StreetName");;
        $oUser->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field("5");
        $oUser->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field("test'City");

        $aPayPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = "test'StreetName 5";
        $aPayPalData["PAYMENTREQUEST_0_SHIPTOCITY"] = "test'City";
        $oDetails->setData($aPayPalData);

        $this->assertTrue($oUser->isSamePayPalUser($oDetails));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isRealPayPalUser()
     * In single shop
     */
    public function testIsRealPayPalUser()
    {
        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array('getConfigParam', 'getShopId'));
        $oConfig->expects($this->never())->method('getShopId');
        $oConfig->expects($this->any())->method('getConfigParam')->with('blMallUsers')->will($this->returnValue(true));

        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.test');
        $oUser->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $oUser->setId('_testId');
        $oUser->setShopId('_testShop2');
        $oUser->save();

        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test1@test.test');
        $oUser->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('');
        $oUser->setShopId('_testShop1');
        $oUser->save();

        $oUser = $this->getMock(\OxidEsales\PayPalModule\Model\User::class, array('getConfig'), array(), '', false);
        $oUser->expects($this->any())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertEquals('_testId', $oUser->isRealPayPalUser('test@test.test'));
        $this->assertFalse($oUser->isRealPayPalUser('test1@test.test'));
        $this->assertFalse($oUser->isRealPayPalUser('blabla@bla.bla'));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isRealPayPalUser()
     * In multi shop
     */
    public function testIsRealPayPalUserMultiShop()
    {
        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array('getConfigParam', 'getShopId'));
        $oConfig->expects($this->any())->method('getShopId')->will($this->returnValue('_testShop1'));
        $oConfig->expects($this->any())->method('getConfigParam')->with('blMallUsers')->will($this->returnValue(false));

        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.test');
        $oUser->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $oUser->oxuser__oxshopid = new \OxidEsales\Eshop\Core\Field('_testShop1');
        $oUser->setId('_testId');
        $oUser->save();

        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test3@test.test');
        $oUser->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $oUser->setId('_testId2');
        $oUser->setShopId('_testShop2');
        $oUser->save();

        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test1@test.test');
        $oUser->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('');
        $oUser->setShopId('_testShop1');
        $oUser->save();

        $oUser = new \OxidEsales\PayPalModule\Model\User();

        $oUser = $this->getMock(\OxidEsales\PayPalModule\Model\User::class, array('getConfig'), array(), '', false);
        $oUser->expects($this->any())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertEquals('_testId', $oUser->isRealPayPalUser('test@test.test'));
        $this->assertFalse($oUser->isRealPayPalUser('test1@test.test'));
        $this->assertFalse($oUser->isRealPayPalUser('test3@test.test'));
        $this->assertFalse($oUser->isRealPayPalUser('blabla@bla.bla'));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::loadUserPayPalUser()
     */
    public function testLoadUserPayPalUser()
    {
        //session empty
        $oUser = new \OxidEsales\PayPalModule\Model\User();
        $this->assertNull($oUser->loadUserPayPalUser());

        $oUser = new \OxidEsales\Eshop\Application\Model\User();
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.test');
        $oUser->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $oUser->setId('_testId');
        $oUser->save();

        // user id in session
        $this->getSession()->setVariable('oepaypal-userId', '_testId');

        $oUser = new \OxidEsales\PayPalModule\Model\User();
        $this->assertTrue($oUser->loadUserPayPalUser());
        $this->assertEquals('_testId', $oUser->oxuser__oxid->value);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::initializeUserForCallBackPayPalUser()
     * Creating user
     */
    public function testInitializeUserForCallBackPayPalUser()
    {
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $aPayPalData["SHIPTOSTREET"] = "testStreetName str. 12a";
        $aPayPalData["SHIPTOCITY"] = "testCity";
        $aPayPalData["SHIPTOSTATE"] = "SS";
        $aPayPalData["SHIPTOCOUNTRY"] = "US";
        $aPayPalData["SHIPTOZIP"] = "testZip";

        $oPayPalUser = new \OxidEsales\PayPalModule\Model\User();
        $oPayPalUser->initializeUserForCallBackPayPalUser($aPayPalData);

        $this->assertTrue(is_string($oPayPalUser->getId()));

        $this->assertEquals('testStreetName str.', $oPayPalUser->oxuser__oxstreet->value);
        $this->assertEquals('12a', $oPayPalUser->oxuser__oxstreetnr->value);
        $this->assertEquals('testCity', $oPayPalUser->oxuser__oxcity->value);
        $this->assertEquals('testZip', $oPayPalUser->oxuser__oxzip->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $oPayPalUser->oxuser__oxcountryid->value);
        $this->assertEquals('333', $oPayPalUser->oxuser__oxstateid->value);
    }

    /**
     * Mock an object which is created by oxNew.
     *
     * Attention: please don't use this method, we want to get rid of it - all places can, and should, be replaced
     *            with plain mocks.
     *
     * Hint: see also Unit/Controller/ExpressCheckoutDispatcherTest
     *
     * @param string $className The name under which the object will be created with oxNew.
     * @param object $object    The mocked object oxNew should return instead of the original one.
     */
    protected function addModuleObject($className, $object)
    {
        \OxidEsales\Eshop\Core\Registry::set($className, null);
        \OxidEsales\Eshop\Core\UtilsObject::setClassInstance($className, $object);
    }
}
