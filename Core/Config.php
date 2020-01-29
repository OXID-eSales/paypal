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
 * PayPal config class
 */
class Config
{

    /**
     * PayPal payment was triggered via standard checkout by selecting PP as the payment method.
     *
     * @var int
     */
    const OEPAYPAL_ECS = 1;

    /**
     * PayPal payment was triggered by shortcut button in basket step.
     *
     * @var int
     */
    const OEPAYPAL_SHORTCUT = 2;

    /**
     * Name of session variable that marks how payment was triggered.
     *
     * @var string
     */
    const OEPAYPAL_TRIGGER_NAME = 'oepaypal';

    /**
     * Name of partnercode array key in case payment was triggered by shortcut button.
     *
     * @var string
     */
    const PARTNERCODE_SHORTCUT_KEY = 'SHORTCUT';

    /**
     * PayPal module id.
     *
     * @var string
     */
    protected $payPalId = 'oepaypal';

    /**
     * PayPal host.
     *
     * @var string
     */
    protected $payPalHost = 'api-3t.paypal.com';

    /**
     * PayPal sandbox host.
     *
     * @var string
     */
    protected $payPalSandboxHost = 'api-3t.sandbox.paypal.com';

    /**
     * PayPal sandbox Url where user must be redirected after his session gets PayPal token.
     *
     * @var string
     */
    protected $payPalSandboxUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * PayPal Url where user must be redirected after his session gets PayPal token.
     *
     * @var string
     */
    protected $payPalUrl = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * PayPal sandbox API url.
     *
     * @var string
     */
    protected $payPalSandboxApiUrl = 'https://api-3t.sandbox.paypal.com/nvp';

    /**
     * PayPal API url.
     *
     * @var string
     */
    protected $payPalApiUrl = 'https://api-3t.paypal.com/nvp';

    /**
     * Maximum possible delivery costs value.
     *
     * @var double
     */
    protected $maxDeliveryAmount = 30;

    /**
     * Please do not change this place.
     * It is important to guarantee the future development of this OXID eShop extension and to keep it free of charge.
     * Thanks!
     *
     * @var array Partner codes based on edition
     */
    protected $partnerCodes = array(
        'EE' => 'OXID_Cart_EnterpriseECS',
        'PE' => 'OXID_Cart_ProfessionalECS',
        'CE' => 'OXID_Cart_CommunityECS',
        'SHORTCUT' => 'Oxid_Cart_ECS_Shortcut'
    );

    /**
     * Return PayPal module id.
     *
     * @return string
     */
    public function getModuleId()
    {
        return $this->payPalId;
    }

    /**
     * Sets PayPal host.
     *
     * @param string $payPalHost
     */
    public function setPayPalHost($payPalHost)
    {
        $this->payPalHost = $payPalHost;
    }

    /**
     * Returns PayPal host.
     *
     * @return string
     */
    public function getPayPalHost()
    {
        $host = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sPayPalHost');
        if ($host) {
            $this->setPayPalHost($host);
        }

        return $this->payPalHost;
    }

    /**
     * Sets PayPal sandbox host.
     *
     * @param string $payPalSandboxHost
     */
    public function setPayPalSandboxHost($payPalSandboxHost)
    {
        $this->payPalSandboxHost = $payPalSandboxHost;
    }

    /**
     * Returns PayPal sandbox host.
     *
     * @return string
     */
    public function getPayPalSandboxHost()
    {
        $host = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sPayPalSandboxHost');
        if ($host) {
            $this->setPayPalSandboxHost($host);
        }

        return $this->payPalSandboxHost;
    }

    /**
     * Returns PayPal OR PayPal sandbox host.
     *
     * @return string
     */
    public function getHost()
    {
        if ($this->isSandboxEnabled()) {
            $url = $this->getPayPalSandboxHost();
        } else {
            $url = $this->getPayPalHost();
        }

        return $url;
    }

    /**
     *  Api Url setter
     *
     * @param string $payPalApiUrl
     */
    public function setPayPalApiUrl($payPalApiUrl)
    {
        $this->payPalApiUrl = $payPalApiUrl;
    }

    /**
     *  Api Url getter
     *
     * @return string
     */
    public function getPayPalApiUrl()
    {
        $url = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sPayPalApiUrl');
        if ($url) {
            $this->setPayPalApiUrl($url);
        }

        return $this->payPalApiUrl;
    }

    /**
     * PayPal sandbox api url setter
     *
     * @param string $payPalSandboxApiUrl
     */
    public function setPayPalSandboxApiUrl($payPalSandboxApiUrl)
    {
        $this->payPalSandboxApiUrl = $payPalSandboxApiUrl;
    }

    /**
     * PayPal sandbox api url getter
     *
     * @return string
     */
    public function getPayPalSandboxApiUrl()
    {
        $url = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sPayPalSandboxApiUrl');
        if ($url) {
            $this->setPayPalSandboxApiUrl($url);
        }

        return $this->payPalSandboxApiUrl;
    }

    /**
     * Returns end point url
     *
     * @return string
     */
    public function getApiUrl()
    {
        if ($this->isSandboxEnabled()) {
            $url = $this->getPayPalSandboxApiUrl();
        } else {
            $url = $this->getPayPalApiUrl();
        }

        return $url;
    }

    /**
     * PayPal Url Setter
     *
     * @param string $payPalUrl
     */
    public function setPayPalUrl($payPalUrl)
    {
        $this->payPalUrl = $payPalUrl;
    }

    /**
     * PayPal sandbox url setter
     *
     * @param string $payPalSandboxUrl
     */
    public function setPayPalSandboxUrl($payPalSandboxUrl)
    {
        $this->payPalSandboxUrl = $payPalSandboxUrl;
    }

    /**
     * PayPal sandbox url getter
     *
     * @return string
     */
    public function getPayPalUrl()
    {
        $url = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sPayPalUrl');
        if ($url) {
            $this->setPayPalUrl($url);
        }

        return $this->payPalUrl;
    }

    /**
     * PayPal sandbox url getter
     *
     * @return string
     */
    public function getPayPalSandboxUrl()
    {
        $url = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sPayPalSandboxUrl');
        if ($url) {
            $this->setPayPalSandboxUrl($url);
        }

        return $this->payPalSandboxUrl;
    }

    /**
     * Get PayPal url.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->isSandboxEnabled()) {
            $url = $this->getPayPalSandboxUrl();
        } else {
            $url = $this->getPayPalUrl();
        }

        return $url;
    }

    /**
     * Returns module config parameter value
     *
     * @param string $paramName parameter name
     *
     * @return mixed
     */
    public function getParameter($paramName)
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam($paramName);
    }

    /**
     * Returns true if Express Checkout is ON
     *
     * @return bool
     */
    public function isExpressCheckoutEnabled()
    {
        return $this->getParameter('blOEPayPalExpressCheckout');
    }

    /**
     * Returns true if Express Checkout is ON in mini basket
     *
     * @return bool
     */
    public function isExpressCheckoutInMiniBasketEnabled()
    {
        return $this->getParameter('blOEPayPalECheckoutInMiniBasket');
    }

    /**
     * Returns true if Standard PayPal Checkout is ON
     *
     * @return bool
     */
    public function isStandardCheckoutEnabled()
    {
        return $this->getParameter('blOEPayPalStandardCheckout');
    }

    /**
     * Returns true if logging request/response to PayPal is enabled
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->getParameter('blPayPalLoggerEnabled');
    }

    /**
     * Returns Brand/Shop name [OXID ESHOP]
     *
     * @return string
     */
    public function getBrandName()
    {
        $shopName = $this->getParameter('sOEPayPalBrandName');

        if (empty($shopName)) {
            $shop = \OxidEsales\Eshop\Core\Registry::getConfig()->getActiveShop();
            $shopName = $shop->oxshops__oxname->value;
        }

        return $shopName;
    }

    /**
     * Returns custom cart border color which is displayed in PayPal side
     *
     * @return string
     */
    public function getBorderColor()
    {
        return $this->getParameter('sOEPayPalBorderColor');
    }

    /**
     * Returns TRUE if order finalization on PayPal side is on
     *
     * @return bool
     */
    public function finalizeOrderOnPayPalSide()
    {
        $finalize = $this->getParameter('blOEPayPalFinalizeOrderOnPayPal');

        return $finalize !== null ? $finalize : false;
    }

    /**
     * Send order info to PayPal or not
     *
     * @return bool
     */
    public function sendOrderInfoToPayPal()
    {
        return $this->getParameter('blOEPayPalSendToPayPal');
    }

    /**
     * Send order info to PayPal or not config's default value: checked or not
     *
     * @return bool
     */
    public function sendOrderInfoToPayPalDefault()
    {
        return $this->getParameter('blOEPayPalDefaultUserChoice');
    }

    /**
     * Guest buy mode getter
     *
     * @return bool
     */
    public function isGuestBuyEnabled()
    {
        return $this->getParameter('blOEPayPalGuestBuyRole');
    }

    /**
     * Returns true of GiroPay is ON (not implemented yet)
     *
     * @return bool
     */
    public function isGiroPayEnabled()
    {
        return false;
    }

    /**
     * Returns true of sandbox mode is ON
     *
     * @return bool
     */
    public function isSandboxEnabled()
    {
        return $this->getParameter('blOEPayPalSandboxMode');
    }

    /**
     * Returns Empty Stock Level
     *
     * @return string
     */
    public function getEmptyStockLevel()
    {
        return $this->getParameter('sOEPayPalEmptyStockLevel');
    }

    /**
     * Returns PayPal password
     *
     * @return string
     */
    public function getPassword()
    {
        if ($this->isSandboxEnabled()) {
            // sandbox password
            return $this->getParameter('sOEPayPalSandboxPassword');
        }

        // password
        return $this->getParameter('sOEPayPalPassword');
    }

    /**
     * Returns PayPal user name
     *
     * @return string
     */
    public function getUserName()
    {
        if ($this->isSandboxEnabled()) {
            // sandbox login
            return $this->getParameter('sOEPayPalSandboxUsername');
        }

        // login
        return $this->getParameter('sOEPayPalUsername');
    }

    /**
     * Returns PayPal user name
     *
     * @return string
     */
    public function getUserEmail()
    {
        if ($this->isSandboxEnabled()) {
            // sandbox login
            return $this->getParameter('sOEPayPalSandboxUserEmail');
        }

        // login
        return $this->getParameter('sOEPayPalUserEmail');
    }

    /**
     * Returns PayPal signature
     *
     * @return string
     */
    public function getSignature()
    {
        if ($this->isSandboxEnabled()) {
            // sandbox signature
            return $this->getParameter('sOEPayPalSandboxSignature');
        }

        // test sandbox signature
        return $this->getParameter('sOEPayPalSignature');
    }

    /**
     * Returns PayPal transaction mode
     *
     * @return string
     */
    public function getTransactionMode()
    {
        return $this->getParameter('sOEPayPalTransactionMode');
    }


    /**
     * Returns redirect url.
     *
     * @param string $token      token to append to redirect url.
     * @param string $userAction checkout button action - continue (standard checkout) or commit (express checkout)
     *
     * @return string
     */
    public function getPayPalCommunicationUrl($token = null, $userAction = 'continue')
    {
        return $this->getUrl() . '&cmd=_express-checkout&token=' . (string) $token . '&useraction=' . (string) $userAction;
    }


    /**
     * Get logo Url based on selected settings
     * Returns shop url, or false
     *
     * @return string|bool
     */
    public function getLogoUrl()
    {
        $logoUrl = false;

        $logoName = $this->getLogoImageName();

        if (!empty($logoName)) {
            $logo = oxNew(\OxidEsales\PayPalModule\Core\ShopLogo::class);
            $logo->setImageDir(\OxidEsales\Eshop\Core\Registry::getConfig()->getImageDir());
            $logo->setImageDirUrl(\OxidEsales\Eshop\Core\Registry::getConfig()->getImageUrl());
            $logo->setImageName($logoName);
            $logo->setImageHandler(\OxidEsales\Eshop\Core\Registry::getUtilsPic());

            $logoUrl = $logo->getShopLogoUrl();
        }

        return $logoUrl;
    }

    /**
     * Returns IPN callback url
     *
     * @return string
     */
    public function getIPNCallbackUrl()
    {
        return $this->getShopUrl() . 'index.php?cl=oepaypalipnhandler&fnc=handleRequest&shp=' . $this->getShopId();
    }

    /**
     * Methods checks if sending of IPN callback url to PayPal is supressed by configuration.
     *
     * @return bool
     */
    public function suppressIPNCallbackUrl()
    {
        return (bool) \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('OEPayPalDisableIPN');
    }


    /**
     * Returns SSL or non SSL shop URL without index.php depending on Mall
     * affecting environment is admin mode and current ssl usage status
     *
     * @param bool $admin if admin
     *
     * @return string
     */
    public function getShopUrl($admin = null)
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig()->getCurrentShopUrl($admin);
    }

    /**
     * Wrapper to get language object from registry.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    public function getLang()
    {
        return \OxidEsales\Eshop\Core\Registry::getLang();
    }

    /**
     * Wrapper to get utils object from registry.
     *
     * @return \OxidEsales\Eshop\Core\Utils
     */
    public function getUtils()
    {
        return \OxidEsales\Eshop\Core\Registry::getUtils();
    }

    /**
     * Returns shop charset
     *
     * @return string
     */
    public function getCharset()
    {
        $charset = 'UTF-8';

        return $charset;
    }

    /**
     * @deprecated in dev-master (2018-04-27); Use OxidEsales\PayPalModule\Core\IPnConfig::getIPNResponseUrl()
     *
     * Returns Url for IPN response call to notify PayPal
     *
     * @return string
     */
    public function getIPNResponseUrl()
    {
        return $this->getUrl() . '&cmd=_notify-validate';
    }

    /**
     * Returns true if Express Checkout is in details page
     *
     * @return bool
     */
    public function isExpressCheckoutInDetailsPage()
    {
        return $this->getParameter('blOEPayPalECheckoutInDetails');
    }

    /**
     * Returns current URL
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return \OxidEsales\Eshop\Core\Registry::getUtilsUrl()->getCurrentUrl();
    }

    /**
     * Returns max delivery amount.
     *
     * @return integer
     */
    public function getMaxPayPalDeliveryAmount()
    {
        $maxDeliveryAmount = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dMaxPayPalDeliveryAmount');
        if (!$maxDeliveryAmount) {
            $maxDeliveryAmount = $this->maxDeliveryAmount;
        }

        return $maxDeliveryAmount;
    }

    /**
     * Please do not change this place.
     * It is important to guarantee the future development of this OXID eShop extension and to keep it free of charge.
     * Thanks!
     *
     * @return string partner code.
     */
    public function getPartnerCode()
    {
        $facts = new \OxidEsales\Facts\Facts();
        $key = $this->isShortcutPayment() ? self::PARTNERCODE_SHORTCUT_KEY : $facts->getEdition();

        return $this->partnerCodes[$key];
    }

    /**
     * Detects device type
     *
     * @return bool
     */
    public function isDeviceMobile()
    {
        $userAgent = oxNew(\OxidEsales\PayPalModule\Core\UserAgent::class);

        return ($userAgent->getDeviceType() == 'mobile');
    }

    /**
     * Returns id of shipping assigned for EC for mobile devices
     *
     * @return string
     */
    public function getMobileECDefaultShippingId()
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sOEPayPalMECDefaultShippingId');
    }

    /**
     * Returns logo image name according to parameter
     *
     * @return mixed|string
     */
    protected function getLogoImageName()
    {
        $option = $this->getParameter('sOEPayPalLogoImageOption');
        switch ($option) {
            case 'shopLogo':
                $logo = $this->getParameter('sShopLogo');
                break;
            case 'customLogo':
                $logo = $this->getParameter('sOEPayPalCustomShopLogoImage');
                break;
            case 'noLogo':
            default:
                $logo = '';

                return $logo;
        }

        return $logo;
    }

    /**
     * Returns active shop id
     *
     * @return string
     */
    protected function getShopId()
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();
    }

    /**
     * Returns oxConfig instance
     *
     * @return \OxidEsales\Eshop\Core\Config
     */
    protected function getConfig()
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig();
    }

    /**
     * Was the payment triggered by shortcut button or not?
     *
     * @return bool
     */
    protected function isShortcutPayment()
    {
        $trigger = (int) \OxidEsales\Eshop\Core\Registry::getSession()->getVariable(self::OEPAYPAL_TRIGGER_NAME);
        return (bool) ($trigger == self::OEPAYPAL_SHORTCUT);
    }
}
