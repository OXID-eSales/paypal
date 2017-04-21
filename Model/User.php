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

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal oxUser class.
 *
 * @mixin \OxidEsales\Eshop\Application\Model\User
 */
class User extends User_parent
{
    /**
     * CallBack user mode
     *
     * @var bool
     */
    protected $_blCallBackUser = false;

    /**
     * Check if exist real user (with password) for passed email
     *
     * @param string $sUserEmail - email
     *
     * @return bool
     */
    public function isRealPayPalUser($sUserEmail)
    {
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $sQ = "SELECT `oxid` FROM `oxuser` WHERE `oxusername` = " . $oDb->quote($sUserEmail) . " AND `oxpassword` != ''";
        if (!$this->getConfig()->getConfigParam('blMallUsers')) {
            $sQ .= " AND `oxshopid` = " . $oDb->quote($this->getConfig()->getShopId());
        }
        if ($sUserId = $oDb->getOne($sQ)) {
            return $sUserId;
        }

        return false;
    }

    /**
     * Check if the shop user is the same as PayPal user.
     * Fields: first name, last name, street, street nr, city - must be equal.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $oDetails - data returned from PayPal
     *
     * @return bool
     */
    public function isSamePayPalUser($oDetails)
    {
        $aUserData = array();
        $aUserData[] = getStr()->html_entity_decode($this->oxuser__oxfname->value);
        $aUserData[] = getStr()->html_entity_decode($this->oxuser__oxlname->value);

        $aCompareData = array();
        $aCompareData[] = $oDetails->getFirstName();
        $aCompareData[] = $oDetails->getLastName();

        return (($aUserData == $aCompareData) && $this->isSameAddressPayPalUser($oDetails));
    }

    /**
     * Check if the shop user address is the same in PayPal.
     * Fields: street, street nr, city - must be equal.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $oDetails - data returned from PayPal
     *
     * @return bool
     */
    public function isSameAddressPayPalUser($oDetails)
    {
        $aUserData = array();
        $aUserData[] = getStr()->html_entity_decode($this->oxuser__oxstreet->value);
        $aUserData[] = getStr()->html_entity_decode($this->oxuser__oxstreetnr->value);
        $aUserData[] = getStr()->html_entity_decode($this->oxuser__oxcity->value);

        $aStreet = $this->_splitShipToStreetPayPalUser($oDetails->getShipToStreet());

        $aCompareData = array();
        $aCompareData[] = $aStreet['street'];
        $aCompareData[] = $aStreet['streetnr'];
        $aCompareData[] = $oDetails->getShipToCity();

        return $aUserData == $aCompareData;
    }

    /**
     * Check if the shop user address user name is the same in PayPal.
     * Fields: name, lname.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $oDetails - data returned from PayPal
     *
     * @return bool
     */
    public function isSameAddressUserPayPalUser($oDetails)
    {
        $aFullUserName = getStr()->html_entity_decode($this->oxuser__oxfname->value) . ' ' . getStr()->html_entity_decode($this->oxuser__oxlname->value);

        return $aFullUserName == $oDetails->getShipToName();
    }

    /**
     * Returns user from session associated with current PayPal order.
     *
     * @return bool
     */
    public function loadUserPayPalUser()
    {
        if (($sUserId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable("oepaypal-userId"))) {
            return $this->load($sUserId);
        }
    }

    /**
     * Creates user from PayPal data.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $oPayPalData Data returned from PayPal.
     */
    public function createPayPalUser($oPayPalData)
    {
        $aUserData = $this->_prepareDataPayPalUser($oPayPalData);

        $sUserId = $this->getIdByUserName($oPayPalData->getEmail());
        if ($sUserId) {
            $this->load($sUserId);
        }

        $this->oxuser__oxactive = new \OxidEsales\Eshop\Core\Field(1);
        $this->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field($oPayPalData->getEmail());
        $this->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field($aUserData['oxfname']);
        $this->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field($aUserData['oxlname']);
        $this->oxuser__oxfon = new \OxidEsales\Eshop\Core\Field($aUserData['oxfon']);
        $this->oxuser__oxsal = new \OxidEsales\Eshop\Core\Field($aUserData['oxsal']);
        $this->oxuser__oxcompany = new \OxidEsales\Eshop\Core\Field($aUserData['oxcompany']);
        $this->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field($aUserData['oxstreet']);
        $this->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field($aUserData['oxstreetnr']);
        $this->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field($aUserData['oxcity']);
        $this->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field($aUserData['oxzip']);
        $this->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($aUserData['oxcountryid']);
        $this->oxuser__oxstateid = new \OxidEsales\Eshop\Core\Field($aUserData['oxstateid']);
        $this->oxuser__oxaddinfo = new \OxidEsales\Eshop\Core\Field($aUserData['oxaddinfo']);

        if ($this->save()) {
            $this->_setAutoGroups($this->oxuser__oxcountryid->value);

            // and adding to group "oxidnotyetordered"
            $this->addToGroup("oxidnotyetordered");
        }
    }

    /**
     * Prepare address data array from PayPal response data.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $oPayPalData PayPal data.
     *
     * @return array
     */
    protected function _prepareDataPayPalUser($oPayPalData)
    {
        $aUserData = array();

        $oFullName = oxNew(\OxidEsales\PayPalModule\Core\FullName::class, $oPayPalData->getShipToName());

        $aUserData['oxfname'] = $oFullName->getFirstName();
        $aUserData['oxlname'] = $oFullName->getLastName();

        $aStreet = $this->_splitShipToStreetPayPalUser($oPayPalData->getShipToStreet());
        $aUserData['oxstreet'] = $aStreet['street'];
        $aUserData['oxstreetnr'] = $aStreet['streetnr'];

        $aUserData['oxcity'] = $oPayPalData->getShipToCity();

        $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $sCountryId = $oCountry->getIdByCode($oPayPalData->getShipToCountryCode());
        $aUserData['oxcountryid'] = $sCountryId;

        $sStateId = '';
        if ($oPayPalData->getShipToState()) {
            $oState = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
            $sStateId = $oState->getIdByCode($oPayPalData->getShipToState(), $sCountryId);
        }
        $aUserData['oxstateid'] = $sStateId;

        $aUserData['oxzip'] = $oPayPalData->getShipToZip();
        $aUserData['oxfon'] = $oPayPalData->getShipToPhoneNumber();
        $aUserData['oxaddinfo'] = $oPayPalData->getShipToStreet2();
        $aUserData['oxsal'] = $oPayPalData->getSalutation();
        $aUserData['oxcompany'] = $oPayPalData->getBusiness();

        return $aUserData;
    }

    /**
     * Check required fields.
     *
     * @param array $aAddressData PayPal data.
     *
     * @return bool
     */
    protected function _checkRequiredFieldsPayPalUser($aAddressData)
    {
        $aReqFields = $this->getConfig()->getConfigParam('aMustFillFields');
        $blResult = true;

        foreach ($aReqFields as $sField) {
            if (strpos($sField, 'oxuser__') === 0 && empty($aAddressData[str_replace('oxuser__', '', $sField)])) {
                return false;
            }
        }

        return $blResult;
    }

    /**
     * Split street nr from address.
     *
     * @param string $sShipToStreet Address string.
     *
     * @return array
     */
    protected function _splitShipToStreetPayPalUser($sShipToStreet)
    {
        $oAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);

        return $oAddress->splitShipToStreetPayPalAddress($sShipToStreet);
    }

    /**
     * Returns true if user has callback state.
     *
     * @return bool
     */
    public function isCallBackUserPayPalUser()
    {
        return $this->_blCallBackUser;
    }

    /**
     * Returns user group list.
     *
     * @param string $sOxId oxId identifier.
     *
     * @return \OxidEsales\Eshop\Core\Model\ListModel
     */
    public function getUserGroups($sOxId = null)
    {
        if (!$this->isCallBackUserPayPalUser()) {
            return parent::getUserGroups();
        }

        if (!$this->_oGroups) {
            $sViewName = getViewName("oxgroups");
            $sSelect = "select {$sViewName}.* from {$sViewName} where ({$sViewName}.oxid = 'oxidnotyetordered' OR {$sViewName}.oxid = 'oxidnewcustomer')";
            $this->_oGroups = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class, \OxidEsales\Eshop\Application\Model\Groups::class);
            $this->_oGroups->selectString($sSelect);
        }

        return $this->_oGroups;
    }

    /**
     * Initializes call back user.
     *
     * @param array $aPayPalData Callback user data.
     */
    public function initializeUserForCallBackPayPalUser($aPayPalData)
    {
        // setting mode..
        $this->_blCallBackUser = true;

        // setting data..
        $aStreet = $this->_splitShipToStreetPayPalUser($aPayPalData['SHIPTOSTREET']);

        // setting object id as it is requested later while processing user object
        $this->setId(\OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUID());

        $this->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field($aStreet['street']);
        $this->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field($aStreet['streetnr']);
        $this->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field($aPayPalData['SHIPTOCITY']);
        $this->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field($aPayPalData['SHIPTOZIP']);

        $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $sCountryId = $oCountry->getIdByCode($aPayPalData["SHIPTOCOUNTRY"]);
        $this->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($sCountryId);

        $sStateId = '';
        if (isset($aPayPalData["SHIPTOSTATE"])) {
            $oState = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
            $sStateId = $oState->getIdByCode($aPayPalData["SHIPTOSTATE"], $sCountryId);
        }
        $this->oxuser__oxstateid = new \OxidEsales\Eshop\Core\Field($sStateId);
    }
}
