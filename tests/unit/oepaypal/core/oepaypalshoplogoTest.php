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

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';

class Unit_oePayPal_core_oePayPalShopLogoTest extends OxidTestCase
{
    /**
     * Tests default width getter, which is set to 190
     */
    public function testGetWidthDefault()
    {
        $oLogo = new oePayPalShopLogo();
        $this->assertEquals(190, $oLogo->getWidth());
    }

    /**
     * Tests width getter, when setting
     */
    public function testGetWidthIsSet()
    {
        $oLogo = new oePayPalShopLogo();
        $oLogo->setWidth(200);
        $this->assertEquals(200, $oLogo->getWidth());
    }

    /**
     * Tests default height getter, which is set to 60
     */
    public function testGetHeightDefault()
    {
        $oLogo = new oePayPalShopLogo();
        $this->assertEquals(160, $oLogo->getHeight());
    }

    /**
     * Tests height getter, when setting
     */
    public function testGetHeightIsSet()
    {
        $oLogo = new oePayPalShopLogo();
        $oLogo->setHeight(200);
        $this->assertEquals(200, $oLogo->getHeight());
    }

    /**
     * Tests getImageName when value is not set
     */
    public function testGetImageNameNotSet()
    {
        $oLogo = new oePayPalShopLogo();
        $this->assertNull($oLogo->getImageName());
    }

    /**
     * Tests getImageName when value is  set
     */
    public function testGetImageNameIsSet()
    {
        $oLogo = new oePayPalShopLogo();
        $oLogo->setImageName("name.png");
        $this->assertEquals("name.png", $oLogo->getImageName());
    }

    /**
     * Tests getImageDir when value is not set
     */
    public function testGetImageDirNotSet()
    {
        $oLogo = new oePayPalShopLogo();
        $this->assertNull($oLogo->getImageDir());
    }

    /**
     * Tests getImageDir when value is set
     */
    public function testGetImageDirIsSet()
    {
        $oLogo = new oePayPalShopLogo();
        $oLogo->setImageDir("/var/www");
        $this->assertEquals("/var/www", $oLogo->getImageDir());
    }

    /**
     * Data provider for testGetShopLogoUrl
     * @return array
     */
    public function getShopLogoUrlProvider()
    {
        $sDefaultImageDir = $this->getConfig()->getImageDir();
        $sDefaultImageDirUrl = $this->getConfig()->getImageUrl();
        $sDefaultImageName = "logo.png";
        $iDefaultWidth = 40;
        $iDefaultHeight = 30;
        $oDefaultImageHandler = oxRegistry::get("oxUtilsPic");
        $sExpectedResized = $sDefaultImageDirUrl . "resized_logo.png";
        $sExpectedOriginal = $sDefaultImageDirUrl . $sDefaultImageName;

        return array(
            array($sDefaultImageDir, $sDefaultImageDirUrl, $sDefaultImageName, 30, 30, $iDefaultWidth, $iDefaultHeight, $oDefaultImageHandler, $sExpectedOriginal),
            array($sDefaultImageDir, $sDefaultImageDirUrl, $sDefaultImageName, 50, 50, $iDefaultWidth, $iDefaultHeight, $oDefaultImageHandler, $sExpectedResized),
            array(null, $sDefaultImageDirUrl, $sDefaultImageName, 40, 40, $iDefaultWidth, $iDefaultHeight, $oDefaultImageHandler, false),
            array($sDefaultImageDir, $sDefaultImageDirUrl, null, 50, 50, $iDefaultWidth, $iDefaultHeight, $oDefaultImageHandler, false),
            array($sDefaultImageDir, $sDefaultImageDirUrl, $sDefaultImageName, 60, 60, $iDefaultWidth, $iDefaultHeight, null, $sExpectedOriginal),

            array($sDefaultImageDir . "donotexist", $sDefaultImageDirUrl, $sDefaultImageName, 60, 60, $iDefaultWidth, $iDefaultHeight, $oDefaultImageHandler, false),
            array($sDefaultImageDir, $sDefaultImageDirUrl, "donotexist.png", 60, 60, $iDefaultWidth, $iDefaultHeight, $oDefaultImageHandler, false),
            array($sDefaultImageDir, $sDefaultImageDirUrl, $sDefaultImageName, 60, 60, 2000, 1000, $oDefaultImageHandler, $sExpectedOriginal),
            array($sDefaultImageDir, $sDefaultImageDirUrl, $sDefaultImageName, 2000, 1000, 60, 60, $oDefaultImageHandler, $sExpectedResized),

        );
    }

    /**
     * Checks getShopLogo with all possible ways to pass parameters
     *
     * @dataProvider getShopLogoUrlProvider
     */
    public function testGetShopLogoUrl($sImageDir, $sImageDirUrl, $sImageName, $iImgWidth, $iImgHeight, $iWidth, $iHeight, $oImageHandler, $sResult)
    {
        $oLogo = $this->getMock('oePayPalShopLogo', array('_resizeImage', '_getImageSize'));
        $oLogo->expects($this->any())->method('_resizeImage')->will($this->returnValue(!empty($oImageHandler)));
        $oLogo->expects($this->any())->method('_getImageSize')->will($this->returnValue(array('width' => $iImgWidth, 'height' => $iImgHeight)));

        $oLogo->setImageDir($sImageDir);
        $oLogo->setImageDirUrl($sImageDirUrl);
        $oLogo->setImageName($sImageName);
        $oLogo->setWidth($iWidth);
        $oLogo->setHeight($iHeight);
        $oLogo->setImageHandler($oImageHandler);

        $this->assertEquals($sResult, $oLogo->getShopLogoUrl());

        $this->_cleanUp($sImageName);
    }

    /**
     * Cleans out the images that are created before image tests
     */
    protected function _cleanUp($sImageName)
    {
        $sImgDir = $this->getConfig()->getImageDir();

        $sLogoDir = $sImgDir . "resized_$sImageName";
        if (!file_exists($sLogoDir)) {
            return;
        }

        unlink($sLogoDir);
    }
}