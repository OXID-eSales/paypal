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
 * PayPal IPN config class
 */
class IpnConfig
{
    /**
     * Name of sandbox IPN host configuration variable.
     *
     * @var string
     */
    const OEPAYPAL_SANDBOX_IPNHOST_CONFIGVAR = 'sPayPalSandboxIpnHost';

    /**
     * Name of sandbox mode configuration variable.
     *
     * @var string
     */
    const OEPAYPAL_IPNHOST_CONFIGVAR = 'sPayPalIpnHost';

    /**
     * Name of sandbox IPN url configuration variable.
     *
     * @var string
     */
    const OEPAYPAL_SANDBOX_IPN_URL_CONFIGVAR = 'sPayPalSandboxIpnUrl';

    /**
     * Name of IPN url configuration variable.
     *
     * @var string
     */
    const OEPAYPAL_IPN_URL_CONFIGVAR = 'sPayPalIpnUrl';

    /**
     * PayPal IPN host.
     *
     * @var string
     */
    const OEPAYPAL_IPN_HOST = 'ipnpb.paypal.com';

    /**
     * PayPal IPN sandbox host.
     *
     * @var string
     */
    const OEPAYPAL_IPN_SANDBOX_HOST = 'ipnpb.sandbox.paypal.com';

    /**
     * PayPal sandbox url to send IPN callback to for verification.
     *
     * @var string
     */
    const OEPAYPAL_SANDBOX_IPN_CALLBACK_URL = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * PayPal sandbox url to send IPN callback to for verification.
     *
     * @var string
     */
    const OEPAYPAL_IPN_CALLBACK_URL = 'https://ipnpb.paypal.com/cgi-bin/webscr';

    /**
     * @var
     */
    protected $payPalConfig = null;

    /**
     * PayPal IPN host.
     *
     * @var string
     */
    protected $payPalIpnHost = '';

    /**
     * PayPal IPN sandbox host.
     *
     * @var string
     */
    protected $payPalIpnSandboxHost = '';

    /**
     * PayPal sandbox url to send IPN callback to for verification.
     *
     * @var string
     */
    protected $payPalSandboxIpnUrl = '';

    /**
     * PayPal sandbox url to send IPN callback to for verification.
     *
     * @var string
     */
    protected $payPalIpnUrl = '';

    /**
     * IpnConfig constructor.
     */
    public function __construct()
    {
        if ($host = $this->getPayPalConfig()->getParameter(self::getPayPalIpnHost())) {
            $this->setPayPalIpnHost($host);
        }

        if ($sandboxHost = $this->getPayPalConfig()->getParameter(self::OEPAYPAL_SANDBOX_IPNHOST_CONFIGVAR)) {
            $this->setPayPalSandboxIpnHost($sandboxHost);
        }

        if ($url = $this->getPayPalConfig()->getParameter(self::OEPAYPAL_IPN_URL_CONFIGVAR)) {
            $this->setPayPalIpnUrl($url);
        }

        if ($sandboxUrl = $this->getPayPalConfig()->getParameter(self::OEPAYPAL_SANDBOX_IPN_URL_CONFIGVAR)) {
            $this->setPayPalSandboxIPNUrl($sandboxUrl);
        }
    }

    /**
     * Sets PayPal IPN host.
     *
     * @param string $payPalIpnHost
     */
    public function setPayPalIpnHost($payPalIpnHost)
    {
        $this->payPalIpnHost = $payPalIpnHost;
    }

    /**
     * Returns PayPal IPN host.
     *
     * @return string
     */
    public function getPayPalIpnHost()
    {
        if (empty($this->payPalIpnHost)) {
            $this->payPalIpnHost = self::OEPAYPAL_IPN_HOST;
        }

        return $this->payPalIpnHost;
    }

    /**
     * Sets PayPal IPN sandbox host.
     *
     * @param string $payPalIpnSandboxHost
     */
    public function setPayPalSandboxIpnHost($payPalIpnSandboxHost)
    {
        $this->payPalIpnSandboxHost = $payPalIpnSandboxHost;
    }

    /**
     * Returns PayPal sandbox host.
     *
     * @return string
     */
    public function getPayPalSandboxIpnHost()
    {
        if (empty($this->payPalIpnSandboxHost)) {
            $this->payPalIpnSandboxHost = self::OEPAYPAL_IPN_SANDBOX_HOST;
        }

        return $this->payPalIpnSandboxHost;
    }

    /**
     * Returns PayPal OR PayPal IPN sandbox host.
     *
     * @return string
     */
    public function getIpnHost()
    {
        if ($this->getPayPalConfig()->isSandboxEnabled()) {
            $url = $this->getPayPalSandboxIpnHost();
        } else {
            $url = $this->getPayPalIpnHost();
        }

        return $url;
    }

    /**
     * PayPal IPN Url Setter
     *
     * @param string $payPalUrl
     */
    public function setPayPalIpnUrl($payPalIpnUrl)
    {
        $this->payPalIpnUrl = $payPalIpnUrl;
    }

    /**
     *  IPN Url getter
     *
     * @return string
     */
    public function getPayPalIpnUrl()
    {
        if (empty($this->payPalIpnUrl)) {
            $this->payPalIpnUrl = self::OEPAYPAL_IPN_CALLBACK_URL;
        }

        return $this->payPalIpnUrl;
    }

    /**
     * PayPal sandbox IPN url setter
     *
     * @param string $payPalSandboxIPNUrl
     */
    public function setPayPalSandboxIpnUrl($payPalSandboxIpnUrl)
    {
        $this->payPalSandboxIpnUrl = $payPalSandboxIpnUrl;
    }

    /**
     * PayPal sandbox IPN url getter
     *
     * @return string
     */
    public function getPayPalSandboxIpnUrl()
    {
        if (empty($this->payPalSandboxIpnUrl)) {
            $this->payPalSandboxIpnUrl = self::OEPAYPAL_SANDBOX_IPN_CALLBACK_URL;
        }

        return $this->payPalSandboxIpnUrl;
    }

    /**
     * Returns end point url
     *
     * @return string
     */
    public function getIpnUrl()
    {
        if ($this->getPayPalConfig()->isSandboxEnabled()) {
            $url = $this->getPayPalSandboxIpnUrl();
        } else {
            $url = $this->getPayPalIpnUrl();
        }

        return $url;
    }

    /**
     * Returns Url for IPN response call to notify PayPal
     *
     * @return string
     */
    public function getIPNResponseUrl()
    {
        return $this->getIpnUrl() . '&cmd=_notify-validate';
    }

    /**
     * Getter for oepaypal config.
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
}