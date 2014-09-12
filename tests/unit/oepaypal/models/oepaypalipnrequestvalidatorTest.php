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
 * @copyright (C) OXID eSales AG 2003-2013
 */

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';

/**
 * Testing oePayPalIPNRequestValidator class.
 */
class Unit_oePayPal_Models_oePayPalIPNRequestValidatorTest extends OxidTestCase
{
    /**
     * Data provider for testIsValid()
     *
     * @return array
     */
    public function providerIsValid()
    {
        return array(
            array(true, null, null, array('VERIFIED' => 'true')),
            array(true, 'none@oxid-esales.com', array('receiver_email' => 'none@oxid-esales.com'), array('VERIFIED' => '')),
            array(true, 'none2@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('VERIFIED' => '')),
            array(false, 'none@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('VERIFIED' => '')),
            array(false, null, null, null),
            array(false, 'none2@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('NOT_VERIFIED' => '')),
            array(false, 'none@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('someString' => '')),
        );
    }

    /**
     * @param bool $blIsValidExpected
     * @param string $sShopOwnerUserName
     * @param array $aPayPalRequest
     * @param array $aPayPalResponse
     *
     * @dataProvider providerIsValid
     */
    public function testIsValid($blIsValidExpected, $sShopOwnerUserName, $aPayPalRequest, $aPayPalResponse)
    {
        $oPayPalIPNRequestValidator = $this->getIPNRequestValidator($aPayPalRequest, $aPayPalResponse, $sShopOwnerUserName);
        $blIsValid = $oPayPalIPNRequestValidator->isValid();

        $this->assertEquals($blIsValidExpected, $blIsValid, 'IPN request validation state is not as expected.');
    }

    /**
     * Data provider for testIsValid()
     *
     * @return array
     */
    public function providerGetValidationFailureMessage()
    {
        $aCases = array();
        $aMessages = array(
            'Shop owner' => 'none@oxid-esales.com', 'PayPal ID' => 'none2@oxid-esales.com', 'PayPal ACK' => 'VERIFIED',
            'PayPal Full Request' => 'Array(    [receiver_email] => none2@oxid-esales.com)',
            'PayPal Full Response' => 'Array(    [VERIFIED] => )',
        );
        $aCases[] = array($aMessages, 'none@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('VERIFIED' => ''));

        $aMessages = array(
            'Shop owner' => '', 'PayPal ID' => '', 'PayPal ACK' => 'NOT VERIFIED',
            'PayPal Full Request' => '',
            'PayPal Full Response' => '',
        );
        $aCases[] = array($aMessages, null, null, null);

        $aMessages = array(
            'Shop owner' => 'none2@oxid-esales.com', 'PayPal ID' => 'none2@oxid-esales.com', 'PayPal ACK' => 'NOT VERIFIED',
            'PayPal Full Request' => 'Array(    [receiver_email] => none2@oxid-esales.com)',
            'PayPal Full Response' => 'Array(    [NOT_VERIFIED] => )',
        );
        $aCases[] = array($aMessages, 'none2@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('NOT_VERIFIED' => ''));

        $aMessages = array(
            'Shop owner' => 'none@oxid-esales.com', 'PayPal ID' => 'none2@oxid-esales.com', 'PayPal ACK' => 'NOT VERIFIED',
            'PayPal Full Request' => 'Array(    [receiver_email] => none2@oxid-esales.com)',
            'PayPal Full Response' => 'Array(    [someString] => )',
        );
        $aCases[] = array($aMessages, 'none@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('someString' => ''));

        return $aCases;
    }

    /**
     * @param bool $aValidationMessageExpected
     * @param string $sShopOwnerUserName
     * @param array $aPayPalRequest
     * @param array $aPayPalResponse
     *
     * @dataProvider providerGetValidationFailureMessage
     */
    public function testGetValidationFailureMessage($aValidationMessageExpected, $sShopOwnerUserName, $aPayPalRequest, $aPayPalResponse)
    {
        $oPayPalIPNRequestValidator = $this->getIPNRequestValidator($aPayPalRequest, $aPayPalResponse, $sShopOwnerUserName);
        $aValidationMessage = $oPayPalIPNRequestValidator->getValidationFailureMessage();

        foreach ($aValidationMessage as &$sValue) {
            $sValue = str_replace("\n", '', $sValue);
        }

        $this->assertEquals($aValidationMessageExpected, $aValidationMessage, 'IPN request validation message is not as expected.');
    }

    protected function getIPNRequestValidator($aPayPalRequest, $aPayPalResponse, $sShopOwnerUserName)
    {
        $oPayPalResponse = new oePayPalResponseDoVerifyWithPayPal();
        $oPayPalResponse->setData($aPayPalResponse);

        $oPayPalIPNRequestValidator = new oePayPalIPNRequestValidator();
        $oPayPalIPNRequestValidator->setPayPalRequest($aPayPalRequest);
        $oPayPalIPNRequestValidator->setPayPalResponse($oPayPalResponse);
        $oPayPalIPNRequestValidator->setShopOwnerUserName($sShopOwnerUserName);

        return $oPayPalIPNRequestValidator;
    }
}