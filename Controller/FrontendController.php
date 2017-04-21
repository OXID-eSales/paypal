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
namespace OxidEsales\PayPalModule\Controller;
/**
 * Main PayPal controller
 */
class FrontendController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    /**
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $_oRequest = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\Logger
     */
    protected $_oLogger = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\Config
     */
    protected $_oPayPalConfig = null;

    /**
     * Return request object
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        if (is_null($this->_oRequest)) {
            $this->_oRequest = oxNew(\OxidEsales\PayPalModule\Core\Request::class);
        }

        return $this->_oRequest;
    }

    /**
     * Return PayPal logger
     *
     * @return \OxidEsales\PayPalModule\Core\Logger
     */
    public function getLogger()
    {
        if (is_null($this->_oLogger)) {
            $this->_oLogger = oxNew(\OxidEsales\PayPalModule\Core\Logger::class);
            $this->_oLogger->setLoggerSessionId($this->getSession()->getId());
        }

        return $this->_oLogger;
    }

    /**
     * Return PayPal config
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     */
    public function getPayPalConfig()
    {
        if (is_null($this->_oPayPalConfig)) {
            $this->setPayPalConfig(oxNew(\OxidEsales\PayPalModule\Core\Config::class));
        }

        return $this->_oPayPalConfig;
    }

    /**
     * Set PayPal config
     *
     * @param \OxidEsales\PayPalModule\Core\Config $oPayPalConfig config
     */
    public function setPayPalConfig($oPayPalConfig)
    {
        $this->_oPayPalConfig = $oPayPalConfig;
    }


    /**
     * Logs passed value.
     *
     * @param mixed $mValue
     */
    public function log($mValue)
    {
        if ($this->getPayPalConfig()->isLoggingEnabled()) {
            $this->getLogger()->log($mValue);
        }
    }
}
