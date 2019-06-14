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
}
