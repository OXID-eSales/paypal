<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Admin;

use OxidEsales\Codeception\Page\Page;

/**
 * Class Orders
 * @package OxidEsales\PayPalModule\Tests\Codeception\Admin
 */
class PayPalOrder extends Page
{
    public $paypalTab = '//a[@href="#oepaypalorder_paypal"]';
    public $captureButton = '#captureButton';
    public $amountSelect = '.amountSelect';
    public $captureAmountInput = '#captureAmountInput';
    public $pendingStatusCheckbox = '#pendingStatusCheckbox';
    public $editForm = '[name=myedit]';
    public $refundButton = '#refundButton0';
    public $refundAmountInput = '#refundAmountInput';
    public $errorBox = '.errorbox';
    public $captureErrorText = 'Error message from PayPal: Amount is not valid';
    public $refundErrorText = 'Error message from PayPal: The partial refund amount is not valid';

    /**
     * Capture order
     */
    public function captureAmount()
    {
        $I = $this->user;
        $I->waitForElement($this->captureButton, 10);
        $I->click($this->captureButton);
        $I->selectOption($this->amountSelect,'NotComplete');
        $I->fillField($this->captureAmountInput, '55,55');
        $I->click($this->pendingStatusCheckbox);
        $I->submitForm($this->editForm, []);
    }

    /**
     * Refund amount
     */
    public function refundAmount()
    {
        $I = $this->user;
        $I->waitForElement($this->refundButton, 10);
        $I->click($this->refundButton);
        $I->selectOption($this->amountSelect,'Partial');
        $I->fillField($this->refundAmountInput, '49,50');
        $I->submitForm($this->editForm, []);
    }
}
