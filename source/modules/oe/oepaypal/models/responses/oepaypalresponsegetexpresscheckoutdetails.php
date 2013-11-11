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
 * @copyright (C) OXID eSales AG 2003-2013
 */

/**
 * PayPal response class for get express checkout details
 */
class oePayPalResponseGetExpressCheckoutDetails extends oePayPalResponse
{

    /**
     * Return internal/system name of a shipping option.
     *
     * @return string
     */
    public function getShippingOptionName()
    {
        return $this->_getValue( 'SHIPPINGOPTIONNAME' );
    }

    /**
     * Return price amount
     *
     * @return string
     */
    public function getAmount()
    {
        return ( float )$this->_getValue( 'PAYMENTREQUEST_0_AMT' );
    }

    /**
     * Return payer id
     *
     * @return string
     */
    public function getPayerId()
    {
        return $this->_getValue( 'PAYERID' );
    }

    /**
     * Return email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_getValue( 'EMAIL' );
    }

    /**
     * Return first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->_getValue( 'FIRSTNAME' );
    }

    /**
     * Return last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->_getValue( 'LASTNAME' );
    }

    /**
     * Return shipping street
     *
     * @return string
     */
    public function getShipToStreet()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTOSTREET' );
    }

    /**
     * Return shipping city
     *
     * @return string
     */
    public function getShipToCity()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTOCITY' );
    }

    /**
     * Return name
     *
     * @return string
     */
    public function getShipToName()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTONAME' );
    }

    /**
     * Return shipping country
     *
     * @return string
     */
    public function getShipToCountryCode()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' );
    }

    /**
     * Return shipping state
     *
     * @return string
     */
    public function getShipToState()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTOSTATE' );
    }

    /**
     * Return shipping zip code
     *
     * @return string
     */
    public function getShipToZip()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTOZIP' );
    }

    /**
     * Return phone number
     *
     * @return string
     */
    public function getShipToPhoneNumber()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTOPHONENUM' );
    }

    /**
     * Return second shipping street
     *
     * @return string
     */
    public function getShipToStreet2()
    {
        return $this->_getValue( 'PAYMENTREQUEST_0_SHIPTOSTREET2' );
    }

    /**
     * Return salutation
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->_getValue( 'SALUTATION' );
    }

    /**
     * Returns company
     *
     * @return string
     */
    public function getBusiness()
    {
        return $this->_getValue( 'BUSINESS' );
    }

}