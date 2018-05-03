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

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

/**
 * Testing \OxidEsales\PayPalModule\Core\Request class.
 */
class RequestTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Data provider for testGetPost()
     *
     * @return array
     */
    public function providerGetPost()
    {
        return array(
            array(
                array('asd&' => 'a%&'),
                array('asd&' => 'a%&'),
            ),
            array(
                null,
                array(),
            )
        );
    }

    /**
     * Test if return POST.
     *
     * @param array $post
     * @param array $postExpected
     *
     * @dataProvider providerGetPost
     */
    public function testGetPost($post, $postExpected)
    {
        $_POST = $post;
        $_GET = array('zzz' => 'yyyy');
        $payPalRequest = new \OxidEsales\PayPalModule\Core\Request();
        $this->assertEquals($postExpected, $payPalRequest->getPost());
    }

    /**
     * Data provider for testGetGet()
     *
     * @return array
     */
    public function providerGetGet()
    {
        return array(
            array(
                array('asd&' => 'a%&'),
                array('asd&' => 'a%&'),
            ),
            array(
                null,
                array(),
            )
        );
    }

    /**
     * Test if return Get.
     *
     * @param array $get
     * @param array $getExpected
     *
     * @dataProvider providerGetGet
     */
    public function testGetGet($get, $getExpected)
    {
        $_GET = $get;
        $_POST = array('zzz' => 'yyyy');
        $payPalRequest = new \OxidEsales\PayPalModule\Core\Request();
        $this->assertEquals($getExpected, $payPalRequest->getGet());
    }

    /**
     * Data provider for testGetRequestParameter()
     *
     * @return array
     */
    public function providerGetRequestParameter()
    {
        return array(
            array(array('zzz' => 'yyy'), array('zzz' => 'iii'), 'zzz', false, 'yyy'),
            array(array('zzz' => 'yyy'), array('zzz' => 'yyy'), 'zzz', false, 'yyy'),
            array(array('zzz' => 'iii'), array('zzz' => 'yyy'), 'zzz', false, 'iii'),
            array(array('zzz' => 'yyy&'), null, 'zzz', true, 'yyy&'),
            array(null, array('zzz' => 'yyy&'), 'zzz', true, 'yyy&'),
            array(array('zzz' => 'yyy&'), null, 'zzz', false, 'yyy&amp;'),
            array(null, array('zzz' => 'yyy&'), 'zzz', false, 'yyy&amp;'),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Request::getRequestParameter()
     * Test case for \OxidEsales\PayPalModule\Core\Request::getGetParameter()
     * Test case for \OxidEsales\PayPalModule\Core\Request::getPostParameter()
     *
     * @dataProvider providerGetRequestParameter
     */
    public function testGetRequestParameter($post, $get, $parameterName, $raw, $expectedRequestParameter)
    {
        $_POST = $post;
        $_GET = $get;
        $payPalRequest = new \OxidEsales\PayPalModule\Core\Request();
        $this->assertEquals($expectedRequestParameter, $payPalRequest->getRequestParameter($parameterName, $raw));
    }
}