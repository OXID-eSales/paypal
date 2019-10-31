<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use PHPUnit\Framework\AssertionFailedError;

class Acceptance extends \Codeception\Module
{
    public function seePageHasElement($element)
    {
        try {
            $this->getModule('WebDriver')->_findElements($element);
        } catch (AssertionFailedError $f) {
            return false;
        }
        return true;
    }
}
