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
 * Class for User Agent.
 *
 * @package core
 */
class oePayPalUserAgent
{
    /**
     * Detected device type
     *
     * @var string
     */
    protected $_sDeviceType = null;

    /**
     * Mobile device types
     *
     * @var string
     */
    protected $_sMobileDevicesTypes = 'iphone|ipod|android|webos|htc|fennec|iemobile|blackberry|symbianos|opera mobi';

    /**
     * Function returns all supported mobile devices types
     *
     * @return string
     */
    public function getMobileDeviceTypes()
    {
        return $this->_sMobileDevicesTypes;
    }

    /**
     * Returns device type: mobile | desktop
     *
     * @return string
     */
    public function getDeviceType()
    {
        if ( $this->_sDeviceType === null ) {
            $this->setDeviceType( $this->_detectDeviceType() );
        }

        return $this->_sDeviceType;
    }

    /**
     * Set device type
     */
    public function setDeviceType( $sDeviceType )
    {
        $this->_sDeviceType = $sDeviceType;
    }

    /**
     * Set mobile device types
     */
    public function setMobileDeviceTypes( $sMobileDeviceTypes )
    {
        $this->_sMobileDevicesTypes = $sMobileDeviceTypes;
    }

    /**
     * Detects device type from global variable. Device types: mobile, desktop
     *
     * @return string
     */
    protected function _detectDeviceType()
    {
        $sDeviceType = 'desktop';
        if ( preg_match( '/('. $this->getMobileDeviceTypes() .')/is', $_SERVER['HTTP_USER_AGENT'] ) ){
            $sDeviceType = 'mobile';
        }
        return $sDeviceType;
    }
}