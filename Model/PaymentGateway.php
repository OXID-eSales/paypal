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

namespace OxidEsales\PayPalModule\Model;

/**
 * Payment gateway manager.
 * Checks and sets payment method data, executes payment.
 *
 * @mixin \OxidEsales\Eshop\Application\Model\PaymentGateway
 */
class PaymentGateway extends PaymentGateway_parent
{
    /**
     * PayPal config.
     *
     * @var null
     */
    protected $payPalConfig = null;

    /**
     * PayPal config.
     *
     * @var null
     */
    protected $checkoutService = null;

    /**
     * Order.
     *
     * @var \OxidEsales\Eshop\Application\Model\Order
     */
    protected $payPalOxOrder;

    /**
     * Sets order.
     *
     * @param \OxidEsales\Eshop\Application\Model\Order $order
     */
    public function setPayPalOxOrder($order)
    {
        $this->payPalOxOrder = $order;
    }

    /**
     * Gets order.
     *
     * @return \OxidEsales\Eshop\Application\Model\Order
     */
    public function getPayPalOxOrder()
    {
        if (is_null($this->payPalOxOrder)) {
            $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $order->loadPayPalOrder();
            $this->setPayPalOxOrder($order);
        }

        return $this->payPalOxOrder;
    }

    /**
     * Executes payment, returns true on success.
     *
     * @param double                               $amount Goods amount
     * @param \OxidEsales\PayPalModule\Model\Order $order  User ordering object
     *
     * @return bool
     */
    public function executePayment($amount, &$order)
    {
        $success = parent::executePayment($amount, $order);
        $session = \OxidEsales\Eshop\Core\Registry::getSession();

        if ( ($session->getVariable('paymentid') == 'oxidpaypal')
             || ($session->getBasket()->getPaymentId() == 'oxidpaypal')
        ) {
            $this->setPayPalOxOrder($order);
            $success = $this->doExpressCheckoutPayment();
        }

        return $success;
    }

    /**
     * Executes "DoExpressCheckoutPayment" to PayPal
     *
     * @return bool
     */
    public function doExpressCheckoutPayment()
    {
        $success = false;
        $order = $this->getPayPalOrder();

        try {
            // updating order state
            if ($order) {
                $order->oePayPalUpdateOrderNumber();
                $session = \OxidEsales\Eshop\Core\Registry::getSession();
                $basket = $session->getBasket();

                $transactionMode = $this->getTransactionMode($basket);

                $builder = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\DoExpressCheckoutPaymentRequestBuilder::class);
                $builder->setPayPalConfig($this->getPayPalConfig());
                $builder->setSession($session);
                $builder->setBasket($basket);
                $builder->setTransactionMode($transactionMode);
                $builder->setUser($this->getPayPalUser());
                $builder->setOrder($order);

                $request = $builder->buildRequest();

                $payPalService = $this->getPayPalCheckoutService();
                $result = $payPalService->doExpressCheckoutPayment($request);

                $order->finalizePayPalOrder(
                    $result,
                    $session->getBasket(),
                    $transactionMode
                );

                $success = true;
            } else {
                /**
                 * @var $exception \OxidEsales\Eshop\Core\Exception\StandardException
                 */
                $exception = oxNew(\OxidEsales\Eshop\Core\Exception\StandardException::class, 'OEPAYPAL_ORDER_ERROR');
                throw $exception;
            }
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $exception) {
            // deleting order on error
            if ($order) {
                $order->deletePayPalOrder();
            }

            $this->_iLastErrorNo = \OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_PAYMENTERROR;
            $utilsView = \OxidEsales\Eshop\Core\Registry::getUtilsView();
            $utilsView->addErrorToDisplay($exception);
        }

        return $success;
    }

    /**
     * Returns transaction mode.
     *
     * @param object $basket
     *
     * @return string
     */
    protected function getTransactionMode($basket)
    {
        $transactionMode = $this->getPayPalConfig()->getTransactionMode();

        if ($transactionMode == "Automatic") {
            $outOfStockValidator = new \OxidEsales\PayPalModule\Model\OutOfStockValidator();
            $outOfStockValidator->setBasket($basket);
            $outOfStockValidator->setEmptyStockLevel($this->getPayPalConfig()->getEmptyStockLevel());

            $transactionMode = ($outOfStockValidator->hasOutOfStockArticles()) ? "Authorization" : "Sale";

            return $transactionMode;
        }

        return $transactionMode;
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
     * Sets PayPal service
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $checkoutService
     */
    public function setPayPalCheckoutService($checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Returns PayPal service
     *
     * @return \OxidEsales\PayPalModule\Core\PayPalService
     */
    public function getPayPalCheckoutService()
    {
        if (is_null($this->checkoutService)) {
            $this->setPayPalCheckoutService(oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class));
        }

        return $this->checkoutService;
    }

    /**
     * Returns PayPal order object
     *
     * @return \OxidEsales\Eshop\Application\Model\Order
     */
    protected function getPayPalOrder()
    {
        return $this->getPayPalOxOrder();
    }

    /**
     * Returns PayPal user
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    protected function getPayPalUser()
    {
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        if (!$user->loadUserPayPalUser()) {
            $user = $this->getUser();
        }

        return $user;
    }
}
