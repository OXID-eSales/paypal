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
 * Class for User Agent.
 *
 * @package core
 */
class UserAgent
{
    /**
     * Detected device type
     *
     * @var string
     */
    protected $deviceType = null;

    /**
     * Mobile device types.
     *
     * @var string
     */
    protected $mobileDevicesTypes = 'iphone|ipod|android|webos|htc|fennec|iemobile|blackberry|symbianos|opera mobi';

    /**
     * Function returns all supported mobile devices types.
     *
     * @return string
     */
    public function getMobileDeviceTypes()
    {
        return $this->mobileDevicesTypes;
    }

    /**
     * Returns device type: mobile | desktop.
     *
     * @return string
     */
    public function getDeviceType()
    {
        if ($this->deviceType === null) {
            $this->setDeviceType($this->detectDeviceType());
        }

        return $this->deviceType;
    }

    /**
     * Set device type.
     *
     * @param string $deviceType
     */
    public function setDeviceType($deviceType)
    {
        $this->deviceType = $deviceType;
    }

    /**
     * Set mobile device types.
     *
     * @param string $mobileDeviceTypes
     */
    public function setMobileDeviceTypes($mobileDeviceTypes)
    {
        $this->mobileDevicesTypes = $mobileDeviceTypes;
    }

    /**
     * Detects device type from global variable. Device types: mobile, desktop.
     *
     * @return string
     */
    protected function detectDeviceType()
    {
        $deviceType = 'desktop';
        if (preg_match('/(' . $this->getMobileDeviceTypes() . ')/is', $_SERVER['HTTP_USER_AGENT'])) {
            $deviceType = 'mobile';
        }

        return $deviceType;
    }
}
