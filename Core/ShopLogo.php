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
 * PayPal shop logo class.
 */
class ShopLogo
{
    /**
     * Suffix for image resizing.
     *
     * @var string
     */
    protected $_suffix = "resized_";

    /**
     * Image directory to work in.
     *
     * @var string
     */
    protected $imageDir = null;

    /**
     * Image directory to work in.
     *
     * @var string
     */
    protected $imageDirUrl = null;

    /**
     * Image name.
     *
     * @var string
     */
    protected $imageName = null;

    /**
     * Path to an image, not null only when it exists.
     *
     * @var string
     */
    protected $imagePath = null;
    /**
     * Image maximum width.
     *
     * @var int
     */
    protected $width = 190;

    /**
     * Image maximum height.
     *
     * @var int
     */
    protected $height = 160;

    /**
     * Provided image handler.
     *
     * @var \OxidEsales\Eshop\Core\UtilsPic
     */
    protected $imageHandler = null;

    /**
     * Set image handler to handle images.
     * Needs to have resizeImage method.
     *
     * @param \OxidEsales\Eshop\Core\UtilsPic $imageHandler
     */
    public function setImageHandler($imageHandler)
    {
        $this->imageHandler = $imageHandler;
    }

    /**
     * Returns image handler object.
     *
     * @return object|oxUtilsPic
     */
    public function getImageHandler()
    {
        return $this->imageHandler;
    }

    /**
     * Sets image maximum width.
     *
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Return image maximum width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Sets image maximum height.
     *
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Return image maximum height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Calculates if logo should be resized.
     *
     * @return bool
     */
    protected function getResize()
    {
        $resize = false;
        $imagePath = $this->getImagePath();
        if ($imagePath) {
            $imageSize = $this->getImageSize($imagePath);

            if ($imageSize['width'] > $this->getWidth() ||
                $imageSize['height'] > $this->getHeight()
            ) {
                $resize = true;
            }
        }

        return $resize;
    }

    /**
     * Sets image directory.
     *
     * @param string $imagePath
     */
    public function setImageDir($imagePath)
    {
        $this->imageDir = $imagePath;
    }

    /**
     * Returns image directory.
     *
     * @return string
     */
    public function getImageDir()
    {
        return $this->imageDir;
    }

    /**
     * Set image directory URL.
     *
     * @param string $imageDirUrl
     */
    public function setImageDirUrl($imageDirUrl)
    {
        $this->imageDirUrl = $imageDirUrl;
    }

    /**
     * Getter for image directory URL.
     *
     * @return string
     */
    public function getImageDirUrl()
    {
        return $this->imageDirUrl;
    }

    /**
     * Set name of an image.
     *
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * Get name of an image.
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Gives new name for image to be resized.
     *
     * @return string
     */
    protected function getResizedImageName()
    {
        $resizedImageName = "";
        if ($this->getImageName()) {
            $resizedImageName = $this->_suffix . $this->getImageName();
        }

        return $resizedImageName;
    }

    /**
     * Returns path to an image.
     *
     * @return string
     */
    protected function getImagePath()
    {
        if (null == $this->imagePath) {
            $dir = $this->getImageDir();
            $name = $this->getImageName();

            if ($dir && $name && $this->fileExists($dir . $name)) {
                $this->imagePath = $dir . $name;
            }
        }

        return $this->imagePath;
    }

    /**
     * Returns path to an image.
     *
     * @return string
     */
    protected function getImageUrl()
    {
        $imageUrl = "";

        if ($this->getImagePath()) {
            $imageUrl = $this->getImageDirUrl() . $this->getImageName();
        }

        return $imageUrl;
    }

    /**
     * Returns resized image path.
     *
     * @return string
     */
    protected function getResizedImagePath()
    {
        $resizedImagePath = "";
        $dir = $this->getImageDir();
        $name = $this->getResizedImageName();

        if ($dir && $name) {
            $resizedImagePath = $dir . $name;
        }

        return $resizedImagePath;
    }

    /**
     * Returns resized image path.
     *
     * @return string
     */
    protected function getResizedImageUrl()
    {
        $imageUrl = "";

        if ($this->getResizedImagePath()) {
            $imageUrl = $this->getImageDirUrl() . $this->getResizedImageName();
        }

        return $imageUrl;
    }

    /**
     * Get logo image path for PayPal express checkout.
     *
     * @return string
     */
    public function getShopLogoUrl()
    {
        $imagePath = $this->getImageUrl();
        $resizedImagePath = $this->getResizedImageUrl();

        if ($this->getResize()) {
            $shopLogoPath = $resizedImagePath;

            if (!$this->fileExists($resizedImagePath)) {
                if (!$this->resizeImage()) {
                    // fallback to original image if can not be resized
                    $shopLogoPath = $imagePath;
                }
            }
        } else {
            $shopLogoPath = $imagePath;
        }

        return $shopLogoPath;
    }

    /**
     * Checks if given image file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function fileExists($path)
    {
        return file_exists($path);
    }

    /**
     * Returns array with width and height of given image
     *
     * @param string $imagePath
     *
     * @return array
     */
    protected function getImageSize($imagePath)
    {
        $imageSize = getimagesize($imagePath);

        return array(
            'width'  => $imageSize[0],
            'height' => $imageSize[1]
        );
    }

    /**
     * Resizes logo if needed to the size provided
     * Returns false when image does'nt exist, or can't be resized
     * Returns original image name when image is within boundaries
     * Returns resized image name when image is too large than defined
     *
     * @return bool
     */
    protected function resizeImage()
    {
        $resized = false;

        if ($utilsPic = $this->getImageHandler()) {
            // checks if image can be resized, and resizes the image
            if ($utilsPic->resizeImage($this->getImagePath(), $this->getResizedImagePath(), $this->getWidth(), $this->getHeight())) {
                $resized = true;
            }
        }

        return $resized;
    }
}
