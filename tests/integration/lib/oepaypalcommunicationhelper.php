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
 * @copyright (C) OXID eSales AG 2003-2014
 */

require_once __DIR__ . '/oepaypalintegrationtesthelper.php';

class oePayPalCommunicationHelper extends oePayPalIntegrationTestHelper
{
    /**
     * Returns loaded oePayPalCaller object returning given parameters on call
     *
     * @param array $aParams
     *
     * @return oePayPalCaller
     */
    public function getCaller($aParams)
    {
        $oCaller = $this->getMock('oePayPalCaller', array('call'));
        $oCaller->expects($this->any())->method('call')->will($this->returnValue($aParams));

        $oService = new oePayPalService();
        $oService->setCaller($oCaller);

        return $oService;
    }

    /**
     * Stub curl to return expected result.
     *
     * @param array $aResult
     *
     * @return oePayPalCurl
     */
    public function getCurl($aResult)
    {
        $oCurl = $this->getMock('oePayPalCurl', array('execute'));
        $oCurl->expects($this->any())->method('execute')->will($this->returnValue($aResult));

        return $oCurl;
    }
}