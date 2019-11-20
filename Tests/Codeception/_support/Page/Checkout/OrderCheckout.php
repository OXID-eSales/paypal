<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Page\Checkout;

/**
 * Class OrderCheckout
 * @package OxidEsales\PayPalModule\Tests\Codeception\Page
 */
class OrderCheckout extends \OxidEsales\Codeception\Page\Checkout\OrderCheckout
{
    public $confirmationButton = '#orderConfirmAgbBottom';

    /**
     * Clicks on submit order button.
     *
     * @return $this
     */
    public function submitOrder()
    {
        $I = $this->user;
        $I->waitForElementVisible($this->confirmationButton, 10);
        $I->submitForm($this->confirmationButton, []);

        return $this;
    }
}
