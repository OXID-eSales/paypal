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


/**
 * Testing _oePayPalPaymentValidatorTest  class.
 */
class Unit_oePayPal_Models_oePayPalPaymentValidatorTest extends OxidTestCase
{
    /**
     * getCheckCountry test with default value.
     */
    public function testSetGetCheckCountry_default()
    {
        $oValidator = new oePayPalPaymentValidator();

        $this->assertTrue($oValidator->getCheckCountry());
    }

    /**
     * getCheckCountry test with custom set value.
     */
    public function testSetGetCheckCountry_custom()
    {
        $blCheckCountry = false;
        $oValidator = new oePayPalPaymentValidator();
        $oValidator->setCheckCountry($blCheckCountry);

        $this->assertFalse($oValidator->getCheckCountry());
    }

    /**
     * Testing validator when price and user objects are not set and payment is active
     */
    public function testIsPaymentValid_paymentActive_true()
    {
        $oPayment = new oxPayment();
        $oPayment->oxpayments__oxactive = new oxField(1);

        $oValidator = new oePayPalPaymentValidator();

        $oValidator->setPayment($oPayment);

        $this->assertTrue($oValidator->isPaymentValid());
    }

    /**
     * Checks validator when getCheckoutCountry returns default- true.
     */
    public function testIsPaymentValid_checkCountryDefault_false()
    {
        $oPaymentValidator = $this->_getPaymentValidator();

        $this->assertFalse($oPaymentValidator->isPaymentValid());
    }

    /**
     * Checks validator when getCheckoutCountry returns custom- false.
     */
    public function testIsPaymentValid_checkCountryCustom_true()
    {
        $oPaymentValidator = $this->_getPaymentValidator();
        $oPaymentValidator->setCheckCountry(false);

        $this->assertTrue($oPaymentValidator->isPaymentValid());
    }

    /**
     * Testing validator when price and user objects are not set and payment is not active
     */
    public function testIsPaymentValid_paymentInActive_true()
    {
        $oPayment = new oxPayment();
        $oPayment->oxpayments__oxactive = new oxField(0);

        $oValidator = new oePayPalPaymentValidator();

        $oValidator->setPayment($oPayment);

        $this->assertFalse($oValidator->isPaymentValid());
    }

    public function providerIsPaymentValid_allCases()
    {
        $oGroup1 = $this->getMock("oxGroups", array("getId"));
        $oGroup1->expects($this->any())->method("getId")->will($this->returnValue("someGroup1"));

        $oGroup2 = $this->getMock("oxGroups", array("getId"));
        $oGroup2->expects($this->any())->method("getId")->will($this->returnValue("someGroup2"));

        $oListEmpty = new oxList();

        $oList1 = new oxList();
        $oList1[] = $oGroup1;

        $oList2 = new oxList();
        $oList2[] = $oGroup1;
        $oList2[] = $oGroup2;

        return array(
            // price outside range, user has account, payment has no set countries, and no user groups set - expects false
            array(1, 10, 100, 5, array(), null, "someCountry", true, $oListEmpty, array(), false),
            // price outside range, user has account, payment has no set countries, but has user group set, user belongs to the same group - expects false
            array(1, 10, 100, 110, array(), null, "someCountry", true, $oList1, array('someGroup1' => "data"), false),
            // price inside range, user has account, payment has no set countries, but has user group set, user belongs to the same group - expects true
            array(1, 10, 100, 10, array(), null, "someCountry", true, $oList1, array('someGroup1' => "data"), true),
            // paypal payment is not active - expects false
            array(0, 10, 100, 10, array(), null, "someCountry", true, $oListEmpty, array(), false),
            // Shipping country not equal to given countries
            // price inside range, user has account, payment has countries and groups set, but user is from other country, but belongs to the same group - expects false
            array(1, 10, 100, 10, array("someOtherCountry", "andAnotherCountry"), "someCountry", null, true, $oList1, array('someGroup1' => "data"), false),
            // price inside range, user has account, payment has countries and groups set, but user is from the same country and belongs to the same group - expects true
            array(1, 10, 100, 100, array("someOtherCountry", "someCountry", "andAnotherCountry"), null, "someCountry", true, $oList1, array('someGroup1' => "data"), true),
            // price inside range, user has account, payment does not have countries set, but has user groups set, user does not belong to any user group - expects false
            array(1, 10, 100, 10, array(), null, "someCountry", true, $oList2, array(), false),
            // price inside range, user has account, payment does not have countries set, but has user groups set, user belongs to one user group - expects true
            array(1, 10, 100, 10, array(), null, "someCountry", true, $oList2, array('someGroup1' => "data"), true),
            // price inside range, user has account, payment does not have countries set, but has user groups set, user belongs to different user group - expects false
            array(1, 10, 100, 10, array(), null, "someCountry", true, $oList2, array('someGroup3' => "data"), false),
            // price inside range, user does not have account (anonymous), payment has groups set - expects true
            array(1, 10, 100, 10, array(), null, "someCountry", false, $oList2, array(), true),
            // Shipping country not given, but user address is given
            array(1, 10, 100, 10, array(), "someCountry", null, false, $oList2, array(), true),
            array(1, 10, 100, 10, array("someOtherCountry", "someCountry", "andAnotherCountry"), "someCountry", null, false, $oList2, array(), true),
            // Shipping country not given and user address is not same as given countries
            array(1, 10, 100, 10, array("someOtherCountry", "andAnotherCountry"), "someCountry", null, true, $oList2, array(), false),
        );
    }

    /**
     * Testing PayPal payment validator all cases
     *
     * @dataProvider providerIsPaymentValid_allCases
     */
    public function testIsPaymentValid_allCases($isActivePayment, $dRangeFrom, $dRangeTo, $dPrice, $aPaymentCountries, $sUserCountryId, $sUserShippingCountryId, $blUserHasAccount, $aPaymentUserGroups, $aUserGroups, $blExpectedResult)
    {
        $oPayment = $this->getMock("oxPayment", array("getCountries", "getGroups"));
        $oPayment->expects($this->any())->method("getCountries")->will($this->returnValue($aPaymentCountries));
        $oPayment->expects($this->any())->method("getGroups")->will($this->returnValue($aPaymentUserGroups));

        $oPayment->load('oxidpaypal');
        $oPayment->oxpayments__oxfromamount = new oxField($dRangeFrom);
        $oPayment->oxpayments__oxtoamount = new oxField($dRangeTo);
        $oPayment->oxpayments__oxactive = new oxField($isActivePayment);

        $oAddress = new oxAddress();
        $oAddress->oxaddress__oxcountryid = new oxField($sUserShippingCountryId);

        $oUser = $this->getMock("oxUser", array("getUserGroups", "hasAccount", "getSelectedAddress", "getSelectedAddressId"));
        $oUser->expects($this->any())->method("getUserGroups")->will($this->returnValue($aUserGroups));
        $oUser->expects($this->any())->method("hasAccount")->will($this->returnValue($blUserHasAccount));
        $oUser->expects($this->any())->method("getSelectedAddress")->will($this->returnValue($oAddress));
        $oUser->expects($this->any())->method("getSelectedAddressId")->will($this->returnValue($sUserShippingCountryId));

        $oUser->oxuser__oxcountryid = new oxField($sUserCountryId);

        $oValidator = new oePayPalPaymentValidator();

        $oValidator->setConfig($this->getConfig());
        $oValidator->setPayment($oPayment);
        $oValidator->setUser($oUser);
        $oValidator->setPrice($dPrice);

        $this->assertEquals($blExpectedResult, $oValidator->isPaymentValid());
    }

    /**
     * Testing PayPal payment validator all cases
     *
     */
    public function testIsPaymentValid_NoGroupsAssignedToPayment_True()
    {
        $oPayment = $this->getMock("oxPayment", array("getCountries", "getGroups"));
        $oPayment->oxpayments__oxactive = new oxField(1);
        $oPayment->expects($this->any())->method("getGroups")->will($this->returnValue(array()));

        $oUser = $this->getMock("oxUser", array("hasAccount"));
        $oUser->expects($this->any())->method("hasAccount")->will($this->returnValue(1));

        $oValidator = new oePayPalPaymentValidator();

        $oValidator->setConfig($this->getConfig());
        $oValidator->setPayment($oPayment);
        $oValidator->setUser($oUser);

        $this->assertEquals(true, $oValidator->isPaymentValid());
    }

    /**
     * Testing isPaymentValid when iMinOrderPrice is not set and payment price is passed
     */
    public function testIsPaymentActive_MinOrderPriceNotSet_True()
    {
        $oPayment = new oxPayment();
        $oPayment->oxpayments__oxfromamount = new oxField(0);
        $oPayment->oxpayments__oxtoamount = new oxField(0);
        $oPayment->oxpayments__oxactive = new oxField(1);

        $this->getConfig()->setConfigParam('iMinOrderPrice', '');

        $oValidator = new oePayPalPaymentValidator();
        $oValidator->setConfig($this->getConfig());
        $oValidator->setPayment($oPayment);
        $oValidator->setPrice(50);

        $this->assertEquals(true, $oValidator->isPaymentValid());
    }

    /**
     * Testing isPaymentValid when iMinOrderPrice is set and payment price is higher
     */
    public function testIsPaymentActive_MinOrderPriceSet_True()
    {
        $oPayment = new oxPayment();
        $oPayment->oxpayments__oxfromamount = new oxField(0);
        $oPayment->oxpayments__oxtoamount = new oxField(0);
        $oPayment->oxpayments__oxactive = new oxField(1);

        $this->getConfig()->setConfigParam('iMinOrderPrice', 10);

        $oValidator = new oePayPalPaymentValidator();
        $oValidator->setConfig($this->getConfig());
        $oValidator->setPayment($oPayment);
        $oValidator->setPrice(50);

        $this->assertEquals(true, $oValidator->isPaymentValid());
    }

    /**
     * Testing isPaymentValid when iMinOrderPrice is not set and payment price is lower
     */
    public function testIsPaymentActive_MinOrderPriceSet_False()
    {
        $oPayment = new oxPayment();
        $oPayment->oxpayments__oxfromamount = new oxField(0);
        $oPayment->oxpayments__oxtoamount = new oxField(0);
        $oPayment->oxpayments__oxactive = new oxField(1);

        $this->getConfig()->setConfigParam('iMinOrderPrice', 50);

        $oValidator = new oePayPalPaymentValidator();
        $oValidator->setConfig($this->getConfig());
        $oValidator->setPayment($oPayment);
        $oValidator->setPrice(10);

        $this->assertEquals(false, $oValidator->isPaymentValid());
    }

    /**
     * @return oePayPalPaymentValidator
     */
    protected function _getPaymentValidator()
    {
        $oUser = $this->getMock('oxUser', array('hasAccount'));
        $oUser->expects($this->any())->method('hasAccount')->will($this->returnValue(true));

        $oPaymentValidator = $this->getMock(
            'oePayPalPaymentValidator', array('isPaymentActive', 'getPrice',
                                              '_checkPriceRange', '_checkMinOrderPrice', 'getUser', '_checkUserGroup', '_checkUserCountry')
        );
        $oPaymentValidator->expects($this->any())->method('isPaymentActive')->will($this->returnValue(true));
        $oPaymentValidator->expects($this->any())->method('getPrice')->will($this->returnValue(true));
        $oPaymentValidator->expects($this->any())->method('_checkPriceRange')->will($this->returnValue(true));
        $oPaymentValidator->expects($this->any())->method('_checkMinOrderPrice')->will($this->returnValue(true));
        $oPaymentValidator->expects($this->any())->method('getUser')->will($this->returnValue($oUser));
        $oPaymentValidator->expects($this->any())->method('_checkUserGroup')->will($this->returnValue(true));
        $oPaymentValidator->expects($this->any())->method('_checkUserCountry')->will($this->returnValue(false));

        return $oPaymentValidator;
    }
}