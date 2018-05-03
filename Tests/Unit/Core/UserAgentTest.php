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
 * Tests for oeThemeSwitcherUserAgent class
 */
class UserAgentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function providerIsMobile()
    {
        return array(
            array('Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3', 'mobile'),
            array('Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET4.0C)', 'desktop'),
            array('Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A100a Safari/419.3', 'mobile'),
            array('Mozilla/5.0 (webOS/1.0; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pre/1.0', 'mobile'),
            array('Mozilla/4.0 (compatible; MSIE 8.0; AOL 9.1; AOLBuild 4334.34; Windows NT 5.1; SV1; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 1.1.4322)', 'desktop'),
            array('Mozilla/5.0 (Linux; U; Android 0.5; en-us) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3', 'mobile'),
            array('Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0', 'desktop')
        );
    }

    /**
     * Check if given device type is mobile
     *
     * @dataProvider providerIsMobile
     */
    public function testDeviceType_Detect($userAgent, $type)
    {
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
        $userAgent = new \OxidEsales\PayPalModule\Core\UserAgent();

        $this->assertEquals($type, $userAgent->getDeviceType());
    }

    /**
     * Tests getter if it is not null and if there is separators
     */
    public function testGetMobileDevicesTypes_NotNullAndWithSeparators()
    {
        $userAgent = new \OxidEsales\PayPalModule\Core\UserAgent();
        $mobileDevicesTypes = $userAgent->getMobileDeviceTypes();
        $this->assertContains('iphone|', $mobileDevicesTypes);
    }

    /**
     * Tests for mobile device types setter and getter
     */
    public function testGetMobileDevicesTypes_SetAndGet()
    {
        $userAgent = new \OxidEsales\PayPalModule\Core\UserAgent();
        $userAgent->setMobileDeviceTypes('testDevice1|testDevice2');
        $this->assertEquals('testDevice1|testDevice2', $userAgent->getMobileDeviceTypes());
    }

    /**
     * Tests device type for setter and getter
     */
    public function testGetDeviceType_SetAndGet()
    {
        $userAgent = new \OxidEsales\PayPalModule\Core\UserAgent();
        $userAgent->setDeviceType('mobile');
        $this->assertEquals('mobile', $userAgent->getDeviceType());
    }
}
