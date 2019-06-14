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
 * PayPal Express Checkout dispatcher class
 */
class ExpressCheckoutDispatcher extends \OxidEsales\PayPalModule\Controller\Dispatcher
{
    /**
     * Service type identifier - Express Checkout = 2
     *
     * @var int
     */
    protected $serviceType = 2;

    /**
     * Processes PayPal callback
     */
    public function processCallBack()
    {
        $payPalService = $this->getPayPalCheckoutService();
        $this->setParamsForCallbackResponse($payPalService);
        $request = $payPalService->callbackResponse();
        \OxidEsales\Eshop\Core\Registry::getUtils()->showMessageAndExit($request);
    }

    /**
     * Executes "SetExpressCheckout" and on SUCCESS response - redirects to PayPal
     * login/registration page, on error - returns to configured view (default is "basket"),
     * which means - redirect to configured view (default basket) and display error message
     *
     * @return string
     */
    public function setExpressCheckout()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $session->setVariable("oepaypal", "2");
        try {
            /** @var \OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder $builder */
            $builder = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder::class);

            $basket = $session->getBasket();
            $user = $this->getUser();

            $basket->setPayment("oxidpaypal");
            $session->setVariable('paymentid', 'oxidpaypal');

            $prevOptionValue = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('blCalculateDelCostIfNotLoggedIn');
            if ($this->getPayPalConfig()->isDeviceMobile()) {
                if ($this->getPayPalConfig()->getMobileECDefaultShippingId()) {
                    \OxidEsales\Eshop\Core\Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', true);
                    $basket->setShipping($this->getPayPalConfig()->getMobileECDefaultShippingId());
                } else {
                    \OxidEsales\Eshop\Core\Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', false);
                }
            }

            $basket->onUpdate();
            $basket->calculateBasket(true);
            \OxidEsales\Eshop\Core\Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', $prevOptionValue);

            $validator = oxNew(\OxidEsales\PayPalModule\Model\PaymentValidator::class);
            $validator->setUser($user);
            $validator->setConfig(\OxidEsales\Eshop\Core\Registry::getConfig());
            $validator->setPrice($basket->getPrice()->getPrice());
            $validator->setCheckCountry(false);

            if (!$validator->isPaymentValid()) {
                /**
                 * @var \OxidEsales\PayPalModule\Core\Exception\PayPalException $exception
                 */
                $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalException::class);
                $exception->setMessage(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PAYMENT_NOT_VALID"));
                throw $exception;
            }

            $builder->setPayPalConfig($this->getPayPalConfig());
            $builder->setBasket($basket);
            $builder->setUser($user);
            $builder->setReturnUrl($this->getReturnUrl());
            $builder->setCancelUrl($this->getCancelUrl());

            if (!$this->getPayPalConfig()->isDeviceMobile()) {
                $builder->setCallBackUrl($this->getCallBackUrl());
                $builder->setMaxDeliveryAmount($this->getPayPalConfig()->getMaxPayPalDeliveryAmount());
            }
            $showCartInPayPal = $this->getRequest()->getRequestParameter("displayCartInPayPal");
            $showCartInPayPal = $showCartInPayPal && !$basket->isFractionQuantityItemsPresent();
            $builder->setShowCartInPayPal($showCartInPayPal);
            $builder->setTransactionMode($this->getTransactionMode($basket));

            $request = $builder->buildExpressCheckoutRequest();

            $payPalService = $this->getPayPalCheckoutService();
            $result = $payPalService->setExpressCheckout($request);
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $excp) {
            // error - unable to set order info - display error message
            $this->getUtilsView()->addErrorToDisplay($excp);

            // return to requested view
            $returnTo = $this->getRequestedControllerKey();
            $returnTo = !empty($returnTo) ? $returnTo : 'basket';
            return $returnTo;
        }

        // saving PayPal token into session
        $session->setVariable("oepaypal-token", $result->getToken());

        // extracting token and building redirect url
        $url = $this->getPayPalConfig()->getPayPalCommunicationUrl($result->getToken(), $this->userAction);

        // redirecting to PayPal's login/registration page
        $this->getUtils()->redirect($url, false);
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
        $basket = $session->getBasket();

        try {
            $payPalService = $this->getPayPalCheckoutService();
            $builder = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder::class);
            $builder->setSession($session);
            $request = $builder->buildRequest();
            $details = $payPalService->getExpressCheckoutDetails($request);

            // Remove flag of "new item added" to not show "Item added" popup when returning to checkout from paypal
            $basket->isNewItemAdded();

            // creating new or using session user
            $user = $this->initializeUserData($details);
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $excp) {
            $this->getUtilsView()->addErrorToDisplay($excp);

            $logger = $this->getLogger();
            $logger->log("PayPal error: " . $excp->getMessage());

            return "basket";
        }

        // setting PayPal as current active payment
        $session->setVariable('paymentid', "oxidpaypal");
        $basket->setPayment("oxidpaypal");

        if (!$this->isPaymentValidForUserCountry($user)) {
            $this->getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_SELECT_ANOTHER_PAYMENT');

            $logger = $this->getLogger();
            $logger->log("Shop error: PayPal payment validation by user country failed. Payment is not valid for this country.");

            return "payment";
        }

        $shippingId = $this->extractShippingId(urldecode($details->getShippingOptionName()), $user);

        $this->setAnonymousUser($basket, $user);

        $basket->setShipping($shippingId);
        $basket->onUpdate();
        $basket->calculateBasket(true);

        $basketPrice = $basket->getPrice()->getBruttoPrice();

        if (!$this->isPayPalPaymentValid($user, $basketPrice, $basket->getShippingId())) {
            $this->getUtilsView()->addErrorToDisplay("OEPAYPAL_SELECT_ANOTHER_SHIPMENT");

            return "order";
        }

        // Checking if any additional discount was applied after we returned from PayPal.
        if ($basketPrice != $details->getAmount()) {
            $this->getUtilsView()->addErrorToDisplay("OEPAYPAL_ORDER_TOTAL_HAS_CHANGED");

            return "basket";
        }

        $session->setVariable("oepaypal-payerId", $details->getPayerId());
        $session->setVariable("oepaypal-userId", $user->getId());
        $session->setVariable("oepaypal-basketAmount", $details->getAmount());

        $next = "order";

        if ($this->getPayPalConfig()->finalizeOrderOnPayPalSide()) {
            $next .= "?fnc=execute";
        }

        return $next;
    }

    /**
     * Returns transaction mode.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $basket
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
     * Returns RETURN URL
     *
     * @return string
     */
    protected function getReturnUrl()
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
    protected function getCancelUrl()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $cancelURLFromRequest = $this->getRequest()->getRequestParameter('oePayPalCancelURL');
        $cancelUrl = $session->processUrl($this->getBaseUrl() . "&cl=basket");

        if ($cancelURLFromRequest) {
            $cancelUrl = html_entity_decode(urldecode($cancelURLFromRequest));
        } elseif ($requestedControllerKey = $this->getRequestedControllerKey()) {
            $cancelUrl = $session->processUrl($this->getBaseUrl() . '&cl=' . $requestedControllerKey);
        }

        return $cancelUrl;
    }

    /**
     * Extract requested controller key.
     * In case the key makes sense (we find a matching class) it will be returned.
     *
     * @return mixed|null
     */
    protected function getRequestedControllerKey()
    {
        $return = null;
        $requestedControllerKey = $this->getRequest()->getRequestParameter('oePayPalRequestedControllerKey');
        if (!empty($requestedControllerKey) &&
            \OxidEsales\Eshop\Core\Registry::getControllerClassNameResolver()->getClassNameById($requestedControllerKey)) {
            $return = $requestedControllerKey;
        }
        return $return;
    }

    /**
     * Returns CALLBACK URL
     *
     * @return string
     */
    protected function getCallBackUrl()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $controllerKey = \OxidEsales\Eshop\Core\Registry::getControllerClassNameResolver()->getIdByClassName(get_class());
        return $session->processUrl($this->getBaseUrl() . "&cl=" . $controllerKey . "&fnc=processCallBack");
    }

    /**
     *  Initialize new user from user data.
     *
     * @param array $data User data array.
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    protected function getCallBackUser($data)
    {
        // simulating user object
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $user->initializeUserForCallBackPayPalUser($data);

        return $user;
    }

    /**
     * Sets parameters to PayPal callback
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $payPalService PayPal service
     *
     * @return null
     */
    protected function setParamsForCallbackResponse($payPalService)
    {
        //logging request from PayPal
        $logger = $this->getLogger();
        $logger->setTitle("CALLBACK REQUEST FROM PAYPAL");
        $logger->log(http_build_query($_REQUEST, "", "&"));

        // initializing user..
        $user = $this->getCallBackUser($_REQUEST);

        // unknown country?
        if (!$this->getUserShippingCountryId($user)) {
            $logger = $this->getLogger();
            $logger->log("Callback error: NO SHIPPING COUNTRY ID");

            // unknown country - no delivery
            $this->setPayPalIsNotAvailable($payPalService);

            return;
        }

        //basket
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $basket = $session->getBasket();

        // get possible delivery sets
        $delSetList = $this->getDeliverySetList($user);

        //no shipping methods for user country
        if (empty($delSetList)) {
            $logger = $this->getLogger();
            $logger->log("Callback error: NO DELIVERY LIST SET");

            $this->setPayPalIsNotAvailable($payPalService);

            return;
        }

        $deliverySetList = $this->makeUniqueNames($delSetList);

        // checking if PayPal is valid payment for selected user country
        if (!$this->isPaymentValidForUserCountry($user)) {
            $logger->log("Callback error: NOT VALID COUNTRY ID");

            // PayPal payment is not possible for user country
            $this->setPayPalIsNotAvailable($payPalService);

            return;
        }

        $session->setVariable('oepaypal-oxDelSetList', $deliverySetList);

        $totalDeliveries = $this->setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket);

        // if none of deliveries contain PayPal - disabling PayPal
        if ($totalDeliveries == 0) {
            $logger->log("Callback error: DELIVERY SET LIST HAS NO PAYPAL");

            $this->setPayPalIsNotAvailable($payPalService);

            return;
        }

        $payPalService->setParameter("OFFERINSURANCEOPTION", "false");
    }

    /**
     * Sets delivery sets parameters to PayPal callback
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $payPalService   PayPal service.
     * @param array                                       $deliverySetList Delivery list.
     * @param \OxidEsales\Eshop\Application\Model\User    $user            User object.
     * @param \OxidEsales\Eshop\Application\Model\Basket  $basket          Basket object.
     *
     * @return int Total amount of deliveries
     */
    protected function setDeliverySetListForCallbackResponse($payPalService, $deliverySetList, $user, $basket)
    {
        $maxDeliveryAmount = $this->getPayPalConfig()->getMaxPayPalDeliveryAmount();
        $cur = \OxidEsales\Eshop\Core\Registry::getConfig()->getActShopCurrencyObject();
        $basketPrice = $basket->getPriceForPayment() / $cur->rate;
        $actShipSet = $basket->getShippingId();
        $hasActShipSet = false;
        $cnt = 0;

        // VAT for delivery will be calculated always
        $delVATPercent = $basket->getAdditionalServicesVatPercent();

        foreach ($deliverySetList as $delSetId => $delSetName) {
            // checking if PayPal is valid payment for selected delivery set
            if (!$this->isPayPalInDeliverySet($delSetId, $basketPrice, $user)) {
                continue;
            }

            $deliveryListProvider = oxNew(\OxidEsales\Eshop\Application\Model\DeliveryList::class);
            $deliveryList = array();

            // list of active delivery costs
            if ($deliveryListProvider->hasDeliveries($basket, $user, $this->getUserShippingCountryId($user), $delSetId)) {
                $deliveryList = $deliveryListProvider->getDeliveryList($basket, $user, $this->getUserShippingCountryId($user), $delSetId);
            }

            if (is_array($deliveryList) && !empty($deliveryList)) {
                $price = 0;

                if (\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('bl_perfLoadDelivery')) {
                    foreach ($deliveryList as $delivery) {
                        $price += $delivery->getDeliveryPrice($delVATPercent)->getBruttoPrice();
                    }
                }

                if ($price <= $maxDeliveryAmount) {
                    $payPalService->setParameter("L_SHIPPINGOPTIONNAME{$cnt}", \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($delSetName));
                    $payPalService->setParameter("L_SHIPPINGOPTIONLABEL{$cnt}", \OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"));
                    $payPalService->setParameter("L_SHIPPINGOPTIONAMOUNT{$cnt}", $this->formatFloat($price));

                    //setting active delivery set
                    if ($delSetId == $actShipSet) {
                        $hasActShipSet = true;
                        $payPalService->setParameter("L_SHIPPINGOPTIONISDEFAULT{$cnt}", "true");
                    } else {
                        $payPalService->setParameter("L_SHIPPINGOPTIONISDEFAULT{$cnt}", "false");
                    }

                    if ($basket->isCalculationModeNetto()) {
                        $payPalService->setParameter("L_TAXAMT{$cnt}", $this->formatFloat($basket->getPayPalBasketVatValue()));
                    } else {
                        $payPalService->setParameter("L_TAXAMT{$cnt}", $this->formatFloat(0));
                    }
                }

                $cnt++;
            }
        }

        //checking if active delivery set was set - if not, setting first in the list
        if ($cnt > 0 && !$hasActShipSet) {
            $payPalService->setParameter("L_SHIPPINGOPTIONISDEFAULT0", "true");
        }

        return $cnt;
    }

    /**
     * Makes delivery set array with unique names
     *
     * @param array $deliverySetList delivery list
     *
     * @return array
     */
    public function makeUniqueNames($deliverySetList)
    {
        $result = array();
        $nameCounts = array();

        foreach ($deliverySetList as $deliverySet) {
            $deliverySetName = trim($deliverySet->oxdeliveryset__oxtitle->value);

            if (isset($nameCounts[$deliverySetName])) {
                $nameCounts[$deliverySetName] += 1;
            } else {
                $nameCounts[$deliverySetName] = 1;
            }

            $suffix = ($nameCounts[$deliverySetName] > 1) ? " (" . $nameCounts[$deliverySetName] . ")" : '';
            $result[$deliverySet->oxdeliveryset__oxid->value] = $deliverySetName . $suffix;
        }

        return $result;
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

    /**
     * Extracts shipping id from given parameter
     *
     * @param string                                   $shippingOptionName Shipping option name, which comes from PayPal.
     * @param \OxidEsales\Eshop\Application\Model\User $user               User object.
     *
     * @return string
     */
    protected function extractShippingId($shippingOptionName, $user)
    {
        $result = null;
        $session = \OxidEsales\Eshop\Core\Registry::getSession();

        $charset = $this->getPayPalConfig()->getCharset();
        $shippingOptionName = htmlentities(html_entity_decode($shippingOptionName, ENT_QUOTES, $charset), ENT_QUOTES, $charset);

        $name = trim(str_replace(\OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_PRICE"), "", $shippingOptionName));

        $deliverySetList = $session->getVariable("oepaypal-oxDelSetList");

        if (!$deliverySetList) {
            $delSetList = $this->getDeliverySetList($user);
            $deliverySetList = $this->makeUniqueNames($delSetList);
        }

        if (is_array($deliverySetList)) {
            $flipped = array_flip($deliverySetList);

            $result = $flipped[$name];
        }

        return $result;
    }

    /**
     * Creates new or returns session user
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $details
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    protected function initializeUserData($details)
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $userEmail = $details->getEmail();
        $loggedUser = $this->getUser();
        if ($loggedUser) {
            $userEmail = $loggedUser->oxuser__oxusername->value;
        }

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        if ($userId = $user->isRealPayPalUser($userEmail)) {
            // if user exist
            $user->load($userId);

            if (!$loggedUser) {
                if (!$user->isSamePayPalUser($details)) {
                    /**
                     * @var $exception \OxidEsales\Eshop\Core\Exception\StandardException
                     */
                    $exception = oxNew(\OxidEsales\Eshop\Core\Exception\StandardException::class);
                    $exception->setMessage('OEPAYPAL_ERROR_USER_ADDRESS');
                    throw $exception;
                }
            } elseif (!$user->isSameAddressUserPayPalUser($details) || !$user->isSameAddressPayPalUser($details)) {
                // user has selected different address in PayPal (not equal with usr shop address)
                // so adding PayPal address as new user address to shop user account
                $this->createUserAddress($details, $userId);
            } else {
                // removing custom shipping address ID from session as user uses billing
                // address for shipping
                $session->deleteVariable('deladrid');
            }
        } else {
            $user->createPayPalUser($details);
        }

        $session->setVariable('usr', $user->getId());

        return $user;
    }

    /**
     * Creates user address and sets address id into session
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails $details User address info.
     * @param string                                                                    $userId  User id.
     *
     * @return bool
     */
    protected function createUserAddress($details, $userId)
    {
        $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);

        return $address->createPayPalAddress($details, $userId);
    }

    /**
     * Checking if PayPal payment is available in user country
     *
     * @param \OxidEsales\Eshop\Application\Model\User $user User object.
     *
     * @return boolean
     */
    protected function isPaymentValidForUserCountry($user)
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load("oxidpaypal");
        $paymentCountries = $payment->getCountries();

        if (!is_array($paymentCountries) || empty($paymentCountries)) {
            // not assigned to any country - valid to all countries
            return true;
        }

        return in_array($this->getUserShippingCountryId($user), $paymentCountries);
    }

    /**
     * Checks if selected delivery set has PayPal payment.
     *
     * @param string                                   $delSetId    Delivery set ID.
     * @param double                                   $basketPrice Basket price.
     * @param \OxidEsales\Eshop\Application\Model\User $user        User object.
     *
     * @return boolean
     */
    protected function isPayPalInDeliverySet($delSetId, $basketPrice, $user)
    {
        $paymentList = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Application\Model\PaymentList::class);
        $paymentList = $paymentList->getPaymentList($delSetId, $basketPrice, $user);

        if (is_array($paymentList) && array_key_exists("oxidpaypal", $paymentList)) {
            return true;
        }

        return false;
    }

    /**
     * Disables PayPal payment in PayPal side
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $payPalService PayPal service.
     */
    protected function setPayPalIsNotAvailable($payPalService)
    {
        // "NO_SHIPPING_OPTION_DETAILS" works only in version 61, so need to switch version
        $payPalService->setParameter("CALLBACKVERSION", "61.0");
        $payPalService->setParameter("NO_SHIPPING_OPTION_DETAILS", "1");
    }

    /**
     * Get delivery set list for PayPal callback
     *
     * @param \OxidEsales\Eshop\Application\Model\User $user User object.
     *
     * @return array
     */
    protected function getDeliverySetList($user)
    {
        $delSetList = oxNew(\OxidEsales\Eshop\Application\Model\DeliverySetList::class);

        return $delSetList->getDeliverySetList($user, $this->getUserShippingCountryId($user));
    }

    /**
     * Returns user shipping address country id.
     *
     * @param \OxidEsales\Eshop\Application\Model\User $user
     *
     * @return string
     */
    protected function getUserShippingCountryId($user)
    {
        if ($user->getSelectedAddressId() && $user->getSelectedAddress()) {
            $countryId = $user->getSelectedAddress()->oxaddress__oxcountryid->value;
        } else {
            $countryId = $user->oxuser__oxcountryid->value;
        }

        return $countryId;
    }

    /**
     * Checks whether PayPal payment is available
     *
     * @param \OxidEsales\Eshop\Application\Model\User $user
     * @param double                                   $basketPrice
     * @param string                                   $shippingId
     *
     * @return bool
     */
    protected function isPayPalPaymentValid($user, $basketPrice, $shippingId)
    {
        $valid = true;

        $payPalPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payPalPayment->load('oxidpaypal');
        if (!$payPalPayment->isValidPayment(null, null, $user, $basketPrice, $shippingId)) {
            $valid = $this->isEmptyPaymentValid($user, $basketPrice, $shippingId);
        }

        return $valid;
    }

    /**
     * Checks whether Empty payment is available.
     *
     * @param \OxidEsales\Eshop\Application\Model\User $user
     * @param double                                   $basketPrice
     * @param string                                   $shippingId
     *
     * @return bool
     */
    protected function isEmptyPaymentValid($user, $basketPrice, $shippingId)
    {
        $valid = true;

        $emptyPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $emptyPayment->load('oxempty');
        if (!$emptyPayment->isValidPayment(null, null, $user, $basketPrice, $shippingId)) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * PayPal express checkout might be called before user is set to basket.
     * This happens if user is not logged in to the Shop
     * and it goes to PayPal from details page or basket first step.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $basket
     * @param \OxidEsales\Eshop\Application\Model\User   $user
     */
    private function setAnonymousUser($basket, $user)
    {
        $basket->setBasketUser($user);
    }
}
