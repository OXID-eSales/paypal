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
 * oxViewConfig class wrapper for PayPal module.
 */
class oePayPalOxViewConfig extends oePayPalOxViewConfig_parent
{
    /** @var null oePayPalConfig */
    protected $_oPayPalConfig = null;

    /**
     * PayPal payment object.
     *
     * @var bool
     */
    protected $_oPayPalPayment = null;

    /**
     * Status if Express checkout is ON.
     *
     * @var bool
     */
    protected $_blExpressCheckoutEnabled = null;

    /**
     * Status if Standard checkout is ON.
     *
     * @var bool
     */
    protected $_blStandardCheckoutEnabled = null;

    /**
     * Status if PayPal is ON.
     *
     * @var bool
     */
    protected $_blPayPalEnabled = null;

    /**
     * PayPal Payment Validator object.
     *
     * @var oePayPalPaymentValidator
     */
    protected $_oPaymentValidator = null;

    /**
     * Set oePayPalPaymentValidator.
     *
     * @param oePayPalPaymentValidator $oPaymentValidator
     */
    public function setPaymentValidator($oPaymentValidator)
    {
        $this->_oPaymentValidator = $oPaymentValidator;
    }

    /**
     * Get oePayPalPaymentValidator. Create new if does not exist.
     *
     * @return oePayPalPaymentValidator
     */
    public function getPaymentValidator()
    {
        if (is_null($this->_oPaymentValidator)) {
            $this->setPaymentValidator(oxNew('oePayPalPaymentValidator'));
        }

        return $this->_oPaymentValidator;
    }

    /**
     * Returns TRUE if express checkout is enabled.
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabled()
    {
        if ($this->_blExpressCheckoutEnabled === null) {
            $this->_blExpressCheckoutEnabled = false;
            if ($this->_getPayPalConfig()->isExpressCheckoutEnabled()) {
                $oUser = $this->getUser();
                $oValidator = $this->getPaymentValidator();
                $oValidator->setUser($oUser);
                $oValidator->setConfig($this->getConfig());
                $oValidator->setCheckCountry(false);

                $this->_blExpressCheckoutEnabled = $oValidator->isPaymentValid();
            }
        }

        return $this->_blExpressCheckoutEnabled;
    }

    /**
     * Returns TRUE if express checkout and displaying it in mini basket is enabled.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabledInMiniBasket()
    {
        $blExpressCheckoutEnabledInMiniBasket = false;
        if ($this->isExpressCheckoutEnabled() && $this->_getPayPalConfig()->isExpressCheckoutInMiniBasketEnabled()) {
            $blExpressCheckoutEnabledInMiniBasket = true;
        }

        return $blExpressCheckoutEnabledInMiniBasket;
    }

    /**
     * Returns TRUE if express checkout is enabled.
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabledInDetails()
    {
        $blExpressCheckoutEnabledInDetails = false;
        if ($this->isExpressCheckoutEnabled() && $this->_getPayPalConfig()->isExpressCheckoutInDetailsPage()) {
            $blExpressCheckoutEnabledInDetails = true;
        }

        return $blExpressCheckoutEnabledInDetails;
    }

    /**
     * Returns TRUE if standard checkout is enabled.
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isStandardCheckoutEnabled()
    {
        if ($this->_blStandardCheckoutEnabled === null) {
            $this->_blStandardCheckoutEnabled = false;
            if ($this->_getPayPalConfig()->isStandardCheckoutEnabled()) {
                $oUser = $this->getUser();
                $oValidator = $this->getPaymentValidator();
                $oValidator->setUser($oUser);
                $oValidator->setConfig($this->getConfig());

                $this->_blStandardCheckoutEnabled = $oValidator->isPaymentValid();
            }
        }

        return $this->_blStandardCheckoutEnabled;
    }

    /**
     * Checks if PayPal standard or express checkout is enabled.
     * Does not do payment amount or user country/group check.
     *
     * @return bool
     */
    public function isPayPalActive()
    {
        return $this->getPaymentValidator()->isPaymentActive();
    }

    /**
     * Returns PayPal payment description text.
     *
     * @return string
     */
    public function getPayPalPaymentDescription()
    {
        $sDesc = "";
        if (($oPayPalPayment = $this->getPayPalPayment())) {
            $sDesc = $oPayPalPayment->oxpayments__oxlongdesc->getRawValue();
        }

        return $sDesc;
    }

    /**
     * Returns PayPal payment object.
     *
     * @return oxPayment
     */
    public function getPayPalPayment()
    {
        if ($this->_oPayPalPayment === null) {
            $this->_oPayPalPayment = false;
            $oPayPalPayment = oxNew("oxpayment");

            // payment is not available/active?
            if ($oPayPalPayment->load("oxidpaypal") && $oPayPalPayment->oxpayments__oxactive->value) {
                $this->_oPayPalPayment = $oPayPalPayment;
            }
        }

        return $this->_oPayPalPayment;
    }

    /**
     * Returns state if order info should be send to PayPal.
     *
     * @return bool
     */
    public function sendOrderInfoToPayPal()
    {
        $blSendInfoToPayPalEnabled = $this->_getPayPalConfig()->sendOrderInfoToPayPal();
        if ($blSendInfoToPayPalEnabled) {
            /** @var oePayPalOxBasket $oBasket */
            $oBasket = $this->getSession()->getBasket();
            $blSendInfoToPayPalEnabled = !$oBasket->isFractionQuantityItemsPresent();
        }

        return $blSendInfoToPayPalEnabled;
    }

    /**
     * Returns default (on/off) state if order info should be send to PayPal.
     *
     * @return bool
     */
    public function sendOrderInfoToPayPalDefault()
    {
        return $this->_getPayPalConfig()->sendOrderInfoToPayPalDefault();
    }

    /**
     * Returns PayPal config.
     *
     * @return oePayPalConfig
     */
    protected function _getPayPalConfig()
    {
        if (is_null($this->_oPayPalConfig)) {
            $this->_oPayPalConfig = oxNew("oePayPalConfig");
        }

        return $this->_oPayPalConfig;
    }

    /**
     * Returns current URL.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_getPayPalConfig()->getCurrentUrl();
    }

    /**
     * @return string
     */
    public function getPayPalClientId()
    {
        return oxRegistry::getConfig()->getConfigParam('oePayPalClientId');
    }

    /**
     * Returns whether PayPal banners should be shown on the start page
     *
     * @return bool
     */
    public function showPayPalBannerOnStartPage()
    {
        $config = oxRegistry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersStartPage') &&
            $config->getConfigParam('oePayPalBannersStartPageSelector')
        );
    }

    /**
     * Returns PayPal banners selector for the start page
     *
     * @return string
     */
    public function getPayPalBannerStartPageSelector()
    {
        $config = oxRegistry::getConfig();
        return $config->getConfigParam('oePayPalBannersStartPageSelector');
    }

    /**
     * Returns whether PayPal banners should be shown on the category page
     *
     * @return bool
     */
    public function showPayPalBannerOnCategoryPage()
    {
        $config = oxRegistry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersCategoryPage') &&
            $config->getConfigParam('oePayPalBannersCategoryPageSelector')
        );
    }

    /**
     * Returns PayPal banners selector for the category page
     *
     * @return string
     */
    public function getPayPalBannerCategoryPageSelector()
    {
        $config = oxRegistry::getConfig();
        return $config->getConfigParam('oePayPalBannersCategoryPageSelector');
    }

    /**
     * Returns whether PayPal banners should be shown on the search results page
     *
     * @return bool
     */
    public function showPayPalBannerOnSearchResultsPage()
    {
        $config = oxRegistry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersSearchResultsPage') &&
            $config->getConfigParam('oePayPalBannersSearchResultsPageSelector')
        );
    }

    /**
     * Returns PayPal banners selector for the search page
     *
     * @return string
     */
    public function getPayPalBannerSearchPageSelector()
    {
        $config = oxRegistry::getConfig();
        return $config->getConfigParam('oePayPalBannersSearchResultsPageSelector');
    }

    /**
     * Returns whether PayPal banners should be shown on the product details page
     *
     * @return bool
     */
    public function showPayPalBannerOnProductDetailsPage()
    {
        $config = oxRegistry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersProductDetailsPage') &&
            $config->getConfigParam('oePayPalBannersProductDetailsPageSelector')
        );
    }

    /**
     * Returns PayPal banners selector for the product detail page
     *
     * @return string
     */
    public function getPayPalBannerProductDetailsPageSelector()
    {
        $config = oxRegistry::getConfig();
        return $config->getConfigParam('oePayPalBannersProductDetailsPageSelector');
    }

    /**
     * Returns whether PayPal banners should be shown on the checkout page
     *
     * @return bool
     */
    public function showPayPalBannerOnCheckoutPage()
    {
        $showBanner = false;
        $actionClassName = $this->getActionClassName();
        $config = oxRegistry::getConfig();
        if ($actionClassName === 'basket') {
            $showBanner = (
                !$config->getConfigParam('oePayPalBannersHideAll') &&
                $config->getConfigParam('oePayPalBannersCheckoutPage') &&
                $config->getConfigParam('oePayPalBannersCartPageSelector')
            );
        } else if ($actionClassName === 'payment') {
            $showBanner = (
                !$config->getConfigParam('oePayPalBannersHideAll') &&
                $config->getConfigParam('oePayPalBannersCheckoutPage') &&
                $config->getConfigParam('oePayPalBannersPaymentPageSelector')
            );
        }
        return $showBanner;
    }

    /**
     * Returns PayPal banners selector for the cart page
     *
     * @return string
     */
    public function getPayPalBannerCartPageSelector()
    {
        $config = oxRegistry::getConfig();
        return $config->getConfigParam('oePayPalBannersCartPageSelector');
    }

    /**
     * Returns PayPal banners selector for the payment page
     *
     * @return string
     */
    public function getPayPalBannerPaymentPageSelector()
    {
        $config = oxRegistry::getConfig();
        return $config->getConfigParam('oePayPalBannersPaymentPageSelector');
    }

    /**
     * Returns the PayPal banners colour scheme
     *
     * @return string
     */
    public function getPayPalBannersColorScheme()
    {
        return oxRegistry::getConfig()->getConfigParam('oePayPalBannersColorScheme');
    }
}
