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

namespace OxidEsales\PayPalModule\Tests\Unit\Model\PayPalRequest;

/**
 * Testing \OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder class.
 */
class GetExpressCheckoutDetailsRequestBuilderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Test building PayPal request object
     */
    public function testBuildRequest()
    {
        $expectedParams = array(
            'TOKEN' => '111',
        );
        $session = oxNew(\OxidEsales\Eshop\Core\Session::class);
        $session->setVariable("oepaypal-token", "111");

        $builder = $this->getPayPalRequestBuilder();
        $builder->setSession($session);
        $builder->buildRequest();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    /**
     *
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder
     */
    protected function getPayPalRequestBuilder()
    {
        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder();

        return $builder;
    }

    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    protected function assertArraysEqual($expected, $result)
    {
        $this->assertArraysContains($expected, $result);
        $this->assertEquals(count($expected), count($result));
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    protected function assertArraysContains($expected, $result)
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result, "Key not found: $key");
            $this->assertEquals($value, $result[$key], "Key '$key' value is not equal to '$value'");
        }
    }
}