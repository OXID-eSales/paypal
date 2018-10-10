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

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

/**
 * Class IpnConfigTest
 *
 * @package OxidEsales\PayPalModule\Tests\Unit\Core
 */
class IpnConfigTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function providerGetPayPalIpnHost()
    {
        return [
            // Default PayPal IPN sandbox host
            'default_IPN_sandbox_host' =>
            [true, null, null, \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_IPN_SANDBOX_HOST],
            // Default PayPal IPN host.
            'default_IPN_host' =>
            [false, null, null, \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_IPN_HOST],
            // Sandbox on; PayPal IPN sandbox host NOT set; PayPal IPN host set; Sandbox default host return.
            'sandbox_on_default_IPN_sandbox_host' =>
            [true, null, 'testSandboxIpnHost', \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_IPN_SANDBOX_HOST],
            // Sandbox off; PayPal IPN sandbox host set; PayPal IPN host NOT set; PayPal default host return.
            'sandbox_off_default_IPN_host' =>
            [false, 'testSandboxIpnHost', null, \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_IPN_HOST],
            // Sandbox on; PayPal IPN sandbox host set; PayPal IPN host set; PayPal set sandbox host return.
            'sandbox_on_set_IPN_sandbox_host' =>
            [true, 'testSandboxIpnHost', 'testIpnHost', 'testSandboxIpnHost'],
            // Sandbox off; PayPal IPN sandbox host set; PayPal IPN host set; PayPal set host return.
            'sandbox_off_set_IPN_host' =>
            [false, 'testSandboxIpnHost', 'testIpnHost', 'testIpnHost']
        ];
    }

    /**
     * Test setter/getter depending on module configuration.
     *
     * @dataProvider providerGetPayPalIpnHost
     */
    public function testGetIpnHost($sandboxEnabled, $payPalSandboxIpnHost, $payPalIpnHost, $expected)
    {
        $IpnConfig = new \OxidEsales\PayPalModule\Core\IpnConfig();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandboxEnabled);

        if (!empty($payPalSandboxIpnHost)) {
            $IpnConfig->setPayPalSandboxIpnHost($payPalSandboxIpnHost);
        }
        if (!empty($payPalIpnHost)) {
            $IpnConfig->setPayPalIpnHost($payPalIpnHost);
        }

        $this->assertEquals($expected, $IpnConfig->getIpnHost());
    }

    public function providerGetPayPalIpnCallbackUrl()
    {
        return [
            // Default PayPal IPN sandbox url
            'default_IPN_sandbox_url' =>
                [true, null, null, \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_SANDBOX_IPN_CALLBACK_URL],
            // Default PayPal IPN url.
            'default_IPN_url' =>
                [false, null, null, \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_IPN_CALLBACK_URL],
            // Sandbox on; PayPal IPN sandbox url NOT set; PayPal IPN url set; Sandbox default url return.
            'sandbox_on_default_IPN_sandbox_url' =>
                [true, null, 'testSandboxIpnUrl', \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_SANDBOX_IPN_CALLBACK_URL],
            // Sandbox off; PayPal IPN sandbox url set; PayPal IPN url NOT set; PayPal default url return.
            'sandbox_off_default_IPN_url' =>
                [false, 'testSandboxIpnUrl', null, \OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_IPN_CALLBACK_URL],
            // Sandbox on; PayPal IPN sandbox url set; PayPal IPN url set; PayPal set sandbox url return.
            'sandbox_on_set_IPN_sandbox_url' =>
                [true, 'testSandboxIpnUrl', 'testIpnUrl', 'testSandboxIpnUrl'],
            // Sandbox off; PayPal IPN sandbox url set; PayPal IPN url set; PayPal set url return.
            'sandbox_off_set_IPN_url' =>
                [false, 'testSandboxIpnUrl', 'testIpnUrl', 'testIpnUrl']
        ];
    }

    /**
     * Test setter/getter depending on module configuration.
     *
     * @dataProvider providerGetPayPalIpnCallbackUrl
     */
    public function testGetIpnCallbackUrl($sandboxEnabled, $payPalSandboxIpnUrl, $payPalIpnUrl, $expected)
    {
        $IpnConfig = new \OxidEsales\PayPalModule\Core\IpnConfig();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandboxEnabled);

        if (!empty($payPalSandboxIpnUrl)) {
            $IpnConfig->setPayPalSandboxIpnUrl($payPalSandboxIpnUrl);
        }
        if (!empty($payPalIpnUrl)) {
            $IpnConfig->setPayPalIpnUrl($payPalIpnUrl);
        }

        $this->assertEquals($expected, $IpnConfig->getIpnUrl());
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetIpnResponseUrl()
    {
        $url = 'http://mypaypal.local/webscr';

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\IpnConfig::class);
        $mockBuilder->setMethods(['getIpnUrl']);
        $ipnConfig = $mockBuilder->getMock();
        $ipnConfig->expects($this->once())
            ->method('getIpnUrl')
            ->will($this->returnValue($url));

        $this->assertEquals($ipnConfig->getIPNResponseUrl(), $url . '&cmd=_notify-validate');
    }
    
}