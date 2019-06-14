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
namespace OxidEsales\PayPalModule\Controller;

/**
 * Main PayPal controller
 */
class FrontendController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    /**
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $request = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\Logger
     */
    protected $logger = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\Config
     */
    protected $payPalConfig = null;

    /**
     * Return request object
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = oxNew(\OxidEsales\PayPalModule\Core\Request::class);
        }

        return $this->request;
    }

    /**
     * Return PayPal logger
     *
     * @return \OxidEsales\PayPalModule\Core\Logger
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            $this->logger = oxNew(\OxidEsales\PayPalModule\Core\Logger::class);
            $this->logger->setLoggerSessionId($session->getId());
        }

        return $this->logger;
    }

    /**
     * Return PayPal config
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     */
    public function getPayPalConfig()
    {
        if (is_null($this->payPalConfig)) {
            $this->setPayPalConfig(oxNew(\OxidEsales\PayPalModule\Core\Config::class));
        }

        return $this->payPalConfig;
    }

    /**
     * Set PayPal config
     *
     * @param \OxidEsales\PayPalModule\Core\Config $payPalConfig config
     */
    public function setPayPalConfig($payPalConfig)
    {
        $this->payPalConfig = $payPalConfig;
    }


    /**
     * Logs passed value.
     *
     * @param mixed $value
     */
    public function log($value)
    {
        if ($this->getPayPalConfig()->isLoggingEnabled()) {
            $this->getLogger()->log($value);
        }
    }
}
