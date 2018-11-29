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

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal oxAddress class
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Address
 */
class Address extends Address_parent
{
    /**
     * Creates user shipping address from PayPal data and set to session.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $details PayPal data.
     * @param string                                                                    $userId  user id.
     */
    public function createPayPalAddress($details, $userId)
    {
        $addressData = $this->prepareDataPayPalAddress($details);

        if ($addressId = $this->existPayPalAddress($addressData)) {
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable("deladrid", $addressId);
        } else {
            $this->oxaddress__oxuserid = new \OxidEsales\Eshop\Core\Field($userId);
            $this->oxaddress__oxfname = new \OxidEsales\Eshop\Core\Field($addressData['oxfname']);
            $this->oxaddress__oxlname = new \OxidEsales\Eshop\Core\Field($addressData['oxlname']);
            $this->oxaddress__oxstreet = new \OxidEsales\Eshop\Core\Field($addressData['oxstreet']);
            $this->oxaddress__oxstreetnr = new \OxidEsales\Eshop\Core\Field($addressData['oxstreetnr']);
            $this->oxaddress__oxaddinfo = new \OxidEsales\Eshop\Core\Field($addressData['oxaddinfo']);
            $this->oxaddress__oxcity = new \OxidEsales\Eshop\Core\Field($addressData['oxcity']);
            $this->oxaddress__oxcountryid = new \OxidEsales\Eshop\Core\Field($addressData['oxcountryid']);
            $this->oxaddress__oxstateid = new \OxidEsales\Eshop\Core\Field($addressData['oxstateid']);
            $this->oxaddress__oxzip = new \OxidEsales\Eshop\Core\Field($addressData['oxzip']);
            $this->oxaddress__oxfon = new \OxidEsales\Eshop\Core\Field($addressData['oxfon']);
            $this->save();

            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable("deladrid", $this->getId());
        }
    }

    /**
     * Prepare address data array from PayPal response data.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $details - PayPal data
     *
     * @return array
     */
    protected function prepareDataPayPalAddress($details)
    {
        $addressData = array();

        $fullName = oxNew(\OxidEsales\PayPalModule\Core\FullName::class, $details->getShipToName());

        $addressData['oxfname'] = $fullName->getFirstName();
        $addressData['oxlname'] = $fullName->getLastName();

        $street = $this->splitShipToStreetPayPalAddress($details->getShipToStreet());
        $addressData['oxstreet'] = $street['street'];
        $addressData['oxstreetnr'] = $street['streetnr'];

        $addressData['oxcity'] = $details->getShipToCity();

        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $countryId = $country->getIdByCode($details->getShipToCountryCode());
        $addressData['oxcountryid'] = $countryId;

        if ($details->getShipToState()) {
            $state = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
            $stateId = $state->getIdByCode($details->getShipToState(), $countryId);
        }
        $addressData['oxstateid'] = $stateId;

        $addressData['oxzip'] = $details->getShipToZip();
        $addressData['oxfon'] = $details->getShipToPhoneNumber();
        $addressData['oxaddinfo'] = $details->getShipToStreet2();

        return $addressData;
    }

    /**
     * Check required fields.
     *
     * @param array $addressData - PayPal data.
     *
     * @return bool
     */
    protected function checkRequiredFieldsPayPalAddress($addressData)
    {
        $reqFields = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('aMustFillFields');

        $result = true;

        foreach ($reqFields as $field) {
            if (strpos($field, 'oxaddress__') === 0 && empty($addressData[str_replace('oxaddress__', '', $field)])) {
                return false;
            }
        }

        return $result;
    }

    /**
     * Checks if exists PayPal address.
     *
     * @param array $addressData
     *
     * @return bool|string
     */
    protected function existPayPalAddress($addressData)
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

        $query = "SELECT `oxid` FROM `oxaddress` WHERE 1 ";
        $query .= " AND `oxfname` = " . $db->quote($addressData['oxfname']);
        $query .= " AND `oxlname` = " . $db->quote($addressData['oxlname']);
        $query .= " AND `oxstreet` = " . $db->quote($addressData['oxstreet']);
        $query .= " AND `oxstreetnr` = " . $db->quote($addressData['oxstreetnr']);
        $query .= " AND `oxcity` = " . $db->quote($addressData['oxcity']);
        $query .= " AND `oxcountryid` = " . $db->quote($addressData['oxcountryid']);
        $query .= " AND `oxstateid` = " . $db->quote($addressData['oxstateid']);
        $query .= " AND `oxzip` = " . $db->quote($addressData['oxzip']);
        $query .= " AND `oxfon` = " . $db->quote($addressData['oxfon']);

        if ($addressId = $db->getOne($query)) {
            return $addressId;
        }

        return false;
    }

    /**
     *  Split street nr from address
     *
     * @param string $shipToStreet address string
     *
     * @return array
     */
    public function splitShipToStreetPayPalAddress($shipToStreet)
    {
        $address = array();
        $shipToStreet = trim($shipToStreet);

        // checking if street number is at the end of the address
        preg_match("/(.*\S)\s+(\d+\s*\S*)$/", $shipToStreet, $address);

        // checking if street name and number was found
        if (!empty($address[1]) && $address[2]) {
            $address['street'] = $address[1];
            $address['streetnr'] = $address[2];

            return $address;
        }

        // checking if street number is at the begining of the address
        preg_match("/(\d+\S*)\s+(.*)$/", $shipToStreet, $address);

        // checking if street name and number was found
        if (!empty($address[1]) && $address[2]) {
            $address['street'] = $address[2];
            $address['streetnr'] = $address[1];

            return $address;
        }

        // it is not possible to resolve address, so assign it without any parsing
        $address['street'] = $shipToStreet;
        $address['streetnr'] = "";

        return $address;
    }
}
