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

use OxidEsales\PayPalModule\Model\PaymentManager;

/**
 * PayPal Standard Checkout dispatcher class
 */
class StandardDispatcher extends \OxidEsales\PayPalModule\Controller\Dispatcher
{
    /**
     * Executes "SetExpressCheckout" and on SUCCESS response - redirects to PayPal
     * login/registration page, on error - returns "basket", which means - redirect
     * to basket view and display error message
     *
     * @return string|void
     */
    public function setExpressCheckout()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $session->setVariable("oepaypal", PaymentManager::PAYPAL_SERVICE_TYPE_STANDARD);
        try {
            $selectedAddressId = $this->getUser() ? (string) $this->getUser()->getSelectedAddressId() : '';
            $paymentManager = $this->getPaymentManager();
            $result = $paymentManager->setStandardCheckout(
                $session->getBasket(),
                $this->getUser() ?: null,
                $this->getReturnUrl(),
                $this->getCancelUrl(),
                (bool)$this->getRequest()->getRequestParameter("displayCartInPayPal"),
                $selectedAddressId
            );
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $excp) {
            // error - unable to set order info - display error message
            $this->getUtilsView()->addErrorToDisplay($excp);

            // return to basket view
            return "basket";
        }

        // saving PayPal token into session
        $session->setVariable("oepaypal-token", $result->getToken());

        // extracting token and building redirect url
        $url = $this->getPayPalConfig()->getPayPalCommunicationUrl($result->getToken(), $this->userAction);

        // redirecting to PayPal's login/registration page
        $this->getUtils()->redirect($url, false);
    }

    /**
     * Returns transaction mode.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $basket
     *
     * @return string
     *
     * @deprecated Please use OxidEsales\PayPalModule\Model\PaymentManager::getTransactionMode
     */
    protected function getTransactionMode($basket)
    {
        $paymentManager = $this->getPaymentManager();
        return $paymentManager->getTransactionMode($basket, $this->getPayPalConfig());
    }

    /**
     * Executes "GetExpressCheckoutDetails" and on SUCCESS response - saves
     * user information and redirects to order page, on failure - sets error
     * message and redirects to basket page
     *
     * @return string
     */
    public function getExpressCheckoutDetails()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();

        try {
            $paymentManager = $this->getPaymentManager();
            $details = $paymentManager->getExpressCheckoutDetails();

            $user = $this->getUser();
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $excp) {
            // display error message
            $this->getUtilsView()->addErrorToDisplay($excp);

            // problems fetching user info - redirect to payment selection
            return 'payment';
        }

        $session->setVariable("oepaypal-payerId", $details->getPayerId());
        $session->setVariable("oepaypal-basketAmount", $details->getAmount());

        // next step - order page
        $next = 'order';

        // finalize order on paypal side?
        if ($this->getPayPalConfig()->finalizeOrderOnPayPalSide()) {
            $next .= "?fnc=execute";
            $next .= "&sDeliveryAddressMD5=" . $user->getEncodedDeliveryAddress();
            $next .= "&stoken=" . $session->getSessionChallengeToken();
        }

        // everything is fine - redirect to order
        return $next;
    }

    /**
     * Returns RETURN URL
     *
     * @return string
     */
    public function getReturnUrl()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $controllerKey = \OxidEsales\Eshop\Core\Registry::getControllerClassNameResolver()->getIdByClassName(get_class());
        return $session->processUrl($this->getBaseUrl() . "&cl=" . $controllerKey . "&fnc=getExpressCheckoutDetails");
    }

    /**
     * Returns CANCEL URL
     *
     * @return string
     */
    public function getCancelUrl()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        return $session->processUrl($this->getBaseUrl() . "&cl=payment");
    }
}
