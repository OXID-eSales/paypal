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

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';

/**
 * Testing oePayPalResponseGetExpressCheckoutDetails class.
 */
class Unit_oePayPal_models_responses_oePayPalResponseGetExpressCheckoutDetailsTest extends OxidTestCase
{
    /**
     * Returns response data
     *
     * @return array
     */
    protected function _getResponseData()
    {
        $aData = array(
            'SHIPPINGOPTIONNAME'                 => 'Air',
            'PAYMENTREQUEST_0_AMT'               => 1200,
            'PAYERID'                            => 'payer',
            'EMAIL'                              => 'oxid@oxid.com',
            'FIRSTNAME'                          => 'Name',
            'LASTNAME'                           => 'Surname',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Street',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'City',
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'First Last',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'CountryCode',
            'PAYMENTREQUEST_0_SHIPTOSTATE'       => 'State',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => '1121',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => '+37000000000',
            'PAYMENTREQUEST_0_SHIPTOSTREET2'     => 'Street2',
            'SALUTATION'                         => 'this is salutation',
            'BUSINESS'                           => 'company'
        );

        return $aData;
    }

    /**
     * Test getting shipping option name
     */
    public function testGetShippingOptionName()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('Air', $oResponse->getShippingOptionName());
    }

    /**
     * Test getting token
     */
    public function testGetAmount()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals(1200, $oResponse->getAmount());
    }

    /**
     * Test getting payer id
     */
    public function testGetPayerId()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('payer', $oResponse->getPayerId());
    }

    /**
     * Test getting email
     */
    public function testGetEmail()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('oxid@oxid.com', $oResponse->getEmail());
    }

    /**
     * Test getting first name
     */
    public function testGetFirstName()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('Name', $oResponse->getFirstName());
    }

    /**
     * Test getting last name
     */
    public function testGetLastName()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('Surname', $oResponse->getLastName());
    }

    /**
     * Test getting street
     */
    public function testGetShipToStreet()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('Street', $oResponse->getShipToStreet());
    }

    /**
     * Test getting city
     */
    public function testGetShipToCity()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('City', $oResponse->getShipToCity());
    }

    /**
     * Test getting name
     */
    public function testGetShipToName()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('First Last', $oResponse->getShipToName());
    }

    /**
     * Test getting country code
     */
    public function testGetShipToCountryCode()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('CountryCode', $oResponse->getShipToCountryCode());
    }

    /**
     * Test getting state
     */
    public function testGetShipToState()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('State', $oResponse->getShipToState());
    }

    /**
     * Test getting state
     */
    public function testGetShipToZip()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('1121', $oResponse->getShipToZip());
    }

    /**
     * Test getting phone number
     */
    public function testGetShipToPhoneNumber()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('+37000000000', $oResponse->getShipToPhoneNumber());
    }

    /**
     * Test getting phone number
     */
    public function testGetShipToStreet2()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('Street2', $oResponse->getShipToStreet2());
    }

    /**
     * Test getting salutation
     */
    public function testGetSalutation()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('this is salutation', $oResponse->getSalutation());
    }

    /**
     * Test getting company name
     */
    public function testGetBusiness()
    {
        $oResponse = new oePayPalResponseGetExpressCheckoutDetails();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('company', $oResponse->getBusiness());
    }
}
