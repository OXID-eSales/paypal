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
 * Basket constructor
 *
 */
class oePayPalShopConstruct
{
    /**
     * @var oxUser
     */
    protected $_oParams = null;

    /**
     * @var oxUser
     */
    protected $_oUser = null;

    /**
     * @var null
     */
    protected $_oGroups = null;

    /**
     * Sets constructor parameters
     *
     * @param array $oParams
     */
    public function setParams($oParams)
    {
        $this->_oParams = $oParams;
        $this->setConfigParameters();
        $this->setSessionParameters();
        $this->setRequestParameters();
        $this->setServerParameters();
    }

    /**
     * Sets constructor parameters
     */
    public function getParams($sKey = null)
    {
        if (!is_null($sKey)) {
            return $this->_oParams[$sKey];
        }

        return $this->_oParams;
    }

    /**
     * @param oxUser $oUser
     */
    public function setUser($oUser)
    {
        $this->_oUser = $oUser;
    }

    /**
     * @return oxUser
     */
    public function getUser()
    {
        if (is_null($this->_oUser)) {
            $aUser = $this->getParams('user');
            if ($aUser === false) {
                return null;
            }
            if (!$aUser) {
                $aUser = $this->_getDefaultUserData();
            }
            $this->setUser($this->_createUser($aUser));
        }

        return $this->_oUser;
    }

    /**
     * @param oxUser $oGroups
     */
    public function setGroups($oGroups)
    {
        $this->_oGroups = $oGroups;
    }

    /**
     * @return oxUser
     */
    public function getGroups()
    {
        if (is_null($this->_oGroups)) {
            $aGroups = $this->getParams('groups');
            if ($aGroups === false) {
                return null;
            }
            if (!$aGroups) {
                $aGroups = $this->_getDefaultGroupsData();
            }
            $this->setGroups($this->createGroups($aGroups));
        }

        return $this->_oGroups;
    }

    /**
     * Set config options
     */
    public function setConfigParameters()
    {
        $oConfig = modConfig::getInstance();
        $aParams = $this->getParams('config');
        if (!empty($aParams)) {
            foreach ($aParams as $sKey => $sValue) {
                $oConfig->setConfigParam($sKey, $sValue);
            }
        }
    }

    /**
     * Set config options
     */
    public function setSessionParameters()
    {
        $oSession = oxRegistry::getSession();
        $aParams = $this->getParams('session');
        if (is_array($aParams)) {
            foreach ($aParams as $sName => $sValue) {
                $oSession->setVariable($sName, $sValue);
            }
        }
    }

    /**
     * Set config options
     */
    public function setRequestParameters()
    {
        $aParams = $this->getParams('requestToShop');
        if (is_array($aParams)) {
            $_POST = $aParams;
            $_GET = $aParams;
            $_COOKIE = $aParams;
            $_REQUEST = $aParams;
        }
    }

    /**
     * Set config options
     */
    public function setServerParameters()
    {
        if ($serverParams = $this->getParams('serverParams')) {
            foreach ($serverParams as $sKey => $mValue) {
                $_SERVER[$sKey] = $mValue;
            }
        }
    }

    /**
     * Returns prepared basket, user and config.
     *
     * @param array $aParams test data to create mocks and what expect in return.
     *
     * @return oxBasket
     */
    public function getBasket()
    {
        $this->_createCats($this->getParams('categories'));
        $this->_setDiscounts($this->getParams('discounts'));
        $sTSProductId = $this->_setTrustedShop($this->getParams('trustedshop'));

        $aCosts = $this->getParams('costs');
        $sDeliverySetId = $this->_setDeliveryCosts($aCosts['delivery']);
        $aPayment = $this->_setPayments($aCosts['payment']);
        $aVoucherIDs = $this->_setVouchers($aCosts['voucherserie']);

        $oBasket = new oePayPalOxBasket();

        $oBasket->setBasketUser($this->getUser());

        $this->getGroups();

        $aArtsForBasket = $this->createArticles($this->getParams('articles'));
        $aWrap = $this->_setWrappings($aCosts['wrapping']);
        foreach ($aArtsForBasket as $aArt) {
            if (!$aArt['amount']) {
                continue;
            }
            $oItem = $oBasket->addToBasket($aArt['id'], $aArt['amount']);

            if (!empty($aWrap)) {
                $oItem->setWrapping($aWrap[$aArt['id']]);
            }
        }

        $aWrap['card'] ? $oBasket->setCardId($aWrap['card']) : '';

        if (!empty($sDeliverySetId) && !$aCosts['delivery']['oxdeliveryset']['createOnly']) {
            $oBasket->setShipping($sDeliverySetId);
        }

        if (!empty($aPayment)) {
            $oBasket->setPayment($aPayment[0]);
        }

        if (!empty($sTSProductId)) {
            $oBasket->setTsProductId($sTSProductId);
        }

        $oBasket->setSkipVouchersChecking(true);
        if (!empty($aVoucherIDs)) {
            $iCount = count($aVoucherIDs);
            for ($i = 0; $i < $iCount; $i++) {
                $oBasket->addVoucher($aVoucherIDs[$i]);
            }
        }

        $oBasket->calculateBasket();

        return $oBasket;
    }

    /**
     * Creates articles from array
     *
     * @param array $aArticles
     *
     * @return array $aResult of id's and basket amounts of created articles
     */
    public function createArticles($aArticles)
    {
        $aResult = array();
        if (empty($aArticles)) {
            return $aResult;
        }
        foreach ($aArticles as $aArticle) {
            $oArticle = new oxArticle();
            $oArticle->setId($aArticle['oxid']);
            foreach ($aArticle as $sKey => $sValue) {
                if (strstr($sKey, "ox")) {
                    $sField = "oxarticles__{$sKey}";
                    $oArticle->$sField = new oxField($aArticle[$sKey]);
                }
            }
            $oArticle->save();
            if ($aArticle['scaleprices']) {
                $this->_createScalePrices(array($aArticle['scaleprices']));
            }
            if ($aArticle['field2shop']) {
                $this->_createField2Shop($oArticle, $aArticle['field2shop']);
            }
            $aResult[$aArticle['oxid']] = array(
                'id'     => $aArticle['oxid'],
                'amount' => $aArticle['amount'],
            );
        }

        return $aResult;
    }


    /**
     * Create user
     *
     * @param array $aUser user data
     *
     * @return oxUser
     */
    protected function _createUser($aUser)
    {
        $oUser = $this->createObj($aUser, "oxuser", "oxuser");
        if (isset($aUser['address'])) {
            $aUser['address']['oxuserid'] = $oUser->getId();
            $this->createObj($aUser['address'], "oxaddress", "oxaddress");
        }

        return $oUser;
    }

    /**
     * Create categories with assigning articles
     *
     * @param array $aCategories category data
     */
    protected function _createCats($aCategories)
    {
        if (empty($aCategories)) {
            return;
        }
        foreach ($aCategories as $iKey => $aCat) {
            $oCat = $this->createObj($aCat, 'oxcategory', ' oxcategories');
            if (!empty($aCat['oxarticles'])) {
                $iCnt = count($aCat['oxarticles']);
                for ($i = 0; $i < $iCnt; $i++) {
                    $aData = array(
                        'oxcatnid'   => $oCat->getId(),
                        'oxobjectid' => $aCat['oxarticles'][$i]
                    );
                    $this->createObj2Obj($aData, 'oxprice2article');
                }
            }
        }
    }

    /**
     * Creates price 2 article connection needed for scale prices
     *
     * @param array $aScalePrices of scale prices needed db fields
     */
    protected function _createScalePrices($aScalePrices)
    {
        $this->createObj2Obj($aScalePrices, "oxprice2article");
    }

    /**
     * Creates price 2 article connection needed for scale prices
     *
     * @param array $aScalePrices of scale prices needed db fields
     */
    protected function _createField2Shop($oArt, $aOptions)
    {
        $oField2Shop = oxNew("oxfield2shop");
        $oField2Shop->setProductData($oArt);
        if (!isset($aOptions['oxartid'])) {
            $aOptions['oxartid'] = new oxField($oArt->getId());
        }
        foreach ($aOptions as $sKey => $sValue) {
            if (strstr($sKey, "ox")) {
                $sField = "oxfield2shop__{$sKey}";
                $oField2Shop->$sField = new oxField($aOptions[$sKey]);
            }
        }
        $oField2Shop->save();
    }

    /**
     * Creates discounts and assign them to according objects
     *
     * @param array $aDiscounts discount data
     */
    protected function _setDiscounts($aDiscounts)
    {
        if (empty($aDiscounts)) {
            return;
        }
        foreach ($aDiscounts as $iKey => $aDiscount) {
            // add discounts
            $oDiscount = new oxDiscount();
            $oDiscount->setId($aDiscount['oxid']);
            foreach ($aDiscount as $sKey => $mxValue) {
                if (!is_array($mxValue)) {
                    $sField = "oxdiscount__" . $sKey;
                    $oDiscount->$sField = new oxField("{$mxValue}");
                } // if $sValue is not empty array then create oxobject2discount
                $oDiscount->save();
                if (is_array($mxValue) && !empty($mxValue)) {
                    foreach ($mxValue as $iArtId) {
                        $aData = array(
                            'oxid'         => $oDiscount->getId() . "_" . $iArtId,
                            'oxdiscountid' => $oDiscount->getId(),
                            'oxobjectid'   => $iArtId,
                            'oxtype'       => $sKey
                        );
                        $this->createObj2Obj($aData, "oxobject2discount");
                    }
                }
            }
        }
    }

    /**
     * Set up trusted shop
     *
     * @param array $aTrustedShop of trusted shops data
     *
     * @return string selected product id
     */
    protected function _setTrustedShop($aTrustedShop)
    {
        if (empty($aTrustedShop)) {
            return null;
        }
        if ($aTrustedShop['payments']) {
            foreach ($aTrustedShop['payments'] as $sShopPayId => $sTsPayId) {
                $aPayment = new oxPayment();
                if ($aPayment->load($sShopPayId)) {
                    $aPayment->oxpayments__oxtspaymentid = new oxField($sTsPayId);
                    $aPayment->save();
                }
            }
        }

        return $aTrustedShop['product_id'];
    }

    /**
     * Creates wrappings
     *
     * @param array $aWrappings
     *
     * @return array of wrapping id's
     */
    protected function _setWrappings($aWrappings)
    {
        if (empty($aWrappings)) {
            return false;
        }
        $aWrap = array();
        foreach ($aWrappings as $aWrapping) {
            $oCard = oxNew('oxbase');
            $oCard->init('oxwrapping');
            foreach ($aWrapping as $sKey => $mxValue) {
                if (!is_array($mxValue)) {
                    $sField = "oxwrapping__" . $sKey;
                    $oCard->$sField = new oxField($mxValue, oxField::T_RAW);
                }
            }
            $oCard->save();
            if ($aWrapping['oxarticles']) {
                foreach ($aWrapping['oxarticles'] as $sArtId) {
                    $aWrap[$sArtId] = $oCard->getId();
                }
            } else {
                $aWrap['card'] = $oCard->getId();
            }
        }

        return $aWrap;
    }

    /**
     * Creates deliveries
     *
     * @param array $aDeliveryCosts
     *
     * @return array of delivery id's
     */
    protected function _setDeliveryCosts($aDeliveryCosts)
    {
        if (empty($aDeliveryCosts)) {
            return;
        }

        if (!empty($aDeliveryCosts['oxdeliveryset'])) {
            $aData = $aDeliveryCosts['oxdeliveryset'];
            unset($aDeliveryCosts['oxdeliveryset']);
        } else {
            $aData = array(
                'oxactive' => 1
            );
        }
        $oDeliverySet = $this->createObj($aData, 'oxdeliveryset', 'oxdeliveryset');

        foreach ($aDeliveryCosts as $iKey => $aDelivery) {
            $oDelivery = new oxDelivery();
            foreach ($aDelivery as $sKey => $mxValue) {
                if (!is_array($mxValue)) {
                    $sField = "oxdelivery__" . $sKey;
                    $oDelivery->$sField = new oxField("{$mxValue}");
                }
            }
            $oDelivery->save();
            $aData = array(
                'oxdelid'    => $oDelivery->getId(),
                'oxdelsetid' => $oDeliverySet->getId(),
            );
            $this->createObj2Obj($aData, "oxdel2delset");
        }

        return $oDeliverySet->getId();
    }

    /**
     * Creates payments
     *
     * @param array $aPayments
     *
     * @return array of payment id's
     */
    protected function _setPayments($aPayments)
    {
        if (empty($aPayments)) {
            return false;
        }
        $aPay = array();
        foreach ($aPayments as $iKey => $aPayment) {
            // add discounts
            $oPayment = new oxPayment();
            if (isset($aPayment['oxid'])) {
                $oPayment->setId($aPayment['oxid']);
            }
            foreach ($aPayment as $sKey => $mxValue) {
                if (!is_array($mxValue)) {
                    $sField = "oxpayments__" . $sKey;
                    $oPayment->$sField = new oxField("{$mxValue}");
                }
            }
            $oPayment->save();
            $aPay[] = $oPayment->getId();
        }

        return $aPay;
    }

    /**
     * Creates voucherserie and it's vouchers
     *
     * @param array $aVoucherSeries voucherserie and voucher data
     *
     * @return array of voucher id's
     */
    protected function _setVouchers($aVoucherSeries)
    {
        if (empty($aVoucherSeries)) {
            return;
        }
        $aVoucherIDs = array();
        foreach ($aVoucherSeries as $aVoucherSerie) {
            $oVoucherSerie = oxNew('oxbase');
            $oVoucherSerie->init('oxvoucherseries');
            foreach ($aVoucherSerie as $sKey => $mxValue) {
                $sField = "oxvoucherseries__" . $sKey;
                $oVoucherSerie->$sField = new oxField($mxValue, oxField::T_RAW);
            }
            $oVoucherSerie->save();
            // inserting vouchers
            for ($i = 1; $i <= $aVoucherSerie['voucher_count']; $i++) {
                $aData = array(
                    'oxreserved'       => 0,
                    'oxvouchernr'      => md5(uniqid(rand(), true)),
                    'oxvoucherserieid' => $oVoucherSerie->getId()
                );
                $oVoucher = $this->createObj($aData, 'oxvoucher', 'oxvouchers');
                $aVoucherIDs[] = $oVoucher->getId();
            }
        }

        return $aVoucherIDs;
    }

    protected function _getDefaultUserData()
    {
        $aUser = array(
            'oxid'          => 'checkoutTestUser',
            'oxrights'      => 'malladmin',
            'oxactive'      => '1',
            'oxusername'    => 'admin',
            'oxpassword'    => 'f6fdffe48c908deb0f4c3bd36c032e72',
            'oxpasssalt'    => '61646D696E',
            'oxcompany'     => 'Your Company Name',
            'oxfname'       => 'John',
            'oxlname'       => 'Doe',
            'oxstreet'      => 'Maple Street',
            'oxstreetnr'    => '10',
            'oxcity'        => 'Any City',
            'oxcountryid'   => 'a7c40f631fc920687.20179984',
            'oxzip'         => '9041',
            'oxfon'         => '217-8918712',
            'oxfax'         => '217-8918713',
            'oxstateid'     => null,
            'oxaddinfo'     => null,
            'oxustid'       => null,
            'oxsal'         => 'MR',
            'oxustidstatus' => '0',
        );

        return $aUser;
    }

    protected function _getDefaultGroupsData()
    {
        $aGroup = array(
            0 => array(
                'oxid'           => 'checkoutTestGroup',
                'oxactive'       => 1,
                'oxtitle'        => 'checkoutTestGroup',
                'oxobject2group' => array('checkoutTestUser', 'oxidpaypal'),
            ),
        );

        return $aGroup;
    }

    /**
     * Getting articles
     *
     * @param array $aArts of article objects
     *
     * @return created articles id's
     */
    public function getArticles($aArts)
    {
        return $this->_getArticles($aArts);
    }

    /**
     * Apply discounts
     *
     * @param array $aDiscounts of discount data
     */
    public function setDiscounts($aDiscounts)
    {
        $this->_setDiscounts($aDiscounts);
    }

    /**
     * Create object 2 object connection in databse
     *
     * @param array  $aData         db fields and values
     * @param string $sObj2ObjTable table name
     */
    public function createObj2Obj($aData, $sObj2ObjTable)
    {
        if (empty($aData)) {
            return;
        }
        $iCnt = count($aData);
        for ($i = 0; $i < $iCnt; $i++) {
            $oObj = new oxBase();
            $oObj->init($sObj2ObjTable);
            if ($iCnt < 2) {
                $aObj = $aData[$i];
            } else {
                $aObj = $aData;
            }
            foreach ($aObj as $sKey => $sValue) {
                $sField = $sObj2ObjTable . "__" . $sKey;
                $oObj->$sField = new oxField($sValue, oxField::T_RAW);
            }
            $oObj->save();
        }
    }

    /**
     * Create group and assign
     *
     * @param array $aData
     */
    public function createGroups($aData)
    {
        if (empty($aData)) {
            return;
        }
        foreach ($aData as $iKey => $aGroup) {
            $oGroup = $this->createObj($aGroup, 'oxgroups', ' oxgroups');
            if (!empty($aGroup['oxobject2group'])) {
                $iCnt = count($aGroup['oxobject2group']);
                for ($i = 0; $i < $iCnt; $i++) {
                    $aCon = array(
                        'oxgroupsid' => $oGroup->getId(),
                        'oxobjectid' => $aGroup['oxobject2group'][$i]
                    );
                    $this->createObj2Obj($aCon, 'oxobject2group');
                }
            }
        }
    }

    /**
     * Standard object creator
     *
     * @param array  $aData   data
     * @param string $sObject object name
     * @param string $sTable  table name
     *
     * @return object $oObj
     */
    public function createObj($aData, $sObject, $sTable)
    {
        if (empty($aData)) {
            return;
        }
        $oObj = new $sObject();
        if ($aData['oxid']) {
            $oObj->setId($aData['oxid']);
        }
        foreach ($aData as $sKey => $sValue) {
            if (!is_array($sValue)) {
                $sField = $sTable . "__" . $sKey;
                $oObj->$sField = new oxField($sValue, oxField::T_RAW);
            }
        }
        $oObj->save();

        return $oObj;
    }

    /**
     * Create shop.
     *
     * @param array $aData
     *
     * @return int
     */
    public function createShop($aData)
    {
        $iActiveShopId = 1;
        $iShopCnt = count($aData);
        for ($i = 0; $i < $iShopCnt; $i++) {
            $aParams = array();
            foreach ($aData[$i] as $sKey => $sValue) {
                $sField = "oxshops__" . $sKey;
                $aParams[$sField] = $sValue;
            }
            $oShop = oxNew("oxshop");
            $oShop->assign($aParams);
            $oShop->save();
            $oShop->generateViews();
            if ($aData[$i]['activeshop']) {
                $iActiveShopId = $oShop->getId();
            }
        }

        return $iActiveShopId;
    }

    /**
     * Setting active shop
     *
     * @param int $iShopId
     */
    public function setActiveShop($iShopId)
    {
        if ($iShopId) {
            oxRegistry::getConfig()->setShopId($iShopId);
        }
    }
}