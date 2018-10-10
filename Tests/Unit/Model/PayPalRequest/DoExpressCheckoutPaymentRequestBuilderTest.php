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

namespace OxidEsales\PayPalModule\Tests\Unit\Model\PayPalRequest;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;

/**
 * Testing \OxidEsales\PayPalModule\Model\PayPalRequest\DoExpressCheckoutPaymentRequestBuilder class.
 */
class DoExpressCheckoutPaymentRequestBuilderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    public function providerDoExpressCheckoutPayment()
    {
        $facts = new \OxidEsales\Facts\Facts();
        $buttonSource = 'OXID_Cart_CommunityECS';

        if ('EE' == $facts->getEdition()) {
            $buttonSource = 'OXID_Cart_EnterpriseECS';
        }
        if ('PE' == $facts->getEdition()) {
            $buttonSource = 'OXID_Cart_ProfessionalECS';
        }

        $data = [
            'standard_checkout' => [\OxidEsales\PayPalModule\Core\Config::OEPAYPAL_ECS, $buttonSource],
            'shortcut'          => [\OxidEsales\PayPalModule\Core\Config::OEPAYPAL_SHORTCUT, 'Oxid_Cart_ECS_Shortcut']
        ];

        return $data;
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalRequest\DoExpressCheckoutPaymentRequestBuilder::buildRequest.
     *
     * @dataProvider providerDoExpressCheckoutPayment
     *
     * @param int    $trigger      Mark if payment triggered by shortcut or by standard checkout.
     * @param string $buttonSource Expected partnercode/BUTTONSOURCE
     */
    public function testDoExpressCheckoutPayment($trigger, $buttonSource)
    {
        // preparing session, inputs etc.
        $result["PAYMENTINFO_0_TRANSACTIONID"] = "321";

        // preparing price
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class);
        $mockBuilder->setMethods(['getBruttoPrice']);
        $price = $mockBuilder->getMock();
        $price->expects($this->once())->method("getBruttoPrice")->will($this->returnValue(123));

        // preparing basket
        $mockBuilder = $this->getMockBuilder(Basket::class);
        $mockBuilder->setMethods(['getPrice']);
        $basket = $mockBuilder->getMock();
        $basket->expects($this->once())->method("getPrice")->will($this->returnValue($price));

        // preparing session
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class);
        $mockBuilder->setMethods(['getBasket']);
        $session = $mockBuilder->getMock();
        $session->expects($this->any())->method("getBasket")->will($this->returnValue($basket));
        $session->setVariable("oepaypal-token", "111");
        $session->setVariable("oepaypal-payerId", "222");
        $session->setVariable(\OxidEsales\PayPalModule\Core\Config::OEPAYPAL_TRIGGER_NAME, $trigger);

        // preparing config
        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();

        // preparing order
        $payPalOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $payPalOrder->oxorder__oxordernr = new \OxidEsales\Eshop\Core\Field("123");

        $user = oxNew(User::class);
        $user->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field('firstname');
        $user->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field('lastname');
        $user->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field('some street');
        $user->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field('47');
        $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field('some city');
        $user->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field('zip');

        $subj = sprintf(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_ORDER_CONF_SUBJECT"), $payPalOrder->oxorder__oxordernr->value);

        $config = $this->getConfig();

        $expectedResult = array(
            'TOKEN'                              => '111',
            'PAYERID'                            => '222',
            'PAYMENTREQUEST_0_PAYMENTACTION'     => 'Sale',
            'PAYMENTREQUEST_0_AMT'               => 123,
            'PAYMENTREQUEST_0_CURRENCYCODE'      => "EUR",
            'PAYMENTREQUEST_0_NOTIFYURL'         => $this->getConfig()->getCurrentShopUrl() . "index.php?cl=oepaypalipnhandler&fnc=handleRequest&shp=" . $config->getShopId(),
            'PAYMENTREQUEST_0_DESC'              => $subj,
            'PAYMENTREQUEST_0_CUSTOM'            => $subj,
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'firstname lastname',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'some street 47',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'some city',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'zip',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => '',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => '',
        );

        $expectedResult['BUTTONSOURCE'] = $buttonSource;

        // testing
        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\DoExpressCheckoutPaymentRequestBuilder();
        $builder->setPayPalConfig($payPalConfig);
        $builder->setSession($session);
        $builder->setBasket($basket);
        $builder->setOrder($payPalOrder);
        $builder->setTransactionMode('Sale');
        $builder->setUser($user);

        $request = $builder->buildRequest();
        $this->assertEquals($expectedResult, $request->getData());
    }

    public function testAddAddressParams_SelectedAddressIdNotSet_TakeInfoFromUser()
    {
        $expectedParams = array(
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'FirstName LastName',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Street StreetNr',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'City',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'Zip',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => 'PhoneNum',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => null,
        );

        $userMethodValues = array(
            'getSelectedAddressId' => null,
        );
        $user = $this->_createStub(\OxidEsales\Eshop\Application\Model\User::class, $userMethodValues);
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.com');
        $user->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field('FirstName');
        $user->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field('LastName');
        $user->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field('Street');
        $user->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field('StreetNr');
        $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field('City');
        $user->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field('Zip');
        $user->oxuser__oxfon = new \OxidEsales\Eshop\Core\Field('PhoneNum');
        $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field('City');

        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\DoExpressCheckoutPaymentRequestBuilder();
        $builder->setUser($user);
        $builder->addAddressParams();

        $this->assertArraysEqual($expectedParams, $builder->getRequest()->getData());
    }

    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    protected function assertArraysEqual($expected, $result)
    {
        $this->assertArraysContains($expected, $result);
        $this->assertEquals(count($expected), count($result));
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    protected function assertArraysContains($expected, $result)
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result, "Key not found: $key");
            $this->assertEquals($value, $result[$key], "Key '$key' value is not equal to '$value'");
        }
    }
}
