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
    protected function cleanUp()
    {
        $imgDir = $this->getConfig()->getImageDir();
        if (!$shopLogo = $this->getConfig()->getConfigParam('sShopLogo')) {
            return;
        }
        $logoDir = $imgDir . "resized_$shopLogo";
        if (!file_exists($logoDir)) {
            return;
        }

        unlink($logoDir);
    }

    /**
     * Check if gets correct module id.
     */
    public function testGetModuleId()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $PayPalId = $config->getModuleId();
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
     * @param string $paramName
     * @param string $shopName
     * @param string $resultName
     */
    public function testGetBrandName($paramName, $shopName, $resultName)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('sOEPayPalBrandName', $paramName);

        $shop = $this->getConfig()->getActiveShop();
        $shop->oxshops__oxname = new \OxidEsales\Eshop\Core\Field($shopName);
        $shop->save();

        $this->assertEquals($resultName, $config->getBrandName());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Config::sendOrderInfoToPayPal()
     */
    public function testSendOrderInfoToPayPal()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSendToPayPal', true);
        $this->assertTrue($config->sendOrderInfoToPayPal());

        $this->getConfig()->setConfigParam('blOEPayPalSendToPayPal', false);
        $this->assertFalse($config->sendOrderInfoToPayPal());
    }

    /**
     * Test case for Config::isGuestBuyEnabled()
     */
    public function testIsGuestBuyEnabled()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalGuestBuyRole', true);
        $this->assertTrue($config->isGuestBuyEnabled());

        $this->getConfig()->setConfigParam('blOEPayPalGuestBuyRole', false);
        $this->assertFalse($config->isGuestBuyEnabled());
    }

    /**
     * Test case for Config::isGiroPayEnabled()
     */
    public function testIsGiroPayEnabled()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertFalse($config->isGiroPayEnabled());
    }

    /**
     * Test case for Config::isSandboxEnabled()
     */
    public function testIsSandboxEnabled()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);
        $this->assertTrue($config->isSandboxEnabled());

        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);
        $this->assertFalse($config->isSandboxEnabled());
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
    public function testGetHost($sandboxEnabled, $payPalSandboxHost, $payPalHost, $result)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandboxEnabled);

        if (!empty($payPalSandboxHost)) {
            $config->setPayPalSandboxHost($payPalSandboxHost);
        }
        if (!empty($payPalHost)) {
            $config->setPayPalHost($payPalHost);
        }

        $this->assertEquals($result, $config->getHost());
    }

    public function testGetPayPalHost_setWithSetter_setValue()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $config->setPayPalHost('PayPalHost');

        $this->assertEquals('PayPalHost', $config->getPayPalHost(), 'Getter must return what is set in setter.');
    }

    public function testGetPayPalHost_default_definedClassAttribute()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('api-3t.paypal.com', $config->getPayPalHost());
    }

    public function testGetPayPalHost_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalHost', 'configHost');
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('configHost', $config->getPayPalHost());
    }

    public function testGetPayPalSandboxHost_setWithSetter_setValue()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $config->setPayPalSandboxHost('PayPalSandboxHost');

        $this->assertEquals('PayPalSandboxHost', $config->getPayPalSandboxHost());
    }

    public function testGetPayPalSandboxHost_default_definedClassAttribute()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('api-3t.sandbox.paypal.com', $config->getPayPalSandboxHost());
    }

    public function testGetPayPalSandboxHost_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalSandboxHost', 'configHost');
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('configHost', $config->getPayPalSandboxHost());
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
    public function testApiUrl($sandBoxEnabled, $sandBoxApiUrl, $apiUrl, $result)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandBoxEnabled);

        if (!empty($sandBoxApiUrl)) {
            $config->setPayPalSandboxApiUrl($sandBoxApiUrl);
        }
        if (!empty($apiUrl)) {
            $config->setPayPalApiUrl($apiUrl);
        }

        $this->assertEquals($result, $config->getApiUrl());
    }

    public function testGetPayPalSandboxApiUrl_setWithSetter_setValue()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $config->setPayPalSandboxApiUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $config->getPayPalSandboxApiUrl());
    }

    public function testGetPayPalSandboxApiUrl_default_definedClassAttribute()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $config->getPayPalSandboxApiUrl());
    }

    public function testGetPayPalSandboxApiUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalSandboxApiUrl', 'apiConfigHost');
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('apiConfigHost', $config->getPayPalSandboxApiUrl());
    }

    public function testGetPayPalApiUrl_setWithSetter_setValue()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $config->setPayPalApiUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $config->getPayPalApiUrl());
    }

    public function testGetPayPalApiUrl_default_definedClassAttribute()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://api-3t.paypal.com/nvp', $config->getPayPalApiUrl());
    }

    public function testGetPayPalApiUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalApiUrl', 'apiConfigHost');
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('apiConfigHost', $config->getPayPalApiUrl());
    }

    public function testGetPayPalSandboxUrl_setWithSetter_setValue()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $config->setPayPalSandboxUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $config->getPayPalSandboxUrl());
    }

    public function testGetPayPalSandboxUrl_default_definedClassAttribute()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://www.sandbox.paypal.com/cgi-bin/webscr', $config->getPayPalSandboxUrl());
    }

    public function testGetPayPalSandboxUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalSandboxUrl', 'ConfigHost');
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('ConfigHost', $config->getPayPalSandboxUrl());
    }

    public function testGetPayPalUrl_setWithSetter_setValue()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $config->setPayPalUrl('ApiPayPalSandboxHost');

        $this->assertEquals('ApiPayPalSandboxHost', $config->getPayPalUrl());
    }

    public function testGetPayPalUrl_default_definedClassAttribute()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr', $config->getPayPalUrl());
    }

    public function testGetPayPalUrl_overrideWithConfig_configValue()
    {
        $this->getConfig()->setConfigParam('sPayPalUrl', 'ConfigUrl');
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals('ConfigUrl', $config->getPayPalUrl());
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
    public function testGetPayPalCommunicationUrl($sandBoxEnabled, $sandBoxApiUrl, $apiUrl, $token, $userAction, $result)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandBoxEnabled);

        if (!empty($sandBoxApiUrl)) {
            $config->setPayPalSandboxUrl($sandBoxApiUrl);
        }
        if (!empty($apiUrl)) {
            $config->setPayPalUrl($apiUrl);
        }

        $this->assertEquals($result, $config->getPayPalCommunicationUrl($token, $userAction));
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
    public function testGetPassword($sandBoxEnabled, $sandBoxPassword, $password, $result)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandBoxEnabled);
        $this->getConfig()->setConfigParam('sOEPayPalSandboxPassword', $sandBoxPassword);
        $this->getConfig()->setConfigParam('sOEPayPalPassword', $password);
        $this->assertEquals($result, $config->getPassword());
    }

    /**
     * Test case for Config::getUserName()
     *
     * @dataProvider providerGetTextConfig
     */
    public function testGetUserName($sandBoxEnabled, $sandBoxUsername, $username, $result)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandBoxEnabled);
        $this->getConfig()->setConfigParam('sOEPayPalSandboxUsername', $sandBoxUsername);
        $this->getConfig()->setConfigParam('sOEPayPalUsername', $username);
        $this->assertEquals($result, $config->getUserName());
    }

    /**
     * Test case for Config::getSignature()
     *
     * @dataProvider providerGetTextConfig
     */
    public function testGetSignature($sandBoxEnabled, $sandBoxSignature, $signature, $result)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', $sandBoxEnabled);
        $this->getConfig()->setConfigParam('sOEPayPalSandboxSignature', $sandBoxSignature);
        $this->getConfig()->setConfigParam('sOEPayPalSignature', $signature);
        $this->assertEquals($result, $config->getSignature());
    }

    /**
     * Test case for Config::getTransactionMode()
     */
    public function testGetTransactionMode()
    {
        $transMode = 'Sale';
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('sOEPayPalTransactionMode', $transMode);

        $this->assertEquals($transMode, $config->getTransactionMode());
    }

    /**
     * Test case for Config::isLoggingEnabled()
     */
    public function testIsLoggingEnabled()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', true);
        $this->assertTrue($config->isLoggingEnabled());

        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', false);
        $this->assertFalse($config->isLoggingEnabled());
    }

    /**
     * Test case for Config::isLoggingEnabled()
     */
    public function testIsExpressCheckoutInMiniBasketEnabled()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', true);
        $this->assertTrue($config->isExpressCheckoutInMiniBasketEnabled());

        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInMiniBasket', false);
        $this->assertFalse($config->isExpressCheckoutInMiniBasketEnabled());
    }


    public function providerGetShopLogoUrl()
    {
        $shopImageLocation = $this->getConfig()->getImageUrl();

        return array(
            array("noLogo", "logo.png", "", false),
            array("shopLogo", "logo.png", "logo_ee.png", $shopImageLocation . "resized_logo.png"),
            array("customLogo", "logo.png", "logo.png", $shopImageLocation . "resized_logo.png"),
            array("shopLogo", "login-fb.png", "logo.png", $shopImageLocation . "login-fb.png"),
            array("customLogo", "logo.png", "login-fb.png", $shopImageLocation . "login-fb.png"),
        );
    }

    /**
     * Checks if correct shop logo is returned with various options
     *
     * @dataProvider providerGetShopLogoUrl
     *
     * @param string $option
     * @param string $shopLogoImage
     * @param string $customLogoImage
     * @param string $expected
     */
    public function testGetShopLogoUrl($option, $shopLogoImage, $customLogoImage, $expected)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam("sOEPayPalLogoImageOption", $option);
        $this->getConfig()->setConfigParam("sOEPayPalCustomShopLogoImage", $customLogoImage);
        $this->getConfig()->setConfigParam("sShopLogo", $shopLogoImage);

        $this->assertEquals($expected, $config->getLogoUrl());
        $this->cleanUp();
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
    public function testGetShopLogoUrlIncorrectFilename($option, $shopLogoImage, $customLogoImage)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam("sOEPayPalLogoImageOption", $option);
        $this->getConfig()->setConfigParam("sOEPayPalCustomShopLogoImage", $customLogoImage);
        $this->getConfig()->setConfigParam("sShopLogo", $shopLogoImage);

        $this->assertFalse($config->getLogoUrl());
        $this->cleanUp();
    }

    /**
     * Test case for Config::getIPNCallbackUrl
     */
    public function testGetIPNCallbackUrl()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $callbackBaseUrl = $config->getIPNCallbackUrl();
        $this->assertEquals($callbackBaseUrl, $this->getConfig()->getCurrentShopUrl() . "index.php?cl=oepaypalipnhandler&fnc=handleRequest&shp=" . $this->getConfig()->getShopId());
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetIPNResponseUrl_sandboxOFF_usePayPalUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($config->getIPNResponseUrl(), 'https://www.paypal.com/cgi-bin/webscr&cmd=_notify-validate');
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetIPNResponseUrl_sandboxON_useSandboxUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($config->getIPNResponseUrl(), 'https://www.sandbox.paypal.com/cgi-bin/webscr&cmd=_notify-validate');
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetUrl_sandboxOFF_returnPayPalUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);

        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($config->getUrl(), 'https://www.paypal.com/cgi-bin/webscr');
    }

    /**
     * Test case for Config::getIPNUrl()
     */
    public function testGetUrl_sandboxON_returnSandboxUrl()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($config->getUrl(), 'https://www.sandbox.paypal.com/cgi-bin/webscr');
    }

    /**
     * Test case for Config::getShopUrl
     */
    public function testGetShopUrl()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $shopUrl = $config->getShopUrl();
        $this->assertEquals($shopUrl, $this->getConfig()->getCurrentShopUrl());
    }

    /**
     * Test case for Config::getLang
     */
    public function testGetLang()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $lang = $config->getLang();

        $this->assertTrue(is_a($lang, Language::class), 'Method getLang() should return language object.');
    }

    /**
     * Test case for Config::getUtils
     */
    public function testGetUtils()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $utils = $config->getUtils();

        $this->assertTrue(is_a($utils, Utils::class), 'Method getUtils() should return utils object.');
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
    public function testIsExpressCheckoutInDetailsPage($oEPayPalECheckoutInDetails)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('blOEPayPalECheckoutInDetails', $oEPayPalECheckoutInDetails);
        $this->assertEquals($oEPayPalECheckoutInDetails, $config->isExpressCheckoutInDetailsPage());
    }

    /**
     * Checks if method returns current URL
     */
    public function testGetCurrentUrl()
    {
        $currentUrl = 'http://oxideshop.com/test';

        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsUrl::class);
        $mockBuilder->setMethods(['getCurrentUrl']);
        $utilsUrl = $mockBuilder->getMock();
        $utilsUrl->expects($this->any())->method('getCurrentUrl')->will($this->returnValue($currentUrl));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsUrl::class, $utilsUrl);

        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($currentUrl, $config->getCurrentUrl());
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
    public function testGetMaxPayPalDeliveryAmount_configSetWithProperValues_configValue($maxAmount, $expectedAmount)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('dMaxPayPalDeliveryAmount', $maxAmount);
        $this->assertEquals($expectedAmount, $config->getMaxPayPalDeliveryAmount());
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
    public function testGetMaxPayPalDeliveryAmount_configSetWithFalseValues_30($maxAmount, $expectedAmount)
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('dMaxPayPalDeliveryAmount', $maxAmount);
        $this->assertEquals($expectedAmount, $config->getMaxPayPalDeliveryAmount());
    }

    /**
     * Checks max delivery amount setting.
     *
     * @dataProvider providerGetMaxPayPalDeliveryAmount
     */
    public function testGetMaxPayPalDeliveryAmount_default_30()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals(30, $config->getMaxPayPalDeliveryAmount());
    }

    /**
     * Tests if partner code is returned correct.
     * Testing non-shortcut partner codes here.
     */
    public function testGetPartnerCode()
    {
        $config = $this->getConfig();
        if ($config->getEdition() == 'EE') {
            $result = 'OXID_Cart_EnterpriseECS';
        } else {
            if ($config->getEdition() == 'PE') {
                $result = 'OXID_Cart_ProfessionalECS';
            } else {
                if ($config->getEdition() == 'CE') {
                    $result = 'OXID_Cart_CommunityECS';
                }
            }
        }
        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($result, $payPalConfig->getPartnerCode());
    }

    /**
     * Tests if partner code is returned correct in case the shortcut was used.
     */
    public function testGetShortcutPartnerCode()
    {
        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable(\OxidEsales\PayPalModule\Core\Config::OEPAYPAL_TRIGGER_NAME,
            \OxidEsales\PayPalModule\Core\Config::OEPAYPAL_SHORTCUT);
        $expected = 'Oxid_Cart_ECS_Shortcut';

        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertEquals($expected, $payPalConfig->getPartnerCode());
    }

    public function testGetMobileECDefaultShippingId_notSet_null()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertNull($config->getMobileECDefaultShippingId());
    }

    public function testGetMobileECDefaultShippingId_setPayment_paymentId()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->getConfig()->setConfigParam('sOEPayPalMECDefaultShippingId', 'shippingId');
        $this->assertEquals('shippingId', $config->getMobileECDefaultShippingId());
    }

    public function testIsMobile_mobileDevice_true()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3';
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertTrue($config->isDeviceMobile());
    }

    public function testIsMobile_notMobileDevice_false()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0';
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $this->assertFalse($config->isDeviceMobile());
    }
}
