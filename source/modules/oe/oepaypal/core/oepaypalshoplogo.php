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

/**
 * PayPal shop logo class
 */
class oePayPalShopLogo
{
    /**
     * Suffix for image resizing
     *
     * @var string
     */
    protected $_suffix = "resized_";

    /**
     * Image directory to work in
     *
     * @var string
     */
    protected $_sImageDir = null;

    /**
     * Image directory to work in
     *
     * @var string
     */
    protected $_sImageDirUrl = null;

    /**
     * Image name
     * @var string
     */
    protected $_sImageName = null;

    /**
     * Path to an image, not null only when it exists
     *
     * @var string
     */
    protected $_sImagePath = null;
    /**
     * Image maximum width
     *
     * @var int
     */
    protected $_iWidth = 190;

    /**
     * Image maximum height
     *
     * @var int
     */
    protected $_iHeight = 160;

    /**
     * Provided image handler
     *
     * @var object|oxUtilsPic
     */
    protected $_oImageHandler = null;

    /**
     * Set image handler to handle images
     * Needs to have resizeImage method
     *
     * @param $oImageHandler
     */
    public function setImageHandler( $oImageHandler )
    {
        $this->_oImageHandler = $oImageHandler;
    }

    /**
     * Returns image handler object
     *
     * @return object|oxUtilsPic
     */
    public function getImageHandler()
    {
        return $this->_oImageHandler;
    }

    /**
     * Sets image maximum width
     *
     * @param $iWidth
     */
    public function setWidth( $iWidth )
    {
        $this->_iWidth = $iWidth;
    }

    /**
     * Return image maximum width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_iWidth;
    }

    /**
     * Sets image maximum height
     *
     * @param $iHeight
     */
    public function setHeight( $iHeight )
    {
        $this->_iHeight = $iHeight;
    }

    /**
     * Return image maximum height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_iHeight;
    }

    /**
     * Calculates if logo should be resized
     *
     * @return bool
     */
    protected function _getResize()
    {
        $blResize = false;
        $sImagePath = $this->_getImagePath();
        if ( $sImagePath )
        {
            $aImageSize = $this->_getImageSize( $sImagePath );

            if ( $aImageSize['width'] > $this->getWidth() ||
                $aImageSize['height'] > $this->getHeight() ){

                $blResize = true;
            }
        }

        return $blResize;
    }

    /**
     * Sets image directory
     *
     * @param $sImagePath string
     */
    public function setImageDir( $sImagePath )
    {
        $this->_sImageDir = $sImagePath;
    }

    /**
     * Returns image directory
     *
     * @return string
     */
    public function getImageDir()
    {
        return $this->_sImageDir;
    }

    /**
     * @param string $sImageDirUrl
     */
    public function setImageDirUrl( $sImageDirUrl )
    {
        $this->_sImageDirUrl = $sImageDirUrl;
    }

    /**
     * @return string
     */
    public function getImageDirUrl()
    {
        return $this->_sImageDirUrl;
    }

    /**
     * Set name of an image
     *
     * @param $sImageName string
     */
    public function setImageName( $sImageName )
    {
        $this->_sImageName = $sImageName;
    }

    /**
     * Get name of an image
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->_sImageName;
    }

    /**
     * Gives new name for image to be resized
     *
     * @return string
     */
    protected function _getResizedImageName()
    {
        $sResizedImageName = "";
        if ( $this->getImageName() ) {
            $sResizedImageName = $this->_suffix . $this->getImageName();
        }

        return $sResizedImageName;
    }

    /**
     * Returns path to an image
     *
     * @return null|string
     */
    protected function _getImagePath()
    {
        if ( null == $this->_sImagePath ) {

            $sDir = $this->getImageDir();
            $sName = $this->getImageName();

            if ( $sDir && $sName  && $this->_fileExists( $sDir . $sName ) ) {
                $this->_sImagePath = $sDir . $sName;
            }
        }
        return $this->_sImagePath;
    }

    /**
     * Returns path to an image
     *
     * @return null|string
     */
    protected function _getImageUrl()
    {
        $sImageUrl = "";

        if ( $this->_getImagePath() ) {
            $sImageUrl = $this->getImageDirUrl() . $this->getImageName();
        }

        return $sImageUrl;
    }

    /**
     * Returns resized image path
     *
     * @return null|string
     */
    protected function _getResizedImagePath()
    {
        $sResizedImagePath = "";
        $sDir = $this->getImageDir();
        $sName = $this->_getResizedImageName();

        if ( $sDir && $sName  ) {
            $sResizedImagePath = $sDir . $sName;
        }
        return $sResizedImagePath;
    }

    /**
     * Returns resized image path
     *
     * @return null|string
     */
    protected function _getResizedImageUrl()
    {
        $sImageUrl = "";

        if ( $this->_getResizedImagePath() ) {
            $sImageUrl = $this->getImageDirUrl() . $this->_getResizedImageName();
        }

        return $sImageUrl;
    }

    /**
     * Get logo image path for PayPal express checkout
     *
     * @return string
     */
    public function getShopLogoUrl()
    {
        $sImagePath = $this->_getImageUrl();
        $sResizedImagePath = $this->_getResizedImageUrl();

        if ( $this->_getResize() ) {
            $sShopLogoPath = $sResizedImagePath;

            if ( !$this->_fileExists( $sResizedImagePath ) ) {
                if ( !$this->_resizeImage( $sImagePath, $sResizedImagePath ) ) {
                    // fallback to original image if can not be resized
                    $sShopLogoPath = $sImagePath;
                }
            }
        } else {
            $sShopLogoPath = $sImagePath;
        }

        return $sShopLogoPath;
    }

    /**
     * Checks if given image file exists
     * @param $sPath
     * @return bool
     */
    protected function _fileExists( $sPath )
    {
        return file_exists( $sPath );
    }
    /**
     * Returns array with width and height of given image
     *
     * @param $sImagePath
     * @return array
     */
    protected function _getImageSize( $sImagePath )
    {
        $aImageSize = getimagesize( $sImagePath );

        return array (
            'width' => $aImageSize[0] ,
            'height' => $aImageSize[1]
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
    protected function _resizeImage()
    {
        $blResized = false;

        if ( $oUtilsPic = $this->getImageHandler() ) {
            // checks if image can be resized, and resizes the image
            if ( $oUtilsPic->resizeImage( $this->_getImagePath(), $this->_getResizedImagePath(), $this->getWidth(), $this->getHeight() ) ) {
                $blResized = true;
            }
        }
        return $blResized;
    }
}