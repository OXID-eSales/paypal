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
 * Abstract PayPal Dispatcher class
 */
abstract class Dispatcher extends \OxidEsales\PayPalModule\Controller\FrontendController
{
    /**
     * Service type identifier - Standard Checkout = 1
     *
     * @var int
     */
    protected $_iServiceType = 1;

    /**
     * PayPal checkout service
     *
     * @var oePayPalCheckoutService
     */
    protected $_oPayPalCheckoutService;

    /**
     * Default user action for checkout process
     *
     * @var string
     */
    protected $_sUserAction = "continue";

    /**
     * Executes "GetExpressCheckoutDetails" and on SUCCESS response - saves
     * user information and redirects to order page, on failure - sets error
     * message and redirects to basket page
     *
     * @return string
     */
    abstract public function getExpressCheckoutDetails();

    /**
     * Sets PayPal checkout service.
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $oPayPalCheckoutService
     */
    public function setPayPalCheckoutService($oPayPalCheckoutService)
    {
        $this->_oPayPalCheckoutService = $oPayPalCheckoutService;
    }

    /**
     * Returns PayPal service
     *
     * @return \OxidEsales\PayPalModule\Core\PayPalService
     */
    public function getPayPalCheckoutService()
    {
        if ($this->_oPayPalCheckoutService === null) {
            $this->_oPayPalCheckoutService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        }

        return $this->_oPayPalCheckoutService;
    }

    /**
     * @return  \OxidEsales\Eshop\Core\UtilsView
     */
    protected function _getUtilsView()
    {
        return \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsView::class);
    }

    /**
     * Formats given float/int value into PayPal friendly form
     *
     * @param float $fIn value to format
     *
     * @return string
     */
    protected function _formatFloat($fIn)
    {
        return sprintf("%.2f", $fIn);
    }

    /**
     * Returns oxUtils instance
     *
     * @return  \OxidEsales\Eshop\Core\Utils
     */
    protected function _getUtils()
    {
        return \OxidEsales\Eshop\Core\Registry::getUtils();
    }

    /**
     * Returns base url, which is used to construct Callback, Return and Cancel Urls
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        $oSession = $this->getSession();
        $sUrl = $this->getConfig()->getSslShopUrl() . "index.php?lang=" . \OxidEsales\Eshop\Core\Registry::getLang()->getBaseLanguage() . "&sid=" . $oSession->getId() . "&rtoken=" . $oSession->getRemoteAccessToken();
        $sUrl .= "&shp=" . $this->getConfig()->getShopId();

        return $sUrl;
    }

    /**
     * Returns PayPal order object
     *
     * @return \OxidEsales\Eshop\Application\Model\Order|object
     */
    protected function _getPayPalOrder()
    {
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if ($oOrder->loadPayPalOrder()) {
            return $oOrder;
        }
    }

    /**
     * Returns PayPal payment object
     *
     * @return \OxidEsales\Eshop\Application\Model\Payment|object
     */
    protected function _getPayPalPayment()
    {
        if (($oOrder = $this->_getPayPalOrder())) {
            $oUserPayment = oxNew(\OxidEsales\Eshop\Application\Model\UserPayment::class);
            $oUserPayment->load($oOrder->oxorder__oxpaymentid->value);

            return $oUserPayment;
        }
    }
}
