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
 * Testing \OxidEsales\PayPalModule\Model\PaymentValidator class.
 */
class PaymentValidatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * getCheckCountry test with default value.
     */
    public function testSetGetCheckCountry_default()
    {
        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();

        $this->assertTrue($validator->getCheckCountry());
    }

    /**
     * getCheckCountry test with custom set value.
     */
    public function testSetGetCheckCountry_custom()
    {
        $checkCountry = false;
        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();
        $validator->setCheckCountry($checkCountry);

        $this->assertFalse($validator->getCheckCountry());
    }

    /**
     * Testing validator when price and user objects are not set and payment is active
     */
    public function testIsPaymentValid_paymentActive_true()
    {
        $payment = new \OxidEsales\Eshop\Application\Model\Payment();
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);

        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();

        $validator->setPayment($payment);

        $this->assertTrue($validator->isPaymentValid());
    }

    /**
     * Checks validator when getCheckoutCountry returns default- true.
     */
    public function testIsPaymentValid_checkCountryDefault_false()
    {
        $paymentValidator = $this->getPaymentValidator();

        $this->assertFalse($paymentValidator->isPaymentValid());
    }

    /**
     * Checks validator when getCheckoutCountry returns custom- false.
     */
    public function testIsPaymentValid_checkCountryCustom_true()
    {
        $paymentValidator = $this->getPaymentValidator();
        $paymentValidator->setCheckCountry(false);

        $this->assertTrue($paymentValidator->isPaymentValid());
    }

    /**
     * Testing validator when price and user objects are not set and payment is not active
     */
    public function testIsPaymentValid_paymentInActive_true()
    {
        $payment = new \OxidEsales\Eshop\Application\Model\Payment();
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(0);

        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();

        $validator->setPayment($payment);

        $this->assertFalse($validator->isPaymentValid());
    }

    public function providerIsPaymentValid_allCases()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Groups::class);
        $mockBuilder->setMethods(['getId']);
        $group1 = $mockBuilder->getMock();
        $group1->expects($this->any())->method("getId")->will($this->returnValue("someGroup1"));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Groups::class);
        $mockBuilder->setMethods(['getId']);
        $group2 = $mockBuilder->getMock();
        $group2->expects($this->any())->method("getId")->will($this->returnValue("someGroup2"));

        $listEmpty = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);

        $list1 = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $list1[] = $group1;

        $list2 = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $list2[] = $group1;
        $list2[] = $group2;

        return array(
            // price outside range, user has account, payment has no set countries, and no user groups set - expects false
            array(1, 10, 100, 5, array(), null, "someCountry", true, $listEmpty, array(), false),
            // price outside range, user has account, payment has no set countries, but has user group set, user belongs to the same group - expects false
            array(1, 10, 100, 110, array(), null, "someCountry", true, $list1, array('someGroup1' => "data"), false),
            // price inside range, user has account, payment has no set countries, but has user group set, user belongs to the same group - expects true
            array(1, 10, 100, 10, array(), null, "someCountry", true, $list1, array('someGroup1' => "data"), true),
            // paypal payment is not active - expects false
            array(0, 10, 100, 10, array(), null, "someCountry", true, $listEmpty, array(), false),
            // Shipping country not equal to given countries
            // price inside range, user has account, payment has countries and groups set, but user is from other country, but belongs to the same group - expects false
            array(1, 10, 100, 10, array("someOtherCountry", "andAnotherCountry"), "someCountry", null, true, $list1, array('someGroup1' => "data"), false),
            // price inside range, user has account, payment has countries and groups set, but user is from the same country and belongs to the same group - expects true
            array(1, 10, 100, 100, array("someOtherCountry", "someCountry", "andAnotherCountry"), null, "someCountry", true, $list1, array('someGroup1' => "data"), true),
            // price inside range, user has account, payment does not have countries set, but has user groups set, user does not belong to any user group - expects false
            array(1, 10, 100, 10, array(), null, "someCountry", true, $list2, array(), false),
            // price inside range, user has account, payment does not have countries set, but has user groups set, user belongs to one user group - expects true
            array(1, 10, 100, 10, array(), null, "someCountry", true, $list2, array('someGroup1' => "data"), true),
            // price inside range, user has account, payment does not have countries set, but has user groups set, user belongs to different user group - expects false
            array(1, 10, 100, 10, array(), null, "someCountry", true, $list2, array('someGroup3' => "data"), false),
            // price inside range, user does not have account (anonymous), payment has groups set - expects true
            array(1, 10, 100, 10, array(), null, "someCountry", false, $list2, array(), true),
            // Shipping country not given, but user address is given
            array(1, 10, 100, 10, array(), "someCountry", null, false, $list2, array(), true),
            array(1, 10, 100, 10, array("someOtherCountry", "someCountry", "andAnotherCountry"), "someCountry", null, false, $list2, array(), true),
            // Shipping country not given and user address is not same as given countries
            array(1, 10, 100, 10, array("someOtherCountry", "andAnotherCountry"), "someCountry", null, true, $list2, array(), false),
        );
    }

    /**
     * Testing PayPal payment validator all cases
     *
     * @dataProvider providerIsPaymentValid_allCases
     */
    public function testIsPaymentValid_allCases($isActivePayment, $rangeFrom, $rangeTo, $price, $paymentCountries, $userCountryId, $userShippingCountryId, $userHasAccount, $paymentUserGroups, $userGroups, $expectedResult)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Payment::class);
        $mockBuilder->setMethods(['getCountries', 'getGroups']);
        $payment = $mockBuilder->getMock();
        $payment->expects($this->any())->method("getCountries")->will($this->returnValue($paymentCountries));
        $payment->expects($this->any())->method("getGroups")->will($this->returnValue($paymentUserGroups));

        $payment->load('oxidpaypal');
        $payment->oxpayments__oxfromamount = new \OxidEsales\Eshop\Core\Field($rangeFrom);
        $payment->oxpayments__oxtoamount = new \OxidEsales\Eshop\Core\Field($rangeTo);
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field($isActivePayment);

        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        $address->oxaddress__oxcountryid = new \OxidEsales\Eshop\Core\Field($userShippingCountryId);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['getUserGroups', 'hasAccount', 'getSelectedAddress', 'getSelectedAddressId']);
        $user = $mockBuilder->getMock();
        $user->expects($this->any())->method("getUserGroups")->will($this->returnValue($userGroups));
        $user->expects($this->any())->method("hasAccount")->will($this->returnValue($userHasAccount));
        $user->expects($this->any())->method("getSelectedAddress")->will($this->returnValue($address));
        $user->expects($this->any())->method("getSelectedAddressId")->will($this->returnValue($userShippingCountryId));

        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($userCountryId);

        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();

        $validator->setConfig($this->getConfig());
        $validator->setPayment($payment);
        $validator->setUser($user);
        $validator->setPrice($price);

        $this->assertEquals($expectedResult, $validator->isPaymentValid());
    }

    /**
     * Testing PayPal payment validator all cases
     *
     */
    public function testIsPaymentValid_NoGroupsAssignedToPayment_True()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Payment::class);
        $mockBuilder->setMethods(['getCountries', 'getGroups']);
        $payment = $mockBuilder->getMock();
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);
        $payment->expects($this->any())->method("getGroups")->will($this->returnValue(array()));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['hasAccount']);
        $user = $mockBuilder->getMock();
        $user->expects($this->any())->method("hasAccount")->will($this->returnValue(1));

        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();

        $validator->setConfig($this->getConfig());
        $validator->setPayment($payment);
        $validator->setUser($user);

        $this->assertEquals(true, $validator->isPaymentValid());
    }

    /**
     * Testing isPaymentValid when iMinOrderPrice is not set and payment price is passed
     */
    public function testIsPaymentActive_MinOrderPriceNotSet_True()
    {
        $payment = new \OxidEsales\Eshop\Application\Model\Payment();
        $payment->oxpayments__oxfromamount = new \OxidEsales\Eshop\Core\Field(0);
        $payment->oxpayments__oxtoamount = new \OxidEsales\Eshop\Core\Field(0);
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);

        $this->getConfig()->setConfigParam('iMinOrderPrice', '');

        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();
        $validator->setConfig($this->getConfig());
        $validator->setPayment($payment);
        $validator->setPrice(50);

        $this->assertEquals(true, $validator->isPaymentValid());
    }

    /**
     * Testing isPaymentValid when iMinOrderPrice is set and payment price is higher
     */
    public function testIsPaymentActive_MinOrderPriceSet_True()
    {
        $payment = new \OxidEsales\Eshop\Application\Model\Payment();
        $payment->oxpayments__oxfromamount = new \OxidEsales\Eshop\Core\Field(0);
        $payment->oxpayments__oxtoamount = new \OxidEsales\Eshop\Core\Field(0);
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);

        $this->getConfig()->setConfigParam('iMinOrderPrice', 10);

        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();
        $validator->setConfig($this->getConfig());
        $validator->setPayment($payment);
        $validator->setPrice(50);

        $this->assertEquals(true, $validator->isPaymentValid());
    }

    /**
     * Testing isPaymentValid when iMinOrderPrice is not set and payment price is lower
     */
    public function testIsPaymentActive_MinOrderPriceSet_False()
    {
        $payment = new \OxidEsales\Eshop\Application\Model\Payment();
        $payment->oxpayments__oxfromamount = new \OxidEsales\Eshop\Core\Field(0);
        $payment->oxpayments__oxtoamount = new \OxidEsales\Eshop\Core\Field(0);
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);

        $this->getConfig()->setConfigParam('iMinOrderPrice', 50);

        $validator = new \OxidEsales\PayPalModule\Model\PaymentValidator();
        $validator->setConfig($this->getConfig());
        $validator->setPayment($payment);
        $validator->setPrice(10);

        $this->assertEquals(false, $validator->isPaymentValid());
    }

    /**
     * @return \OxidEsales\PayPalModule\Model\PaymentValidator
     */
    protected function getPaymentValidator()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class);
        $mockBuilder->setMethods(['hasAccount']);
        $user = $mockBuilder->getMock();
        $user->expects($this->any())->method('hasAccount')->will($this->returnValue(true));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\PaymentValidator::class);
        $mockBuilder->setMethods(
            ['isPaymentActive',
             'getPrice',
             'checkPriceRange',
             'checkMinOrderPrice',
             'getUser',
             'checkUserGroup',
             'checkUserCountry']
        );
        $paymentValidator = $mockBuilder->getMock();
        $paymentValidator->expects($this->any())->method('isPaymentActive')->will($this->returnValue(true));
        $paymentValidator->expects($this->any())->method('getPrice')->will($this->returnValue(true));
        $paymentValidator->expects($this->any())->method('checkPriceRange')->will($this->returnValue(true));
        $paymentValidator->expects($this->any())->method('checkMinOrderPrice')->will($this->returnValue(true));
        $paymentValidator->expects($this->any())->method('getUser')->will($this->returnValue($user));
        $paymentValidator->expects($this->any())->method('checkUserGroup')->will($this->returnValue(true));
        $paymentValidator->expects($this->any())->method('checkUserCountry')->will($this->returnValue(false));

        return $paymentValidator;
    }
}