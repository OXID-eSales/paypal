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

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Driver\Result;
use OxidEsales\Eshop\Core\Exception\StandardException as EshopStandardException;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails;

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
    protected $callBackUser = false;

    /**
     * Check if exist real user (with password) for passed email
     *
     * @param string $userEmail - email
     *
     * @return bool
     */
    public function isRealPayPalUser($userEmail)
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $query = "SELECT `oxid` FROM `oxuser` WHERE `oxusername` = " . $db->quote($userEmail) . " AND `oxpassword` != ''";
        if (!\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('blMallUsers')) {
            $query .= $this->getShopIdQueryPart();
        }
        if ($userId = $db->getOne($query)) {
            return $userId;
        }

        return false;
    }

    /**
     * Check if the shop user is the same as PayPal user.
     * Fields: first name, last name, street, street nr, city - must be equal.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $details - data returned from PayPal
     *
     * @return bool
     */
    public function isSamePayPalUser($details)
    {
        $userData = array();
        $userData[] = \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($this->oxuser__oxfname->value);
        $userData[] = \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($this->oxuser__oxlname->value);

        $compareData = array();
        $compareData[] = $details->getFirstName();
        $compareData[] = $details->getLastName();

        return (($userData == $compareData) && $this->isSameAddressPayPalUser($details));
    }

    /**
     * Check if the shop user address is the same in PayPal.
     * Fields: street, street nr, city - must be equal.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $details - data returned from PayPal
     *
     * @return bool
     */
    public function isSameAddressPayPalUser($details)
    {
        $userData = array();
        $userData[] = \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($this->oxuser__oxstreet->value);
        $userData[] = \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($this->oxuser__oxstreetnr->value);
        $userData[] = \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($this->oxuser__oxcity->value);

        $street = $this->splitShipToStreetPayPalUser($details->getShipToStreet());

        $compareData = array();
        $compareData[] = $street['street'];
        $compareData[] = $street['streetnr'];
        $compareData[] = $details->getShipToCity();

        return $userData == $compareData;
    }

    /**
     * Check if the shop user address user name is the same in PayPal.
     * Fields: name, lname.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $details - data returned from PayPal
     *
     * @return bool
     */
    public function isSameAddressUserPayPalUser($details)
    {
        $fullUserName = \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($this->oxuser__oxfname->value) . ' ' . \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($this->oxuser__oxlname->value);

        return $fullUserName == $details->getShipToName();
    }

    /**
     * Returns user from session associated with current PayPal order.
     *
     * @return bool
     */
    public function loadUserPayPalUser()
    {
        $result = false;
        if (($userId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable("oepaypal-userId"))) {
            $result = $this->load($userId);
        }

        return $result;
    }

    /**
     * Creates user from PayPal data.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $payPalData Data returned from PayPal.
     */
    public function createPayPalUser($payPalData)
    {
        $userData = $this->prepareDataPayPalUser($payPalData);

        $userId = $this->getIdByUserName($payPalData->getEmail());
        if ($userId) {
            $this->load($userId);
        }

        $this->oxuser__oxactive = new \OxidEsales\Eshop\Core\Field(1);
        $this->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field($payPalData->getEmail());
        $this->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field($userData['oxfname']);
        $this->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field($userData['oxlname']);
        $this->oxuser__oxfon = new \OxidEsales\Eshop\Core\Field($userData['oxfon']);
        $this->oxuser__oxsal = new \OxidEsales\Eshop\Core\Field($userData['oxsal']);
        $this->oxuser__oxcompany = new \OxidEsales\Eshop\Core\Field($userData['oxcompany']);
        $this->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field($userData['oxstreet']);
        $this->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field($userData['oxstreetnr']);
        $this->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field($userData['oxcity']);
        $this->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field($userData['oxzip']);
        $this->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($userData['oxcountryid']);
        $this->oxuser__oxstateid = new \OxidEsales\Eshop\Core\Field($userData['oxstateid']);
        $this->oxuser__oxaddinfo = new \OxidEsales\Eshop\Core\Field($userData['oxaddinfo']);
        $this->oxuser__oxpassword = new \OxidEsales\Eshop\Core\Field('');
        $this->oxuser__oxbirthdate = new \OxidEsales\Eshop\Core\Field('');

        if ($this->save()) {
            $this->_setAutoGroups($this->oxuser__oxcountryid->value);

            // and adding to group "oxidnotyetordered"
            $this->addToGroup("oxidnotyetordered");
        }
    }

    /**
     * Prepare address data array from PayPal response data.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $payPalData PayPal data.
     *
     * @return array
     */
    protected function prepareDataPayPalUser($payPalData)
    {
        $userData = array();

        $fullName = oxNew(\OxidEsales\PayPalModule\Core\FullName::class, $payPalData->getShipToName());

        $userData['oxfname'] = $fullName->getFirstName();
        $userData['oxlname'] = $fullName->getLastName();

        $street = $this->splitShipToStreetPayPalUser($payPalData->getShipToStreet());
        $userData['oxstreet'] = $street['street'];
        $userData['oxstreetnr'] = $street['streetnr'];

        $userData['oxcity'] = $payPalData->getShipToCity();

        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $countryId = $country->getIdByCode($payPalData->getShipToCountryCode());
        $userData['oxcountryid'] = $countryId;

        $stateId = '';
        if ($payPalData->getShipToState()) {
            $state = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
            $stateId = $state->getIdByCode($payPalData->getShipToState(), $countryId);
        }
        $userData['oxstateid'] = $stateId;

        $userData['oxzip'] = $payPalData->getShipToZip();
        $userData['oxfon'] = $payPalData->getShipToPhoneNumber();
        $userData['oxaddinfo'] = $payPalData->getShipToStreet2();
        $userData['oxsal'] = $payPalData->getSalutation();
        $userData['oxcompany'] = $payPalData->getBusiness();

        return $userData;
    }

    /**
     * Check required fields.
     *
     * @param array $addressData PayPal data.
     *
     * @return bool
     */
    protected function checkRequiredFieldsPayPalUser($addressData)
    {
        $reqFields = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('aMustFillFields');
        $result = true;

        foreach ($reqFields as $field) {
            if (strpos($field, 'oxuser__') === 0 && empty($addressData[str_replace('oxuser__', '', $field)])) {
                return false;
            }
        }

        return $result;
    }

    /**
     * Split street nr from address.
     *
     * @param string $shipToStreet Address string.
     *
     * @return array
     */
    protected function splitShipToStreetPayPalUser($shipToStreet)
    {
        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);

        return $address->splitShipToStreetPayPalAddress($shipToStreet);
    }

    /**
     * Returns true if user has callback state.
     *
     * @return bool
     */
    public function isCallBackUserPayPalUser()
    {
        return $this->callBackUser;
    }

    /**
     * Returns user group list.
     *
     * @param string $oxId oxId identifier.
     *
     * @return \OxidEsales\Eshop\Core\Model\ListModel
     */
    public function getUserGroups($oxId = null)
    {
        if (!$this->isCallBackUserPayPalUser()) {
            return parent::getUserGroups();
        }

        if (!$this->_oGroups) {
            /** @var \OxidEsales\Eshop\Core\TableViewNameGenerator $viewNameGenerator */
            $viewNameGenerator = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
            $viewName = $viewNameGenerator->getViewName("oxgroups");
            $select = "select {$viewName}.* from {$viewName} where ({$viewName}.oxid = 'oxidnotyetordered' OR {$viewName}.oxid = 'oxidnewcustomer')";
            $this->_oGroups = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class, \OxidEsales\Eshop\Application\Model\Groups::class);
            $this->_oGroups->selectString($select);
        }

        return $this->_oGroups;
    }

    /**
     * Initializes call back user.
     *
     * @param array $payPalData Callback user data.
     */
    public function initializeUserForCallBackPayPalUser($payPalData)
    {
        // setting mode..
        $this->callBackUser = true;

        // setting data..
        $street = $this->splitShipToStreetPayPalUser($payPalData['SHIPTOSTREET']);

        // setting object id as it is requested later while processing user object
        $this->setId(\OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUID());

        $this->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field($street['street']);
        $this->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field($street['streetnr']);
        $this->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field($payPalData['SHIPTOCITY']);
        $this->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field($payPalData['SHIPTOZIP']);

        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $countryId = $country->getIdByCode($payPalData["SHIPTOCOUNTRY"]);
        $this->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($countryId);

        $stateId = '';
        if (isset($payPalData["SHIPTOSTATE"])) {
            $state = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
            $stateId = $state->getIdByCode($payPalData["SHIPTOSTATE"], $countryId);
        }
        $this->oxuser__oxstateid = new \OxidEsales\Eshop\Core\Field($stateId);
    }

    public function setAnonymousUserId(string $userId): bool
    {
        $this->assign(
            [
                'OEPAYPAL_ANON_USERID' => $userId,
            ]
        );

        return (bool) $this->save();
    }

    public function getAnonymousId(string $userId): string
    {
        if (empty($userId)) {
            return '';
        }

        $queryBuilder = ContainerFactory::getInstance()
            ->getContainer()
            ->get(QueryBuilderFactoryInterface::class)
            ->create();

        $queryBuilder->select('oxuser.oxid')
            ->from('oxuser')
            ->where('(oxshopid = :shopid)')
            ->andWhere('(oxuser.OEPAYPAL_ANON_USERID = :userid)')
            ->setParameters([
                ':userid' => $userId,
                ':shopid' => \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId()
            ]);

        $result = $queryBuilder->execute()->fetch(FetchMode::COLUMN);
        $id = $result ?: $userId;

        return $id;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function load($id)
    {
        if (!parent::load($id)) {
            return !empty($id) ? parent::load($this->getAnonymousId($id)) : false;
        }

        return true;
    }

    public function hasNoInvoiceAddress(): bool
    {
        return $this->getFieldData('oxusername') === $this->_getMergedAddressFields();
    }

    public function setGroupsAfterUserCreation(): void
    {
        $this->_setAutoGroups($this->oxuser__oxcountryid->value);

        // and adding to group "oxidnotyetordered"
        $this->addToGroup("oxidnotyetordered");
    }

    public function setInvoiceDataFromPayPalResult(ResponseGetExpressCheckoutDetails $payPalData): void
    {
        //doublecheck
        if (!$this->hasNoInvoiceAddress()) {
            $exception = new EshopStandardException();
            $exception->setMessage('OEPAYPAL_ERROR_USER_ADDRESS');
            throw $exception;
        }

        $this->assign($this->prepareDataPayPalUser($payPalData));
        $this->save();
        $this->setGroupsAfterUserCreation();
    }

    /**
     * Create query part for selecting by shopid.
     *
     * @return string
     */
    protected function getShopIdQueryPart()
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        return  " AND `oxshopid` = " . $db->quote(\OxidEsales\Eshop\Core\Registry::getConfig()->getShopId());
    }
}
