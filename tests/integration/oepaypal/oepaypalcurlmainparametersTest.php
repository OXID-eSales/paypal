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
 * @copyright (C) OXID eSales AG 2003-2013
 */

require_once realpath( "." ).'/unit/OxidTestCase.php';
require_once realpath( "." ).'/unit/test_config.inc.php';

class Integration_oePayPal_oePayPalCurlMainParametersTest extends OxidTestCase
{
    public function testCurlMainParameterHost_modeSandbox_sandboxHost()
    {
        $this->getConfig()->setConfigParam( 'blOEPayPalSandboxMode', true );

        $oService = new oePayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals( 'api-3t.sandbox.paypal.com', $oCurl->getHost() );
    }

    public function testCurlMainParameterHost_modeProduction_payPalHost()
    {
        $this->getConfig()->setConfigParam( 'blOEPayPalSandboxMode', false );

        $oService = new oePayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals( 'api-3t.paypal.com', $oCurl->getHost() );
    }

    public function testCurlMainParameterCharset_default_iso()
    {
        $oService = new oePayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals( 'ISO-8859-15', $oCurl->getDataCharset() );
    }

    public function testCurlMainParameterCharset_utfMode_utf()
    {
        $this->getConfig()->setConfigParam( 'iUtfMode', true );

        $oService = new oePayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals( 'UTF-8', $oCurl->getDataCharset() );
    }

    public function testCurlMainParameterUrlToCall_defaultProductionMode_ApiUrl()
    {
        $this->getConfig()->setConfigParam( 'blOEPayPalSandboxMode', false );

        $oService = new oePayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals( 'https://api-3t.paypal.com/nvp', $oCurl->getUrlToCall() );
    }

    public function testCurlMainParameterUrlToCall_defaultSandboxMode_sandboxApiUrl()
    {
        $this->getConfig()->setConfigParam( 'blOEPayPalSandboxMode', true );

        $oService = new oePayPalService();
        $oCurl = $oService->getCaller()->getCurl();

        $this->assertEquals( 'https://api-3t.sandbox.paypal.com/nvp', $oCurl->getUrlToCall() );
    }
}