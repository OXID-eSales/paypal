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
 * Abstract PayPal Dispatcher class
 */
abstract class Dispatcher extends \OxidEsales\PayPalModule\Controller\FrontendController
{
    /**
     * Service type identifier - Standard Checkout = 1
     *
     * @var int
     */
    protected $serviceType = 1;

    /**
     * PayPal checkout service
     *
     * @var \OxidEsales\PayPalModule\Core\PayPalService
     */
    protected $payPalCheckoutService;

    /**
     * Default user action for checkout process
     *
     * @var string
     */
    protected $userAction = "continue";

    /**
     * Executes "GetExpressCheckoutDetails" and on SUCCESS response - saves
     * user information and redirects to order page, on failure - sets error
     * message and redirects to basket page
     */
    abstract public function getExpressCheckoutDetails();

    /**
     * Sets PayPal checkout service.
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $payPalCheckoutService
     */
    public function setPayPalCheckoutService($payPalCheckoutService)
    {
        $this->payPalCheckoutService = $payPalCheckoutService;
    }

    /**
     * Returns PayPal service
     *
     * @return \OxidEsales\PayPalModule\Core\PayPalService
     */
    public function getPayPalCheckoutService()
    {
        if ($this->payPalCheckoutService === null) {
            $this->payPalCheckoutService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        }

        return $this->payPalCheckoutService;
    }

    /**
     * @return  \OxidEsales\Eshop\Core\UtilsView
     */
    protected function getUtilsView()
    {
        return \OxidEsales\Eshop\Core\Registry::getUtilsView();
    }

    /**
     * Formats given float/int value into PayPal friendly form
     *
     * @param float $in value to format
     *
     * @return string
     */
    protected function formatFloat($in)
    {
        return sprintf("%.2f", $in);
    }

    /**
     * Returns oxUtils instance
     *
     * @return  \OxidEsales\Eshop\Core\Utils
     */
    protected function getUtils()
    {
        return \OxidEsales\Eshop\Core\Registry::getUtils();
    }

    /**
     * Returns base url, which is used to construct Callback, Return and Cancel Urls
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $url = \OxidEsales\Eshop\Core\Registry::getConfig()->getSslShopUrl() . "index.php?lang=" . \OxidEsales\Eshop\Core\Registry::getLang()->getBaseLanguage() . "&sid=" . $session->getId() . "&rtoken=" . $session->getRemoteAccessToken();
        $url .= "&shp=" . \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();

        return $url;
    }

    /**
     * Returns PayPal order object
     *
     * @return \OxidEsales\Eshop\Application\Model\Order|null
     */
    protected function getPayPalOrder()
    {
        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if ($order->loadPayPalOrder()) {
            return $order;
        }
    }

    /**
     * Returns PayPal payment object
     *
     * @return \OxidEsales\Eshop\Application\Model\Payment|null
     */
    protected function getPayPalPayment()
    {
        $userPayment = null;

        if (($order = $this->getPayPalOrder())) {
            $userPayment = oxNew(\OxidEsales\Eshop\Application\Model\UserPayment::class);
            $userPayment->load($order->oxorder__oxpaymentid->value);
        }

        return $userPayment;
    }
}
