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

namespace OxidEsales\PayPalModule\Tests\Integration;

class CurlMainParametersTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testCurlMainParameterHost_modeSandbox_sandboxHost()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $curl = $service->getCaller()->getCurl();

        $this->assertEquals('api-3t.sandbox.paypal.com', $curl->getHost());
    }

    public function testCurlMainParameterHost_modeProduction_payPalHost()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $curl = $service->getCaller()->getCurl();

        $this->assertEquals('api-3t.paypal.com', $curl->getHost());
    }

    public function testCurlMainParameterCharset_default_iso()
    {
        $this->getConfig()->setConfigParam('iUtfMode', false);

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $curl = $service->getCaller()->getCurl();

        $this->assertEquals('UTF-8', $curl->getDataCharset());
    }

    public function testCurlMainParameterCharset_utfMode_utf()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $curl = $service->getCaller()->getCurl();

        $this->assertEquals('UTF-8', $curl->getDataCharset());
    }

    public function testCurlMainParameterUrlToCall_defaultProductionMode_ApiUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $curl = $service->getCaller()->getCurl();

        $this->assertEquals('https://api-3t.paypal.com/nvp', $curl->getUrlToCall());
    }

    public function testCurlMainParameterUrlToCall_defaultSandboxMode_sandboxApiUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $curl = $service->getCaller()->getCurl();

        $this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $curl->getUrlToCall());
    }
}