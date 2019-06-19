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

use OxidEsales\Eshop\Application\Model\User;

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
        $delete = 'TRUNCATE TABLE `oxuser`';
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($delete);

        parent::tearDown();
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
        $payPalData['EMAIL'] = 'test@test.mail';
        $payPalData['FIRSTNAME'] = 'testFirstName';
        $payPalData['LASTNAME'] = 'testLastName';
        $payPalData['PAYMENTREQUEST_0_SHIPTONAME'] = 'testFirstName testLastName';
        $payPalData['PHONENUM'] = 'testPhone';
        $payPalData['SALUTATION'] = 'testSalutation';
        $payPalData['BUSINESS'] = 'testBusiness';
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetName str. 12';
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET2'] = 'testCompany';
        $payPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCity';
        $payPalData['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = 'US';
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTATE'] = 'SS';
        $payPalData['PAYMENTREQUEST_0_SHIPTOZIP'] = 'testZip';
        $payPalData['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = 'testPhoneNum';

        return $payPalData;
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::createPayPalUser()
     * Creating user
     */
    public function testCreatePayPalUser()
    {
        $payPalData = $this->getPayPalData();
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($payPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['_setAutoGroups']);
        $payPalUser = $mockBuilder->getMock();
        $payPalUser->expects($this->once())->method('_setAutoGroups')->with($this->equalTo("8f241f11096877ac0.98748826"));
        $payPalUser->createPayPalUser($details);
        $userId = $payPalUser->getId();

        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->load($userId);

        $this->assertEquals(1, $user->oxuser__oxactive->value);
        $this->assertEquals('test@test.mail', $user->oxuser__oxusername->value);
        $this->assertEquals('testFirstName', $user->oxuser__oxfname->value);
        $this->assertEquals('testLastName', $user->oxuser__oxlname->value);
        $this->assertEquals('testPhoneNum', $user->oxuser__oxfon->value);
        $this->assertEquals('testSalutation', $user->oxuser__oxsal->value);
        $this->assertEquals('testBusiness', $user->oxuser__oxcompany->value);
        $this->assertEquals('testStreetName str.', $user->oxuser__oxstreet->value);
        $this->assertEquals('12', $user->oxuser__oxstreetnr->value);
        $this->assertEquals('testCity', $user->oxuser__oxcity->value);
        $this->assertEquals('testZip', $user->oxuser__oxzip->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $user->oxuser__oxcountryid->value);
        $this->assertEquals('333', $user->oxuser__oxstateid->value);
        $this->assertEquals('testCompany', $user->oxuser__oxaddinfo->value);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::createPayPalUser()
     * Creating user
     */
    public function testCreatePayPalUser_streetName()
    {
        // streetnr in first position
        $payPalData = $this->getPayPalData();
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = '12 testStreetName str.';
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($payPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $payPalUser = new \OxidEsales\PayPalModule\Model\User();
        $payPalUser->createPayPalUser($details);
        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->load($payPalUser->getId());

        $this->assertEquals('testStreetName str.', $user->oxuser__oxstreet->value);
        $this->assertEquals('12', $user->oxuser__oxstreetnr->value);
    }


    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::createPayPalUser()
     * Returning id if exist, not creating
     */
    public function testCreatePayPalAddressIfExist()
    {
        //creating existing user
        $payPalData = $this->getPayPalData();
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($payPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $payPalOxUser = new \OxidEsales\PayPalModule\Model\User();
        $payPalOxUser->createPayPalUser($details);

        $sQ = "SELECT COUNT(*) FROM `oxuser`";
        $addressCount = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQ);

        // prepareing data fo new address - the same
        $payPalOxUser = new \OxidEsales\PayPalModule\Model\User();
        $payPalOxUser->createPayPalUser($details);

        $addressCountAfter = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQ);

        // skips the same address
        $this->assertEquals($addressCount, $addressCountAfter);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSamePayPalUser()
    {
        $payPalData = $this->getPayPalData();
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($payPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $user = new \OxidEsales\PayPalModule\Model\User();
        $user->createPayPalUser($details);
        $this->assertTrue($user->isSamePayPalUser($details));

        $payPalData = $this->getPayPalData();
        $payPalData['FIRSTNAME'] = 'testFirstNameBla';
        $details->setData($payPalData);
        $this->assertFalse($user->isSamePayPalUser($details));

        $payPalData = $this->getPayPalData();
        $payPalData['LASTNAME'] = 'testFirstNameBla';
        $details->setData($payPalData);
        $this->assertFalse($user->isSamePayPalUser($details));

        $payPalData = $this->getPayPalData();
        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetNameBla str. 12';
        $details->setData($payPalData);
        $this->assertFalse($user->isSamePayPalUser($details));

        $payPalData = $this->getPayPalData();
        $payPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCitybla';
        $details->setData($payPalData);
        $this->assertFalse($user->isSamePayPalUser($details));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSamePayPalUser_decoding_html()
    {
        $payPalData = $this->getPayPalData();
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($payPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $user = new \OxidEsales\PayPalModule\Model\User();
        $user->createPayPalUser($details);

        // by default single quote ' will be convrted to &#039;
        $user->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field("test'FName");
        $user->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field("test'LName");

        $payPalData["FIRSTNAME"] = "test'FName";
        $payPalData["LASTNAME"] = "test'LName";
        $details->setData($payPalData);
        $this->assertTrue($user->isSamePayPalUser($details));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSameAddressPayPalUser()
    {
        $payPalData = $this->getPayPalData();
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($payPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $user = new \OxidEsales\PayPalModule\Model\User();
        $user->createPayPalUser($details);
        $this->assertTrue($user->isSamePayPalUser($details));

        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = 'testStreetNameBla str. 12';
        $details->setData($payPalData);
        $this->assertFalse($user->isSamePayPalUser($details));

        $payPalData['PAYMENTREQUEST_0_SHIPTOCITY'] = 'testCitybla';
        $details->setData($payPalData);
        $this->assertFalse($user->isSamePayPalUser($details));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isSamePayPalUser()
     */
    public function testIsSameAddressPayPalUser_decoding_html()
    {
        $payPalData = $this->getPayPalData();
        $details = new \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails();
        $details->setData($payPalData);
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $user = new \OxidEsales\PayPalModule\Model\User();
        $user->createPayPalUser($details);

        // by default single quote ' will be convrted to &#039;
        $user->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field("test'StreetName");;
        $user->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field("5");
        $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field("test'City");

        $payPalData['PAYMENTREQUEST_0_SHIPTOSTREET'] = "test'StreetName 5";
        $payPalData["PAYMENTREQUEST_0_SHIPTOCITY"] = "test'City";
        $details->setData($payPalData);

        $this->assertTrue($user->isSamePayPalUser($details));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isRealPayPalUser()
     * In single shop
     */
    public function testIsRealPayPalUser()
    {
        \OxidEsales\Eshop\Core\Registry::getConfig()->setConfigParam('blMallUsers', true);

        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.test');
        $user->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $user->setId('_testId');
        $user->setShopId('_testShop2');
        $user->save();

        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test1@test.test');
        $user->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('');
        $user->setShopId('_testShop1');
        $user->save();

        $userMock = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)
            ->setMethods(['getShopIdQueryPart'])
            ->getMock();
        $userMock->expects($this->never())->method('getShopIdQueryPart');

        $this->assertEquals('_testId', $userMock->isRealPayPalUser('test@test.test'));
        $this->assertFalse($userMock->isRealPayPalUser('test1@test.test'));
        $this->assertFalse($userMock->isRealPayPalUser('blabla@bla.bla'));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::isRealPayPalUser()
     * In multi shop
     */
    public function testIsRealPayPalUserMultiShop()
    {
        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.test');
        $user->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $user->oxuser__oxshopid = new \OxidEsales\Eshop\Core\Field(1);
        $user->setId('_testId');
        $user->save();

        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test3@test.test');
        $user->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $user->oxuser__oxshopid = new \OxidEsales\Eshop\Core\Field(2);
        $user->setId('_testId2');
        $user->save();

        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test1@test.test');
        $user->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('');
        $user->oxuser__oxshopid = new \OxidEsales\Eshop\Core\Field(1);
        $user->save();

        $user = new \OxidEsales\PayPalModule\Model\User();

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class);
        $mockBuilder->setMethods(['getShopId']);
        $config = $mockBuilder->getMock();
        $config->expects($this->any())->method('getShopId')->will($this->returnValue('1'));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, $config);
        \OxidEsales\Eshop\Core\Registry::getConfig()->setConfigParam('blMallUsers', true);

        $this->assertEquals('_testId', $user->isRealPayPalUser('test@test.test'));
        $this->assertFalse($user->isRealPayPalUser('test1@test.test'));
        $this->assertEquals('_testId2', $user->isRealPayPalUser('test3@test.test'));
        $this->assertFalse($user->isRealPayPalUser('blabla@bla.bla'));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::loadUserPayPalUser()
     */
    public function testLoadUserPayPalUser()
    {
        //session empty
        $user = new \OxidEsales\PayPalModule\Model\User();
        $this->assertFalse($user->loadUserPayPalUser());

        $user = new \OxidEsales\Eshop\Application\Model\User();
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.test');
        $user->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('paswd');
        $user->setId('_testId');
        $user->save();

        // user id in session
        $this->getSession()->setVariable('oepaypal-userId', '_testId');

        $user = new \OxidEsales\PayPalModule\Model\User();
        $this->assertTrue($user->loadUserPayPalUser());
        $this->assertEquals('_testId', $user->oxuser__oxid->value);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\User::initializeUserForCallBackPayPalUser()
     * Creating user
     */
    public function testInitializeUserForCallBackPayPalUser()
    {
        $this->addModuleObject(\OxidEsales\Eshop\Application\Model\Address::class, new \OxidEsales\PayPalModule\Model\Address());

        $payPalData["SHIPTOSTREET"] = "testStreetName str. 12a";
        $payPalData["SHIPTOCITY"] = "testCity";
        $payPalData["SHIPTOSTATE"] = "SS";
        $payPalData["SHIPTOCOUNTRY"] = "US";
        $payPalData["SHIPTOZIP"] = "testZip";

        $payPalUser = new \OxidEsales\PayPalModule\Model\User();
        $payPalUser->initializeUserForCallBackPayPalUser($payPalData);

        $this->assertTrue(is_string($payPalUser->getId()));

        $this->assertEquals('testStreetName str.', $payPalUser->oxuser__oxstreet->value);
        $this->assertEquals('12a', $payPalUser->oxuser__oxstreetnr->value);
        $this->assertEquals('testCity', $payPalUser->oxuser__oxcity->value);
        $this->assertEquals('testZip', $payPalUser->oxuser__oxzip->value);
        $this->assertEquals('8f241f11096877ac0.98748826', $payPalUser->oxuser__oxcountryid->value);
        $this->assertEquals('333', $payPalUser->oxuser__oxstateid->value);
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
