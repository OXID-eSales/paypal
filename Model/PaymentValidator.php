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
 * This class is for checking validation of PayPal payment for user and basket amount
 */
class PaymentValidator
{
    /**
     * Basket price
     *
     * @var double $_dPrice
     */
    protected $price;

    /**
     * Config object
     *
     * @var \OxidEsales\Eshop\Core\Config $_oConfig
     */
    protected $config = null;

    /**
     * User object
     *
     * @var \OxidEsales\Eshop\Application\Model\User $_oUser
     */
    protected $user = null;

    /**
     * Payment object
     *
     * @var \OxidEsales\Eshop\Application\Model\Payment $_oPayment
     */
    protected $payment = null;

    /**
     * Check country in validator.
     *
     * @var bool
     */
    protected $checkCountry = true;

    /**
     * Basket price setter
     *
     * @param double $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Basket price getter
     *
     * @return double
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Config object setter
     *
     * @param \OxidEsales\Eshop\Core\Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Config object getter
     *
     * @return \OxidEsales\Eshop\Core\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * User object setter
     *
     * @param \OxidEsales\Eshop\Application\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * User object getter
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Payment object setter
     *
     * @param \OxidEsales\Eshop\Application\Model\Payment $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Check country setter.
     *
     * @param boolean $checkCountry
     */
    public function setCheckCountry($checkCountry)
    {
        $this->checkCountry = $checkCountry;
    }

    /**
     * Returns if country should be checked.
     *
     * @return boolean
     */
    public function getCheckCountry()
    {
        return $this->checkCountry;
    }

    /**
     * Payment object getter
     *
     * @return \OxidEsales\Eshop\Application\Model\Payment
     */
    public function getPayment()
    {
        if (is_null($this->payment)) {
            $payPalPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            $payPalPayment->load('oxidpaypal');
            $this->setPayment($payPalPayment);
        }

        return $this->payment;
    }

    /**
     * Checks if PayPal payment is active
     *
     * @return boolean
     */
    public function isPaymentActive()
    {
        $result = false;
        if ($payPalPayment = $this->getPayment()) {
            $result = $payPalPayment->oxpayments__oxactive->value ? true : false;
        }

        return $result;
    }


    /**
     * Checks if payment is valid according to config, user and basket amount.
     *
     * @return boolean
     */
    public function isPaymentValid()
    {
        $isValid = $this->isPaymentActive();

        if ($isValid && !is_null($this->getPrice())) {
            $isValid = $this->checkPriceRange() && $this->checkMinOrderPrice();
        }

        $user = $this->getUser();
        if ($isValid && $user && $user->hasAccount()) {
            $isValid = $this->checkUserGroup();
        }

        if ($isValid && $user && $this->getCheckCountry()) {
            $isValid = $this->checkUserCountry();
        }

        return $isValid;
    }

    /**
     * Checks if basket price is inside payment price range
     * If range is not set check returns true
     *
     * @return bool
     */
    protected function checkPriceRange()
    {
        $isValid = true;

        $payPalPayment = $this->getPayment();

        if ($payPalPayment->oxpayments__oxfromamount->value != 0 ||
            $payPalPayment->oxpayments__oxtoamount->value != 0
        ) {
            $cur = \OxidEsales\Eshop\Core\Registry::getConfig()->getActShopCurrencyObject();
            $price = $this->getPrice() / $cur->rate;

            $isValid = (($price >= $payPalPayment->oxpayments__oxfromamount->value) &&
                          ($price <= $payPalPayment->oxpayments__oxtoamount->value));
        }

        return $isValid;
    }

    /**
     * Checks if basket price is higher than minimum order price
     * If min price is not set check returns true
     *
     * @return bool
     */
    protected function checkMinOrderPrice()
    {
        $isValid = true;

        if ($minOrderPrice = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('iMinOrderPrice')) {
            $isValid = $this->getPrice() > $minOrderPrice;
        }

        return $isValid;
    }

    /**
     * Checks if user country is among payment countries
     * If payment countries are not set returns true
     *
     * @return bool
     */
    protected function checkUserCountry()
    {
        $isValid = true;

        $payPalPayment = $this->getPayment();

        $countries = $payPalPayment->getCountries();
        if ($countries) {
            $isValid = false;
            foreach ($countries as $countryId) {
                if ($countryId === $this->getShippingCountryId()) {
                    $isValid = true;
                    break;
                }
            }
        }

        return $isValid;
    }

    /**
     * Checks if user belongs group that is assigned to payment
     * If payment does not have any groups assigned returns true
     *
     * @return bool
     */
    protected function checkUserGroup()
    {
        $isValid = true;

        $payPalPayment = $this->getPayment();
        $groups = $payPalPayment->getGroups();

        if ($groups && $groups->count() > 0) {
            $isValid = $this->isUserAssignedToGroup($groups);
        }

        return $isValid;
    }

    /**
     * Checks whether user is assigned to given groups array.
     *
     * @param \OxidEsales\Eshop\Core\Model\ListModel $groups
     *
     * @return bool
     */
    protected function isUserAssignedToGroup($groups)
    {
        $isValid = false;

        $user = $this->getUser();
        foreach ($groups as $group) {
            if ($user->inGroup($group->getId())) {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }

    /**
     * Returns shipping country ID.
     *
     * @return string
     */
    protected function getShippingCountryId()
    {
        $user = $this->getUser();
        if ($user->getSelectedAddressId()) {
            $countryId = $user->getSelectedAddress()->oxaddress__oxcountryid->value;
        } else {
            $countryId = $user->oxuser__oxcountryid->value;
        }

        return $countryId;
    }
}
