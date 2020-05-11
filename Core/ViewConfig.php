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

namespace OxidEsales\PayPalModule\Core;

use OxidEsales\Eshop\Core\Registry;

/**
 * ViewConfig class wrapper for PayPal module.
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    /** @var null \OxidEsales\PayPalModule\Core\Config */
    protected $payPalConfig = null;

    /**
     * PayPal payment object.
     *
     * @var \OxidEsales\Eshop\Application\Model\Payment|bool
     */
    protected $payPalPayment = null;

    /**
     * Status if Express checkout is ON.
     *
     * @var bool
     */
    protected $expressCheckoutEnabled = null;

    /**
     * Status if Standard checkout is ON.
     *
     * @var bool
     */
    protected $standardCheckoutEnabled = null;

    /**
     * Status if PayPal is ON.
     *
     * @var bool
     */
    protected $payPalEnabled = null;

    /**
     * PayPal Payment Validator object.
     *
     * @var \OxidEsales\PayPalModule\Model\PaymentValidator
     */
    protected $paymentValidator = null;

    /**
     * Set \OxidEsales\PayPalModule\Model\PaymentValidator.
     *
     * @param \OxidEsales\PayPalModule\Model\PaymentValidator $paymentValidator
     */
    public function setPaymentValidator($paymentValidator)
    {
        $this->paymentValidator = $paymentValidator;
    }

    /**
     * Get \OxidEsales\PayPalModule\Model\PaymentValidator. Create new if does not exist.
     *
     * @return \OxidEsales\PayPalModule\Model\PaymentValidator
     */
    public function getPaymentValidator()
    {
        if (is_null($this->paymentValidator)) {
            $this->setPaymentValidator(oxNew(\OxidEsales\PayPalModule\Model\PaymentValidator::class));
        }

        return $this->paymentValidator;
    }

    /**
     * Returns TRUE if express checkout is enabled.
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabled()
    {
        if ($this->expressCheckoutEnabled === null) {
            $this->expressCheckoutEnabled = false;
            if ($this->getPayPalConfig()->isExpressCheckoutEnabled()) {
                $user = $this->getUser();
                $validator = $this->getPaymentValidator();
                $validator->setUser($user);
                $validator->setConfig(\OxidEsales\Eshop\Core\Registry::getConfig());
                $validator->setCheckCountry(false);

                $this->expressCheckoutEnabled = $validator->isPaymentValid();
            }
        }

        return $this->expressCheckoutEnabled;
    }

    /**
     * Returns TRUE if express checkout and displaying it in mini basket is enabled.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabledInMiniBasket()
    {
        $expressCheckoutEnabledInMiniBasket = false;
        if ($this->isExpressCheckoutEnabled() && $this->getPayPalConfig()->isExpressCheckoutInMiniBasketEnabled()) {
            $expressCheckoutEnabledInMiniBasket = true;
        }

        return $expressCheckoutEnabledInMiniBasket;
    }

    /**
     * Returns TRUE if express checkout is enabled.
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabledInDetails()
    {
        $expressCheckoutEnabledInDetails = false;
        if ($this->isExpressCheckoutEnabled() && $this->getPayPalConfig()->isExpressCheckoutInDetailsPage()) {
            $expressCheckoutEnabledInDetails = true;
        }

        return $expressCheckoutEnabledInDetails;
    }

    /**
     * Returns TRUE if standard checkout is enabled.
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isStandardCheckoutEnabled()
    {
        if ($this->standardCheckoutEnabled === null) {
            $this->standardCheckoutEnabled = false;
            if ($this->getPayPalConfig()->isStandardCheckoutEnabled()) {
                $user = $this->getUser();
                $validator = $this->getPaymentValidator();
                $validator->setUser($user);
                $validator->setConfig(\OxidEsales\Eshop\Core\Registry::getConfig());

                $this->standardCheckoutEnabled = $validator->isPaymentValid();
            }
        }

        return $this->standardCheckoutEnabled;
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
        $desc = "";
        if (($payPalPayment = $this->getPayPalPayment())) {
            $desc = $payPalPayment->oxpayments__oxlongdesc->getRawValue();
        }

        return $desc;
    }

    /**
     * Returns PayPal payment object.
     *
     * @return \OxidEsales\Eshop\Application\Model\Payment
     */
    public function getPayPalPayment()
    {
        if ($this->payPalPayment === null) {
            $this->payPalPayment = false;
            $payPalPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);

            // payment is not available/active?
            if ($payPalPayment->load("oxidpaypal") && $payPalPayment->oxpayments__oxactive->value) {
                $this->payPalPayment = $payPalPayment;
            }
        }

        return $this->payPalPayment;
    }

    /**
     * Returns state if order info should be send to PayPal.
     *
     * @return bool
     */
    public function sendOrderInfoToPayPal()
    {
        $sendInfoToPayPalEnabled = $this->getPayPalConfig()->sendOrderInfoToPayPal();
        if ($sendInfoToPayPalEnabled) {
            /** @var \OxidEsales\PayPalModule\Model\Basket $basket */
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            $basket = $session->getBasket();
            $sendInfoToPayPalEnabled = !$basket->isFractionQuantityItemsPresent();
        }

        return $sendInfoToPayPalEnabled;
    }

    /**
     * Returns default (on/off) state if order info should be send to PayPal.
     *
     * @return bool
     */
    public function sendOrderInfoToPayPalDefault()
    {
        return $this->getPayPalConfig()->sendOrderInfoToPayPalDefault();
    }

    /**
     * Returns PayPal config.
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     */
    protected function getPayPalConfig()
    {
        if (is_null($this->payPalConfig)) {
            $this->payPalConfig = oxNew(\OxidEsales\PayPalModule\Core\Config::class);
        }

        return $this->payPalConfig;
    }

    /**
     * Returns current URL.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getPayPalConfig()->getCurrentUrl();
    }

    /**
     * @return string
     */
    public function getPayPalClientId(): string
    {
        return Registry::getConfig()->getConfigParam('oePayPalClientId');
    }

    /**
     * Returns whether PayPal banners should be shown on the start page
     *
     * @return bool
     */
    public function showPayPalBannerOnStartPage()
    {
        $config = Registry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersStartPage') &&
            $config->getConfigParam('oePayPalBannersStartPageSelector') &&
            $config->getConfigParam('bl_perfLoadPrice')
        );
    }

    /**
     * Returns PayPal banners selector for the start page
     *
     * @return string
     */
    public function getPayPalBannerStartPageSelector()
    {
        $config = Registry::getConfig();
        return $config->getConfigParam('oePayPalBannersStartPageSelector');
    }

    /**
     * Returns whether PayPal banners should be shown on the category page
     *
     * @return bool
     */
    public function showPayPalBannerOnCategoryPage()
    {
        $config = Registry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersCategoryPage') &&
            $config->getConfigParam('oePayPalBannersCategoryPageSelector') &&
            $config->getConfigParam('bl_perfLoadPrice')
        );
    }

    /**
     * Returns PayPal banners selector for the category page
     *
     * @return string
     */
    public function getPayPalBannerCategoryPageSelector()
    {
        $config = Registry::getConfig();
        return $config->getConfigParam('oePayPalBannersCategoryPageSelector');
    }

    /**
     * Returns whether PayPal banners should be shown on the search results page
     *
     * @return bool
     */
    public function showPayPalBannerOnSearchResultsPage()
    {
        $config = Registry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersSearchResultsPage') &&
            $config->getConfigParam('oePayPalBannersSearchResultsPageSelector') &&
            $config->getConfigParam('bl_perfLoadPrice')
        );
    }

    /**
     * Returns PayPal banners selector for the search page
     *
     * @return string
     */
    public function getPayPalBannerSearchPageSelector()
    {
        $config = Registry::getConfig();
        return $config->getConfigParam('oePayPalBannersSearchResultsPageSelector');
    }

    /**
     * Returns whether PayPal banners should be shown on the product details page
     *
     * @return bool
     */
    public function showPayPalBannerOnProductDetailsPage()
    {
        $config = Registry::getConfig();
        return (
            !$config->getConfigParam('oePayPalBannersHideAll') &&
            $config->getConfigParam('oePayPalBannersProductDetailsPage') &&
            $config->getConfigParam('oePayPalBannersProductDetailsPageSelector') &&
            $config->getConfigParam('bl_perfLoadPrice')
        );
    }

    /**
     * Returns PayPal banners selector for the product detail page
     *
     * @return string
     */
    public function getPayPalBannerProductDetailsPageSelector()
    {
        $config = Registry::getConfig();
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
        $config = Registry::getConfig();
        if ($actionClassName === 'basket') {
            $showBanner = (
                !$config->getConfigParam('oePayPalBannersHideAll') &&
                $config->getConfigParam('oePayPalBannersCheckoutPage') &&
                $config->getConfigParam('oePayPalBannersCartPageSelector') &&
                $config->getConfigParam('bl_perfLoadPrice')
            );
        } elseif ($actionClassName === 'payment') {
            $showBanner = (
                !$config->getConfigParam('oePayPalBannersHideAll') &&
                $config->getConfigParam('oePayPalBannersCheckoutPage') &&
                $config->getConfigParam('oePayPalBannersPaymentPageSelector') &&
                $config->getConfigParam('bl_perfLoadPrice')
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
        $config = Registry::getConfig();
        return $config->getConfigParam('oePayPalBannersCartPageSelector');
    }

    /**
     * Returns PayPal banners selector for the payment page
     *
     * @return string
     */
    public function getPayPalBannerPaymentPageSelector()
    {
        $config = Registry::getConfig();
        return $config->getConfigParam('oePayPalBannersPaymentPageSelector');
    }

    /**
     * Returns the PayPal banners colour scheme
     *
     * @return string
     */
    public function getPayPalBannersColorScheme()
    {
        return Registry::getConfig()->getConfigParam('oePayPalBannersColorScheme');
    }
}
