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

namespace OxidEsales\PayPalModule\Model\Response;

/**
 * PayPal response class for get express checkout details
 */
class ResponseGetExpressCheckoutDetails extends \OxidEsales\PayPalModule\Model\Response\Response
{
    /**
     * Return internal/system name of a shipping option.
     *
     * @return string
     */
    public function getShippingOptionName()
    {
        return $this->getValue('SHIPPINGOPTIONNAME');
    }

    /**
     * Return price amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return ( float ) $this->getValue('PAYMENTREQUEST_0_AMT');
    }

    /**
     * Return payer id.
     *
     * @return string
     */
    public function getPayerId()
    {
        return $this->getValue('PAYERID');
    }

    /**
     * Return email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getValue('EMAIL');
    }

    /**
     * Return first name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getValue('FIRSTNAME');
    }

    /**
     * Return last name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getValue('LASTNAME');
    }

    /**
     * Return shipping street.
     *
     * @return string
     */
    public function getShipToStreet()
    {
        return $this->getValue('PAYMENTREQUEST_0_SHIPTOSTREET');
    }

    /**
     * Return shipping city.
     *
     * @return string
     */
    public function getShipToCity()
    {
        return $this->getValue('PAYMENTREQUEST_0_SHIPTOCITY');
    }

    /**
     * Return name.
     *
     * @return string
     */
    public function getShipToName()
    {
        return $this->getValue('PAYMENTREQUEST_0_SHIPTONAME');
    }

    /**
     * Return shipping country.
     *
     * @return string
     */
    public function getShipToCountryCode()
    {
        return $this->getValue('PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE');
    }

    /**
     * Return shipping state.
     *
     * @return string
     */
    public function getShipToState()
    {
        return $this->getValue('PAYMENTREQUEST_0_SHIPTOSTATE');
    }

    /**
     * Return shipping zip code.
     *
     * @return string
     */
    public function getShipToZip()
    {
        return $this->getValue('PAYMENTREQUEST_0_SHIPTOZIP');
    }

    /**
     * Return phone number.
     * Note: PayPal returns a contact phone number only if your
     *       Merchant Account Profile settings require that the buyer enter one.
     *
     * @return string
     */
    public function getShipToPhoneNumber()
    {
        $value = $this->getValue('PAYMENTREQUEST_0_SHIPTOPHONENUM');
        $requiredAddressFields = oxNew(\OxidEsales\Eshop\Application\Model\RequiredAddressFields::class);

        if (in_array('oxuser__oxfon', $requiredAddressFields->getRequiredFields())) {
            $phone = $this->getValue('PHONENUM');
            $value = !empty($phone) ? $phone : $value;
        }
        return $value;
    }

    /**
     * Return second shipping street.
     *
     * @return string
     */
    public function getShipToStreet2()
    {
        return $this->getValue('PAYMENTREQUEST_0_SHIPTOSTREET2');
    }

    /**
     * Return salutation.
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->getValue('SALUTATION');
    }

    /**
     * Returns company.
     *
     * @return string
     */
    public function getBusiness()
    {
        return $this->getValue('BUSINESS');
    }
}
