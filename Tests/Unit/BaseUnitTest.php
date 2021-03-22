<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

abstract class BaseUnitTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @param string $needle
     * @param string $haystack
     * @param string $message
     */
    protected function doAssertStringContainsString($needle, $haystack, $message = '')
    {
        if (method_exists($this, 'assertStringContainsString')) {
            parent::assertStringContainsString($needle, $haystack, $message);
        } else {
            parent::assertContains($needle, $haystack, $message);
        }
    }

    /**
     * @param string $needle
     * @param string $haystack
     * @param string $message
     */
    protected function doAssertStringNotContainsString($needle, $haystack, $message = '')
    {
        if (method_exists($this, 'assertStringNotContainsString')) {
            parent::assertStringNotContainsString($needle, $haystack, $message);
        } else {
            parent::assertNotContains($needle, $haystack, $message);
        }
    }
}