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

class ShopLogoTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tests default width getter, which is set to 190
     */
    public function testGetWidthDefault()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $this->assertEquals(190, $logo->getWidth());
    }

    /**
     * Tests width getter, when setting
     */
    public function testGetWidthIsSet()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $logo->setWidth(200);
        $this->assertEquals(200, $logo->getWidth());
    }

    /**
     * Tests default height getter, which is set to 60
     */
    public function testGetHeightDefault()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $this->assertEquals(160, $logo->getHeight());
    }

    /**
     * Tests height getter, when setting
     */
    public function testGetHeightIsSet()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $logo->setHeight(200);
        $this->assertEquals(200, $logo->getHeight());
    }

    /**
     * Tests getImageName when value is not set
     */
    public function testGetImageNameNotSet()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $this->assertNull($logo->getImageName());
    }

    /**
     * Tests getImageName when value is  set
     */
    public function testGetImageNameIsSet()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $logo->setImageName("name.png");
        $this->assertEquals("name.png", $logo->getImageName());
    }

    /**
     * Tests getImageDir when value is not set
     */
    public function testGetImageDirNotSet()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $this->assertNull($logo->getImageDir());
    }

    /**
     * Tests getImageDir when value is set
     */
    public function testGetImageDirIsSet()
    {
        $logo = new \OxidEsales\PayPalModule\Core\ShopLogo();
        $logo->setImageDir("/var/www");
        $this->assertEquals("/var/www", $logo->getImageDir());
    }

    /**
     * Data provider for testGetShopLogoUrl
     *
     * @return array
     */
    public function getShopLogoUrlProvider()
    {
        $defaultImageDir = $this->getConfig()->getImageDir();
        $defaultImageDirUrl = $this->getConfig()->getImageUrl();
        $defaultImageName = "logo.png";
        $defaultWidth = 40;
        $defaultHeight = 30;
        $defaultImageHandler = \OxidEsales\Eshop\Core\Registry::getUtilsPic();
        $expectedResized = $defaultImageDirUrl . "resized_logo.png";
        $expectedOriginal = $defaultImageDirUrl . $defaultImageName;

        return array(
            array($defaultImageDir, $defaultImageDirUrl, $defaultImageName, 30, 30, $defaultWidth, $defaultHeight, $defaultImageHandler, $expectedOriginal),
            array($defaultImageDir, $defaultImageDirUrl, $defaultImageName, 50, 50, $defaultWidth, $defaultHeight, $defaultImageHandler, $expectedResized),
            array(null, $defaultImageDirUrl, $defaultImageName, 40, 40, $defaultWidth, $defaultHeight, $defaultImageHandler, false),
            array($defaultImageDir, $defaultImageDirUrl, null, 50, 50, $defaultWidth, $defaultHeight, $defaultImageHandler, false),
            array($defaultImageDir, $defaultImageDirUrl, $defaultImageName, 60, 60, $defaultWidth, $defaultHeight, null, $expectedOriginal),

            array($defaultImageDir . "donotexist", $defaultImageDirUrl, $defaultImageName, 60, 60, $defaultWidth, $defaultHeight, $defaultImageHandler, false),
            array($defaultImageDir, $defaultImageDirUrl, "donotexist.png", 60, 60, $defaultWidth, $defaultHeight, $defaultImageHandler, false),
            array($defaultImageDir, $defaultImageDirUrl, $defaultImageName, 60, 60, 2000, 1000, $defaultImageHandler, $expectedOriginal),
            array($defaultImageDir, $defaultImageDirUrl, $defaultImageName, 2000, 1000, 60, 60, $defaultImageHandler, $expectedResized),

        );
    }

    /**
     * Checks getShopLogo with all possible ways to pass parameters
     *
     * @dataProvider getShopLogoUrlProvider
     */
    public function testGetShopLogoUrl($imageDir, $imageDirUrl, $imageName, $imgWidth, $imgHeight, $width, $height, $imageHandler, $result)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\ShopLogo::class);
        $mockBuilder->setMethods(['resizeImage', 'getImageSize']);
        $logo = $mockBuilder->getMock();
        $logo->expects($this->any())->method('resizeImage')->will($this->returnValue(!empty($imageHandler)));
        $logo->expects($this->any())->method('getImageSize')->will($this->returnValue(array('width' => $imgWidth, 'height' => $imgHeight)));

        $logo->setImageDir($imageDir);
        $logo->setImageDirUrl($imageDirUrl);
        $logo->setImageName($imageName);
        $logo->setWidth($width);
        $logo->setHeight($height);
        $logo->setImageHandler($imageHandler);

        $this->assertEquals($result, $logo->getShopLogoUrl());

        $this->cleanUp($imageName);
    }

    /**
     * Cleans out the images that are created before image tests
     */
    protected function cleanUp($imageName)
    {
        $imgDir = $this->getConfig()->getImageDir();

        $logoDir = $imgDir . "resized_$imageName";
        if (!file_exists($logoDir)) {
            return;
        }

        unlink($logoDir);
    }
}
