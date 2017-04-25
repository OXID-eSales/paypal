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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Integration;

class CurlMainParametersTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testCurlMainParameterHost_modeSandbox_sandboxHost()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $oService = new \OxidEsales\PayPalModule\Core\PayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals('api-3t.sandbox.paypal.com', $oCurl->getHost());
    }

    public function testCurlMainParameterHost_modeProduction_payPalHost()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $oService = new \OxidEsales\PayPalModule\Core\PayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals('api-3t.paypal.com', $oCurl->getHost());
    }

    public function testCurlMainParameterCharset_default_iso()
    {
        $this->getConfig()->setConfigParam('iUtfMode', false);

        $oService = new \OxidEsales\PayPalModule\Core\PayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals('UTF-8', $oCurl->getDataCharset());
    }

    public function testCurlMainParameterCharset_utfMode_utf()
    {
        $oService = new \OxidEsales\PayPalModule\Core\PayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals('UTF-8', $oCurl->getDataCharset());
    }

    public function testCurlMainParameterUrlToCall_defaultProductionMode_ApiUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $oService = new \OxidEsales\PayPalModule\Core\PayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals('https://api-3t.paypal.com/nvp', $oCurl->getUrlToCall());
    }

    public function testCurlMainParameterUrlToCall_defaultSandboxMode_sandboxApiUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $oService = new \OxidEsales\PayPalModule\Core\PayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $oCurl->getUrlToCall());
    }
}