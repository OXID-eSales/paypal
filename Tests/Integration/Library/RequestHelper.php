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

namespace OxidEsales\PayPalModule\Tests\Integration\Library;

class RequestHelper extends \OxidEsales\PayPalModule\Tests\Integration\Library\IntegrationTestHelper
{
    /**
     * Returns loaded \OxidEsales\PayPalModule\Core\Request object with given parameters
     *
     * @param array $postParams
     * @param array $getParams
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest($postParams = null, $getParams = null)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Request::class);
        $mockBuilder->setMethods(['getPost', 'getGet']);
        $request = $mockBuilder->getMock();
        $request->expects($this->any())->method('getPost')->will($this->returnValue($postParams));
        $request->expects($this->any())->method('getGet')->will($this->returnValue($getParams));

        return $request;
    }
}