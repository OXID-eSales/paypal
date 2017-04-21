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

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Utils;

/**
 * Testing \OxidEsales\PayPalModule\Core\Config class.
 */
class ConfigTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Cleans oxConfig table and calls parent::tearDown();
     */
    protected function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("delete from oxconfig where oxvarname like 'paypal_%'");

        parent::tearDown();
    }

    /**
     * Cleans out the images that are created before image tests
     */
    protected function _cleanUp()
    {
        $sImgDir = $this->getConfig()->getImageDir();
        if (!$sShopLogo = $this->getConfig()->getConfigParam('sShopLogo')) {
            return;
        }
        $sLogoDir = $sImgDir . "resized_$sShopLogo";
        if (!file_exists($sLogoDir)) {
            return;
        }

        unlink($sLogoDir);
    }

    /**
     * Check if gets correct module id.
     */
    public function testGetModuleId()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $PayPalId = $oConfig->getModuleId();
        $this->assertEquals('oepaypal', $PayPalId, 'PayPal module should be oepaypal not ' . $PayPalId);
    }

    public function providerGetBrandName()
    {
        return array(
            array('', '', ''),
            array('', 'testShopName', 'testShopName'),
            array('testPayPalShopName', 'testBrandName', 'testPayPalShopName'),
            array('testPayPalShopName', '', 'testPayPalShopName'),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Config::getBrandName()
     *
     * @dataProvider providerGetBrandName
     *
     * @param string $sParamName
     * @param string $sShopName
     * @param string $sResultName
     */
    public function testGetBrandName($sParamName, $sShopName, $sResultName)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('sOEPayPalBrandName', $sParamName);

        $oShop = $this->getConfig()->getActiveShop();
        $oShop->oxshops__oxname = new \OxidEsales\Eshop\Core\Field($sShopName);
        $oShop->save();

        $this->assertEquals($sResultName, $oConfig->getBrandName());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Config::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPal()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSendToPayPal', true);
        $this->assertTrue($oConfig->sendOrderInfoToPayPal());

        $this->getConfig()->setConfigParam('blOEPayPalSendToPayPal', false);
        $this->assertFalse($oConfig->sendOrderInfoToPayPal());
    }

    /**
     * Test case for Config::isGuestBuyEnabled()
     */
    public function testIsGuestBuyEnabled()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalGuestBuyRole', true);
        $this->assertTrue($oConfig->isGuestBuyEnabled());

        $this->getConfig()->setConfigParam('blOEPayPalGuestBuyRole', false);
        $this->assertFalse($oConfig->isGuestBuyEnabled());
    }

    /**
     * Test case for Config::isGiroPayEnabled()
     */
    public function testIsGiroPayEnabled()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertFalse($oConfig->isGiroPayEnabled());
    }

    /**
     * Test case for Config::isSandboxEnabled()
     */
    public function testIsSandboxEnabled()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);
        $this->assertTrue($oConfig->isSandboxEnabled());

        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);
        $this->assertFalse($oConfig->isSandboxEnabled());
    }

    public function providerGetPayPalHost()
    {
        return array(
            // Default PayPal sandbox host
            array(true, null, null, 'api-3t.sandbox.paypal.com'),
            // Default PayPal host.
            array(false, null, null, 'api-3t.paypal.com'),
            // Sandbox on; PayPal sandbox host NOT set; PayPal host set; Sandbox default host return.
            array(true, null, 'paypalApiUrl', 'api-3t.sandbox.paypal.com'),
            // Sandbox off; PayPal sandbox host set; PayPal host NOT set; PayPal default host return.
            array(false, 'sandboxApiUrl', null, 'api-3t.paypal.com'),
            // Sandbox on; PayPal sandbox host set; PayPal host set; PayPal set sandbox host return.
            array(true, 'sandboxApiUrl', 'paypalApiUrl', 'sandboxApiUrl'),
            // Sandbox off; PayPal sandbox host set; PayPal host set; PayPal set host return.
            array(false, 'sandboxApiUrl', 'paypalApiUrl', 'paypalApiUrl'),
        );
    }

    /**
     * Test if return what is set in setter.
     * Test ir return default value if not set in setter.
     *
     * @dataProvider providerGetPayPalHost
     */
    public function testGetHost($bSandboxEnabled, $sPayPalSandboxHost, $sPayPalHost, $sResult)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $bSandboxEnabled);

        if (!empty($sPayPalSandboxHost)) {
            $oConfig->setPayPalSandboxHost($sPayPalSandboxHost);
        }
        if (!empty($sPayPalHost)) {
            $oConfig->setPayPalHost($sPayPalHost);
        }

        $this->assertEquals($sResult, $oConfig->getHost());
    }

    public function testGetPayPalHost_setWithSetter_setValue()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oConfig->setPayPalHost('PayPalHost');

        $this->assertEquals('PayPalHost', $oConfig->getPayPalHost(), 'Getter must return what is set in setter.');
    }

    public function testGetPayPalHost_default_definedClassAttribute()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('api-3t.paypal.com', $oConfig->getPayPalHost());
    }

    public function testGetPayPalHost_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalHost', 'configHost');
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('configHost', $oConfig->getPayPalHost());
    }

    public function testGetPayPalSandboxHost_setWithSetter_setValue()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oConfig->setPayPalSandboxHost('PayPalSandboxHost');

        $this->assertEquals('PayPalSandboxHost', $oConfig->getPayPalSandboxHost());
    }

    public function testGetPayPalSandboxHost_default_definedClassAttribute()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('api-3t.sandbox.paypal.com', $oConfig->getPayPalSandboxHost());
    }

    public function testGetPayPalSandboxHost_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalSandboxHost', 'configHost');
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('configHost', $oConfig->getPayPalSandboxHost());
    }


    public function providerGetApiUrl()
    {
        return array(
            array(true, null, null, 'https://api-3t.sandbox.paypal.com/nvp'),
            array(false, null, null, 'https://api-3t.paypal.com/nvp'),
            array(true, null, 'paypalApiUrl', 'https://api-3t.sandbox.paypal.com/nvp'),
            array(false, 'sandboxApiUrl', null, 'https://api-3t.paypal.com/nvp'),
            array(true, 'sandboxApiUrl', 'paypalApiUrl', 'sandboxApiUrl'),
            array(false, 'sandboxApiUrl', 'paypalApiUrl', 'paypalApiUrl'),
        );
    }

    /**
     * Test case for Config::getEndPointUrl()
     *
     * @dataProvider providerGetApiUrl
     */
    public function testApiUrl($bSandBoxEnabled, $sSandBoxApiUrl, $sApiUrl, $sResult)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $bSandBoxEnabled);

        if (!empty($sSandBoxApiUrl)) {
            $oConfig->setPayPalSandboxApiUrl($sSandBoxApiUrl);
        }
        if (!empty($sApiUrl)) {
            $oConfig->setPayPalApiUrl($sApiUrl);
        }

        $this->assertEquals($sResult, $oConfig->getApiUrl());
    }

    public function testGetPayPalSandboxApiUrl_setWithSetter_setValue()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oConfig->setPayPalSandboxApiUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $oConfig->getPayPalSandboxApiUrl());
    }

    public function testGetPayPalSandboxApiUrl_default_definedClassAttribute()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $oConfig->getPayPalSandboxApiUrl());
    }

    public function testGetPayPalSandboxApiUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalSandboxApiUrl', 'apiConfigHost');
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('apiConfigHost', $oConfig->getPayPalSandboxApiUrl());
    }

    public function testGetPayPalApiUrl_setWithSetter_setValue()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oConfig->setPayPalApiUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $oConfig->getPayPalApiUrl());
    }

    public function testGetPayPalApiUrl_default_definedClassAttribute()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://api-3t.paypal.com/nvp', $oConfig->getPayPalApiUrl());
    }

    public function testGetPayPalApiUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalApiUrl', 'apiConfigHost');
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('apiConfigHost', $oConfig->getPayPalApiUrl());
    }

    public function testGetPayPalSandboxUrl_setWithSetter_setValue()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oConfig->setPayPalSandboxUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $oConfig->getPayPalSandboxUrl());
    }

    public function testGetPayPalSandboxUrl_default_definedClassAttribute()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://www.sandbox.paypal.com/cgi-bin/webscr', $oConfig->getPayPalSandboxUrl());
    }

    public function testGetPayPalSandboxUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalSandboxUrl', 'ConfigHost');
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('ConfigHost', $oConfig->getPayPalSandboxUrl());
    }

    public function testGetPayPalUrl_setWithSetter_setValue()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oConfig->setPayPalUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $oConfig->getPayPalUrl());
    }

    public function testGetPayPalUrl_default_definedClassAttribute()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr', $oConfig->getPayPalUrl());
    }

    public function testGetPayPalUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalUrl', 'ConfigUrl');
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('ConfigUrl', $oConfig->getPayPalUrl());
    }

    public function providerGetPayPalCommunicationUrl()
    {
        return array(
            array(true, null, null, 'TestToken', 'continue', 'https://www.sandbox.paypal.com/cgi-bin/webscr&cmd=_express-checkout&token=TestToken&useraction=continue'),
            array(false, null, null, 'TestToken', 'commit', 'https://www.paypal.com/cgi-bin/webscr&cmd=_express-checkout&token=TestToken&useraction=commit'),
            array(true, null, 'paypalApiUrl', 'TestToken1', 'commit', 'https://www.sandbox.paypal.com/cgi-bin/webscr&cmd=_express-checkout&token=TestToken1&useraction=commit'),
            array(false, 'sandboxApiUrl', null, 'TestToken1', 'continue', 'https://www.paypal.com/cgi-bin/webscr&cmd=_express-checkout&token=TestToken1&useraction=continue'),
            array(true, 'sandboxApiUrl', 'paypalApiUrl', 'TestToken2', 'action', 'sandboxApiUrl&cmd=_express-checkout&token=TestToken2&useraction=action'),
            array(false, 'sandboxApiUrl', 'paypalApiUrl', 'TestToken2', 'action', 'paypalApiUrl&cmd=_express-checkout&token=TestToken2&useraction=action'),
        );
    }

    /**
     * Test case for Config::getUrl()
     *
     * @dataProvider providerGetPayPalCommunicationUrl
     */
    public function testGetPayPalCommunicationUrl($bSandBoxEnabled, $sSandBoxApiUrl, $sApiUrl, $sToken, $sUserAction, $sResult)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $bSandBoxEnabled);

        if (!empty($sSandBoxApiUrl)) {
            $oConfig->setPayPalSandboxUrl($sSandBoxApiUrl);
        }
        if (!empty($sApiUrl)) {
            $oConfig->setPayPalUrl($sApiUrl);
        }

        $this->assertEquals($sResult, $oConfig->getPayPalCommunicationUrl($sToken, $sUserAction));
    }

    public function providerGetTextConfig()
    {
        return array(
            array(true, 'text1', 'text2', 'text1'),
            array(false, 'text1', 'text2', 'text2'),
        );
    }

    /**
     * Test case for Config::getPassword()
     *
     * @dataProvider providerGetTextConfig
     */
    public function testGetPassword($bSandBoxEnabled, $sSandBoxPassword, $sPassword, $sResult)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $bSandBoxEnabled);
        $this->getConfig()->setConfigParam('sOEPayPalSandboxPassword', $sSandBoxPassword);
        $this->getConfig()->setConfigParam('sOEPayPalPassword', $sPassword);
        $this->assertEquals($sResult, $oConfig->getPassword());
    }

    /**
     * Test case for Config::getUserName()
     *
     * @dataProvider providerGetTextConfig
     */
    public function testGetUserName($bSandBoxEnabled, $sSandBoxUsername, $sUsername, $sResult)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $bSandBoxEnabled);
        $this->getConfig()->setConfigParam('sOEPayPalSandboxUsername', $sSandBoxUsername);
        $this->getConfig()->setConfigParam('sOEPayPalUsername', $sUsername);
        $this->assertEquals($sResult, $oConfig->getUserName());
    }

    /**
     * Test case for Config::getSignature()
     *
     * @dataProvider providerGetTextConfig
     */
    public function testGetSignature($bSandBoxEnabled, $sSandBoxSignature, $sSignature, $sResult)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $bSandBoxEnabled);
        $this->getConfig()->setConfigParam('sOEPayPalSandboxSignature', $sSandBoxSignature);
        $this->getConfig()->setConfigParam('sOEPayPalSignature', $sSignature);
        $this->assertEquals($sResult, $oConfig->getSignature());
    }

    /**
     * Test case for Config::getTransactionMode()
     */
    public function testGetTransactionMode()
    {
        $sTransMode = 'Sale';
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('sOEPayPalTransactionMode', $sTransMode);

        $this->assertEquals($sTransMode, $oConfig->getTransactionMode());
    }

    /**
     * Test case for Config::isLoggingEnabled()
     */
    public function testIsLoggingEnabled()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', true);
        $this->assertTrue($oConfig->isLoggingEnabled());

        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', false);
        $this->assertFalse($oConfig->isLoggingEnabled());
    }

    /**
     * Test case for Config::isLoggingEnabled()
     */
    public function testIsExpressCheckoutInMiniBasketEnabled()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', true);
        $this->assertTrue($oConfig->isExpressCheckoutInMiniBasketEnabled());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', false);
        $this->assertFalse($oConfig->isExpressCheckoutInMiniBasketEnabled());
    }


    public function providerGetShopLogoUrl()
    {
        $sShopImageLocation = $this->getConfig()->getImageUrl();

        return array(
            array("noLogo", "logo.png", "", false),
            array("shopLogo", "logo.png", "logo_ee.png", $sShopImageLocation . "resized_logo.png"),
            array("customLogo", "logo.png", "logo.png", $sShopImageLocation . "resized_logo.png"),
            array("shopLogo", "login-fb.png", "logo.png", $sShopImageLocation . "login-fb.png"),
            array("customLogo", "logo.png", "login-fb.png", $sShopImageLocation . "login-fb.png"),
        );
    }

    /**
     * Checks if correct shop logo is returned with various options
     *
     * @dataProvider providerGetShopLogoUrl
     *
     * @param string $sOption
     * @param string $sShopLogoImage
     * @param string $sCustomLogoImage
     * @param string $sExpected
     */
    public function testGetShopLogoUrl($sOption, $sShopLogoImage, $sCustomLogoImage, $sExpected)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam("sOEPayPalLogoImageOption", $sOption);
        $this->getConfig()->setConfigParam("sOEPayPalCustomShopLogoImage", $sCustomLogoImage);
        $this->getConfig()->setConfigParam("sShopLogo", $sShopLogoImage);

        $this->assertEquals($sExpected, $oConfig->getLogoUrl());
        $this->_cleanUp();
    }

    /**
     * Incorrect file name provider
     *
     * @return array
     */
    public function providerGetShopLogoUrlIncorrectFilename()
    {
        return array(
            array("shopLogo", "", "notexisting.png"),
            array("customLogo", "notexisting.png", ""),
        );
    }

    /**
     * Checks that getLogoUrl returns false when filename is incorrect
     *
     * @dataProvider providerGetShopLogoUrlIncorrectFilename
     */
    public function testGetShopLogoUrlIncorrectFilename($sOption, $sShopLogoImage, $sCustomLogoImage)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam("sOEPayPalLogoImageOption", $sOption);
        $this->getConfig()->setConfigParam("sOEPayPalCustomShopLogoImage", $sCustomLogoImage);
        $this->getConfig()->setConfigParam("sShopLogo", $sShopLogoImage);

        $this->assertFalse($oConfig->getLogoUrl());
        $this->_cleanUp();
    }

    /**
     * Test case for Config::getIPNCallbackUrl
     */
    public function testGetIPNCallbackUrl()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $sCallbackBaseUrl = $oConfig->getIPNCallbackUrl();
        $this->assertEquals($sCallbackBaseUrl, $this->getConfig()->getCurrentShopUrl() . "index.php?cl=oepaypalipnhandler&fnc=handleRequest&shp=" . $this->getConfig()->getShopId());
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetIPNResponseUrl_sandboxOFF_usePayPalUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($oConfig->getIPNResponseUrl(), 'https://www.paypal.com/cgi-bin/webscr&cmd=_notify-validate');
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetIPNResponseUrl_sandboxON_useSandboxUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($oConfig->getIPNResponseUrl(), 'https://www.sandbox.paypal.com/cgi-bin/webscr&cmd=_notify-validate');
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetUrl_sandboxOFF_returnPayPalUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($oConfig->getUrl(), 'https://www.paypal.com/cgi-bin/webscr');
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetUrl_sandboxON_returnSandboxUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($oConfig->getUrl(), 'https://www.sandbox.paypal.com/cgi-bin/webscr');
    }

    /**
     * Test case for Config::getShopUrl
     */
    public function testGetShopUrl()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $sShopUrl = $oConfig->getShopUrl();
        $this->assertEquals($sShopUrl, $this->getConfig()->getCurrentShopUrl());
    }

    /**
     * Test case for Config::getLang
     */
    public function testGetLang()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oLang = $oConfig->getLang();

        $this->assertTrue(is_a($oLang, Language::class), 'Method getLang() should return language object.');
    }

    /**
     * Test case for Config::getUtils
     */
    public function testGetUtils()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $oUtils = $oConfig->getUtils();

        $this->assertTrue(is_a($oUtils, Utils::class), 'Method getUtils() should return utils object.');
    }

    public function providerIsExpressCheckoutInDetailsPage()
    {
        return array(
            array(true),
            array(false)
        );
    }

    /**
     * Test blOEPayPalECheckoutInDetails config
     *
     * @dataProvider providerIsExpressCheckoutInDetailsPage
     */
    public function testIsExpressCheckoutInDetailsPage($blOEPayPalECheckoutInDetails)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', $blOEPayPalECheckoutInDetails);
        $this->assertEquals($blOEPayPalECheckoutInDetails, $oConfig->isExpressCheckoutInDetailsPage());
    }

    /**
     * Checks if method returns current URL
     */
    public function testGetCurrentUrl()
    {
        $sCurrentUrl = 'http://oxideshop.com/test';
        $oUtilsUrl = $this->getMock(\OxidEsales\Eshop\Core\UtilsUrl::class, array('getCurrentUrl'));
        $oUtilsUrl->expects($this->any())->method('getCurrentUrl')->will($this->returnValue($sCurrentUrl));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsUrl::class, $oUtilsUrl);

        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($sCurrentUrl, $oConfig->getCurrentUrl());
    }


    public function providerGetMaxPayPalDeliveryAmount()
    {
        return array(
            array(40.5, 40.5),
            array(-0.51, -0.51)
        );
    }

    /**
     * Checks max delivery amount setting.
     *
     * @dataProvider providerGetMaxPayPalDeliveryAmount
     */
    public function testGetMaxPayPalDeliveryAmount_configSetWithProperValues_configValue($dMaxAmount, $dExpectedAmount)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('dMaxPayPalDeliveryAmount', $dMaxAmount);
        $this->assertEquals($dExpectedAmount, $oConfig->getMaxPayPalDeliveryAmount());
    }

    public function providerGetMaxPayPalDeliveryAmountBadConfigs()
    {
        return array(
            array(null, 30),
            array(0, 30),
            array(false, 30),
        );
    }

    /**
     * Checks max delivery amount setting.
     *
     * @dataProvider providerGetMaxPayPalDeliveryAmountBadConfigs
     */
    public function testGetMaxPayPalDeliveryAmount_configSetWithFalseValues_30($dMaxAmount, $dExpectedAmount)
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('dMaxPayPalDeliveryAmount', $dMaxAmount);
        $this->assertEquals($dExpectedAmount, $oConfig->getMaxPayPalDeliveryAmount());
    }

    /**
     * Checks max delivery amount setting.
     *
     * @dataProvider providerGetMaxPayPalDeliveryAmount
     */
    public function testGetMaxPayPalDeliveryAmount_default_30()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals(30, $oConfig->getMaxPayPalDeliveryAmount());
    }

    /**
     * Tests if partner code is returned correct
     */
    public function testGetPartnerCode()
    {
        $oConfig = $this->getConfig();
        if ($oConfig->getEdition() == 'EE') {
            $sResult = 'OXID_Cart_EnterpriseECS';
        } else {
            if ($oConfig->getEdition() == 'PE') {
                $sResult = 'OXID_Cart_ProfessionalECS';
            } else {
                if ($oConfig->getEdition() == 'CE') {
                    $sResult = 'OXID_Cart_CommunityECS';
                }
            }
        }
        $oPayPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($sResult, $oPayPalConfig->getPartnerCode());
    }

    public function testGetMobileECDefaultShippingId_notSet_null()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertNull($oConfig->getMobileECDefaultShippingId());
    }

    public function testGetMobileECDefaultShippingId_setPayment_paymentId()
    {
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('sOEPayPalMECDefaultShippingId', 'shippingId');
        $this->assertEquals('shippingId', $oConfig->getMobileECDefaultShippingId());
    }

    public function testIsMobile_mobileDevice_true()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3';
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertTrue($oConfig->isDeviceMobile());
    }

    public function testIsMobile_notMobileDevice_false()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0';
        $oConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertFalse($oConfig->isDeviceMobile());
    }
}
