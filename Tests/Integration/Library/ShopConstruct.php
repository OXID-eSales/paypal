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

namespace OxidEsales\PayPalModule\Tests\Integration\Library;

use OxidEsales\Eshop\Application\Model\Basket;

class ShopConstruct
{
    /**
     * @var \OxidEsales\Eshop\Application\Model\User
     */
    protected $_oParams = null;

    /**
     * @var \OxidEsales\Eshop\Application\Model\User
     */
    protected $_oUser = null;

    /**
     * @var null
     */
    protected $_oGroups = null;

    /**
     * Sets constructor parameters
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->_oParams = $params;
        $this->setConfigParameters();
        $this->setSessionParameters();
        $this->setRequestParameters();
        $this->setServerParameters();
    }

    /**
     * Sets constructor parameters
     *
     * @param null $key
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    public function getParams($key = null)
    {
        if (!is_null($key)) {
            return $this->_oParams[$key];
        }

        return $this->_oParams;
    }

    /**
     * @param \OxidEsales\Eshop\Application\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    public function getUser()
    {
        if (is_null($this->user)) {
            $user = $this->getParams('user');
            if ($user === false) {
                return null;
            }
            if (!$user) {
                $user = $this->getDefaultUserData();
            }
            $this->setUser($this->createUser($user));
        }

        return $this->user;
    }

    /**
     * @param \OxidEsales\Eshop\Application\Model\User $groups
     */
    public function setGroups($groups)
    {
        $this->_oGroups = $groups;
    }

    /**
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    public function getGroups()
    {
        if (is_null($this->_oGroups)) {
            $groups = $this->getParams('groups');
            if ($groups === false) {
                return null;
            }
            if (!$groups) {
                $groups = $this->getDefaultGroupsData();
            }
            $this->setGroups($this->createGroups($groups));
        }

        return $this->_oGroups;
    }

    /**
     * Set config options
     */
    public function setConfigParameters()
    {
        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        $params = $this->getParams('config');
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $config->setConfigParam($key, $value);
            }
        }
    }

    /**
     * Set config options
     */
    public function setSessionParameters()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $params = $this->getParams('session');
        if (is_array($params)) {
            foreach ($params as $name => $value) {
                $session->setVariable($name, $value);
            }
        }
    }

    /**
     * Set config options
     */
    public function setRequestParameters()
    {
        $params = $this->getParams('requestToShop');
        if (is_array($params)) {
            $_POST = $params;
            $_GET = $params;
            $_COOKIE = $params;
            $_REQUEST = $params;
        }
    }

    /**
     * Set config options
     */
    public function setServerParameters()
    {
        if ($serverParams = $this->getParams('serverParams')) {
            foreach ($serverParams as $key => $value) {
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Returns prepared basket, user and config.
     *
     * @return \OxidEsales\Eshop\Application\Model\Basket
     */
    public function getBasket()
    {
        $this->createCats($this->getParams('categories'));
        $this->setDiscounts($this->getParams('discounts'));

        $costs = $this->getParams('costs');
        $deliverySetId = $this->setDeliveryCosts($costs['delivery']);
        $payment = $this->setPayments($costs['payment']);
        $voucherIDs = $this->setVouchers($costs['voucherserie']);

        $basket = oxNew(Basket::class);

        $basket->setBasketUser($this->getUser());

        $this->getGroups();

        $artsForBasket = $this->createArticles($this->getParams('articles'));
        $wrap = $this->setWrappings($costs['wrapping']);
        foreach ($artsForBasket as $art) {
            if (!$art['amount']) {
                continue;
            }
            $item = $basket->addToBasket($art['id'], $art['amount']);

            if (!empty($wrap)) {
                $item->setWrapping($wrap[$art['id']]);
            }
        }

        $wrap['card'] ? $basket->setCardId($wrap['card']) : '';

        if (!empty($deliverySetId) && !$costs['delivery']['oxdeliveryset']['createOnly']) {
            $basket->setShipping($deliverySetId);
        }

        if (!empty($payment)) {
            $basket->setPayment($payment[0]);
        }
        
        $basket->setSkipVouchersChecking(true);
        if (!empty($voucherIDs)) {
            $count = count($voucherIDs);
            for ($i = 0; $i < $count; $i++) {
                $basket->addVoucher($voucherIDs[$i]);
            }
        }

        $basket->calculateBasket();

        return $basket;
    }

    /**
     * Creates articles from array
     *
     * @param array $articleDataSet
     *
     * @return array $result of id's and basket amounts of created articles
     */
    public function createArticles($articleDataSet)
    {
        $result = array();
        if (empty($articleDataSet)) {
            return $result;
        }
        foreach ($articleDataSet as $articleData) {
            $article = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
            $article->setId($articleData['oxid']);
            foreach ($articleData as $key => $value) {
                if (strstr($key, "ox")) {
                    $field = "oxarticles__{$key}";
                    $article->$field = new \OxidEsales\Eshop\Core\Field($articleData[$key]);
                }
            }
            $article->save();
            if ($articleData['scaleprices']) {
                $this->createScalePrices(array($articleData['scaleprices']));
            }
            if ($articleData['field2shop']) {
                $this->createField2Shop($article, $articleData['field2shop']);
            }
            $result[$articleData['oxid']] = array(
                'id'     => $articleData['oxid'],
                'amount' => $articleData['amount'],
            );
        }

        return $result;
    }


    /**
     * Create user
     *
     * @param array $userData user data
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    protected function createUser($userData)
    {
        /** @var \OxidEsales\Eshop\Application\Model\User $user */
        $user = $this->createObj($userData, \OxidEsales\Eshop\Application\Model\User::class, "oxuser");
        if (isset($userData['address'])) {
            $userData['address']['oxuserid'] = $user->getId();
            $this->createObj($userData['address'], \OxidEsales\Eshop\Application\Model\Address::class, "oxaddress");
        }

        return $user;
    }

    /**
     * Create categories with assigning articles
     *
     * @param array $categories category data
     */
    protected function createCats($categories)
    {
        if (empty($categories)) {
            return;
        }
        foreach ($categories as $key => $cat) {
            $cat = $this->createObj($cat, \OxidEsales\Eshop\Application\Model\Category::class, ' oxcategories');
            if (!empty($cat['oxarticles'])) {
                $cnt = count($cat['oxarticles']);
                for ($i = 0; $i < $cnt; $i++) {
                    $data = array(
                        'oxcatnid'   => $cat->getId(),
                        'oxobjectid' => $cat['oxarticles'][$i]
                    );
                    $this->createObj2Obj($data, 'oxprice2article');
                }
            }
        }
    }

    /**
     * Creates price 2 article connection needed for scale prices
     *
     * @param array $scalePrices of scale prices needed db fields
     */
    protected function createScalePrices($scalePrices)
    {
        $this->createObj2Obj($scalePrices, "oxprice2article");
    }

    /**
     * Creates price 2 article connection needed for scale prices
     *
     * @param \OxidEsales\Eshop\Application\Model\Article $art
     * @param array                                       $options
     */
    protected function createField2Shop($art, $options)
    {
        $field2Shop = oxNew(\OxidEsales\Eshop\Application\Model\Field2Shop::class);
        $field2Shop->setProductData($art);
        if (!isset($options['oxartid'])) {
            $options['oxartid'] = new \OxidEsales\Eshop\Core\Field($art->getId());
        }
        foreach ($options as $key => $value) {
            if (strstr($key, "ox")) {
                $field = "oxfield2shop__{$key}";
                $field2Shop->$field = new \OxidEsales\Eshop\Core\Field($options[$key]);
            }
        }
        $field2Shop->save();
    }

    /**
     * Apply discounts.
     * Creates discounts and assign them to according objects.
     *
     * @param array $discountDataSet discount data
     */
    public function setDiscounts($discountDataSet)
    {
        if (empty($discountDataSet)) {
            return;
        }
        foreach ($discountDataSet as $discountData) {
            // add discounts
            $discount = oxNew(\OxidEsales\Eshop\Application\Model\Discount::class);
            $discount->setId($discountData['oxid']);
            foreach ($discountData as $key => $value) {
                if (!is_array($value)) {
                    $field = "oxdiscount__" . $key;
                    $discount->$field = new \OxidEsales\Eshop\Core\Field("{$value}");
                } // if $value is not empty array then create oxobject2discount
                $discount->save();
                if (is_array($value) && !empty($value)) {
                    foreach ($value as $artId) {
                        $data = array(
                            'oxid'         => $discount->getId() . "_" . $artId,
                            'oxdiscountid' => $discount->getId(),
                            'oxobjectid'   => $artId,
                            'oxtype'       => $key
                        );
                        $this->createObj2Obj($data, "oxobject2discount");
                    }
                }
            }
        }
    }
    
    /**
     * Creates wrappings
     *
     * @param array $wrappings
     *
     * @return false|array of wrapping id's
     */
    protected function setWrappings($wrappings)
    {
        if (empty($wrappings)) {
            return false;
        }
        $wrap = array();
        foreach ($wrappings as $wrapping) {
            $card = oxNew(\OxidEsales\Eshop\Core\Model\BaseModel::class);
            $card->init('oxwrapping');
            foreach ($wrapping as $key => $mxValue) {
                if (!is_array($mxValue)) {
                    $field = "oxwrapping__" . $key;
                    $card->$field = new \OxidEsales\Eshop\Core\Field($mxValue, \OxidEsales\Eshop\Core\Field::T_RAW);
                }
            }
            $card->save();
            if ($wrapping['oxarticles']) {
                foreach ($wrapping['oxarticles'] as $artId) {
                    $wrap[$artId] = $card->getId();
                }
            } else {
                $wrap['card'] = $card->getId();
            }
        }

        return $wrap;
    }

    /**
     * Creates deliveries
     *
     * @param array $deliveryCostDataSet
     *
     * @return null|array of delivery id's
     */
    protected function setDeliveryCosts($deliveryCostDataSet)
    {
        if (empty($deliveryCostDataSet)) {
            return;
        }

        if (!empty($deliveryCostDataSet['oxdeliveryset'])) {
            $data = $deliveryCostDataSet['oxdeliveryset'];
            unset($deliveryCostDataSet['oxdeliveryset']);
        } else {
            $data = array(
                'oxactive' => 1
            );
        }
        $deliverySet = $this->createObj($data, \OxidEsales\Eshop\Application\Model\DeliverySet::class, 'oxdeliveryset');

        foreach ($deliveryCostDataSet as $deliveryCostData) {
            $delivery = oxNew(\OxidEsales\Eshop\Application\Model\Delivery::class);
            foreach ($deliveryCostData as $key => $value) {
                if (!is_array($value)) {
                    $field = "oxdelivery__" . $key;
                    $delivery->$field = new \OxidEsales\Eshop\Core\Field("{$value}");
                }
            }
            $delivery->save();
            $data = array(
                'oxdelid'    => $delivery->getId(),
                'oxdelsetid' => $deliverySet->getId(),
            );
            $this->createObj2Obj($data, "oxdel2delset");
        }

        return $deliverySet->getId();
    }

    /**
     * Creates payments
     *
     * @param array $paymentDataSet
     *
     * @return false|array of payment id's
     */
    protected function setPayments($paymentDataSet)
    {
        $result = [];

        if (empty($paymentDataSet)) {
            return false;
        }
        $payments = array();
        foreach ($paymentDataSet as $paymentData) {
            // add discounts
            $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            if (isset($paymentData['oxid'])) {
                $payment->setId($paymentData['oxid']);
            }
            foreach ($paymentData as $key => $value) {
                if (!is_array($value)) {
                    $field = "oxpayments__" . $key;
                    $payment->$field = new \OxidEsales\Eshop\Core\Field("{$value}");
                }
            }
            $payment->save();
            $result[] = $payment->getId();
        }

        return $result;
    }

    /**
     * Creates voucherserie and it's vouchers
     *
     * @param array $voucherSeriesDataSet voucherserie and voucher data
     *
     * @return array of voucher id's
     */
    protected function setVouchers($voucherSeriesDataSet)
    {
        $voucherIDs = array();

        $voucherSeriesDataSet = (array) $voucherSeriesDataSet;
        foreach ($voucherSeriesDataSet as $voucherSeriesData) {
            $voucherSeries = oxNew('oxBase');
            $voucherSeries->init('oxvoucherseries');
            foreach ($voucherSeriesData as $key => $value) {
                $field = "oxvoucherseries__" . $key;
                $voucherSeries->$field = new \oxField($value, \oxField::T_RAW);
            }
            $voucherSeries->save();
            // inserting vouchers
            for ($i = 1; $i <= $voucherSeriesData['voucher_count']; $i++) {
                $data = array(
                    'oxreserved'       => 0,
                    'oxvouchernr'      => md5(uniqid(rand(), true)),
                    'oxvoucherserieid' => $voucherSeries->getId()
                );
                $voucher = $this->createObj($data, 'oxvoucher', 'oxvouchers');
                $voucherIDs[] = $voucher->getId();
            }
        }

        return $voucherIDs;
    }

    protected function getDefaultUserData()
    {
        $user = array(
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

        return $user;
    }

    protected function getDefaultGroupsData()
    {
        $group = array(
            0 => array(
                'oxid'           => 'checkoutTestGroup',
                'oxactive'       => 1,
                'oxtitle'        => 'checkoutTestGroup',
                'oxobject2group' => array('checkoutTestUser', 'oxidpaypal'),
            ),
        );

        return $group;
    }

    /**
     * Getting articles
     *
     * @param array $arts of article objects
     *
     * @return created articles id's
     */
    public function getArticles($arts)
    {
        return $this->_getArticles($arts);
    }

    /**
     * Apply discounts
     *
     * @param array $discounts of discount data
     */
    public function DELETEsetDiscounts($discounts)
    {
        $this->setDiscounts($discounts);
    }

    /**
     * Create object 2 object connection in databse
     *
     * @param array  $data         db fields and values
     * @param string $obj2ObjTable table name
     */
    public function createObj2Obj($data, $obj2ObjTable)
    {
        if (empty($data)) {
            return;
        }
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            if ($obj2ObjTable === 'oxobject2group') {
                $object = oxNew(\OxidEsales\Eshop\Application\Model\Object2Group::class);
            } else {
                $object = oxNew(\OxidEsales\Eshop\Core\Model\BaseModel::class);
            }
            $object->init($obj2ObjTable);
            if ($count < 2) {
                $objectData = $data[$i];
            } else {
                $objectData = $data;
            }
            foreach ($objectData as $key => $value) {
                $field = $obj2ObjTable . "__" . $key;
                $object->$field = new \OxidEsales\Eshop\Core\Field($value, \OxidEsales\Eshop\Core\Field::T_RAW);
            }
            $object->save();
        }
    }

    /**
     * Create group and assign
     *
     * @param array $data
     */
    public function createGroups($data)
    {
        if (empty($data)) {
            return;
        }
        foreach ($data as $key => $groupData) {
            $group = $this->createObj($groupData, \OxidEsales\Eshop\Application\Model\Groups::class, ' oxgroups');
            if (!empty($groupData['oxobject2group'])) {
                $cnt = count($groupData['oxobject2group']);
                for ($i = 0; $i < $cnt; $i++) {
                    $con = array(
                        'oxgroupsid' => $group->getId(),
                        'oxobjectid' => $groupData['oxobject2group'][$i]
                    );
                    $this->createObj2Obj($con, 'oxobject2group');
                }
            }
        }
    }

    /**
     * Standard object creator
     *
     * @param array  $data   data
     * @param string $object object name
     * @param string $table  table name
     *
     * @return null|object $obj
     */
    public function createObj($data, $object, $table)
    {
        if (empty($data)) {
            return;
        }
        $obj = new $object();
        if ($data['oxid']) {
            $obj->setId($data['oxid']);
        }
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $field = $table . "__" . $key;
                $obj->$field = new \OxidEsales\Eshop\Core\Field($value, \OxidEsales\Eshop\Core\Field::T_RAW);
            }
        }
        $obj->save();

        return $obj;
    }

    /**
     * Create shop.
     *
     * @param array $data
     *
     * @return int
     */
    public function createShop($data)
    {
        $activeShopId = 1;
        $shopCnt = count($data);
        for ($i = 0; $i < $shopCnt; $i++) {
            $params = array();
            foreach ($data[$i] as $key => $value) {
                $field = "oxshops__" . $key;
                $params[$field] = $value;
            }
            $shop = oxNew(\OxidEsales\Eshop\Application\Model\Shop::class);
            $shop->assign($params);
            $shop->save();
            $shop->generateViews();
            if ($data[$i]['activeshop']) {
                $activeShopId = $shop->getId();
            }
        }

        return $activeShopId;
    }

    /**
     * Setting active shop
     *
     * @param int $shopId
     */
    public function setActiveShop($shopId)
    {
        if ($shopId) {
            \OxidEsales\Eshop\Core\Registry::getConfig()->setShopId($shopId);
        }
    }
}
