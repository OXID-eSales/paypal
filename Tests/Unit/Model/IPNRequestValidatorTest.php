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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing \OxidEsales\PayPalModule\Model\IPNRequestValidator class.
 */
class IPNRequestValidatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
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
     * @param bool   $isValidExpected
     * @param string $shopOwnerUserName
     * @param array  $payPalRequest
     * @param array  $payPalResponseData
     *
     * @dataProvider providerIsValid
     */
    public function testIsValid($isValidExpected, $shopOwnerUserName, $payPalRequest, $payPalResponseData)
    {
        $payPalIPNRequestValidator = $this->getIPNRequestValidator($payPalRequest, $payPalResponseData, $shopOwnerUserName);
        $isValid = $payPalIPNRequestValidator->isValid();

        $this->assertEquals($isValidExpected, $isValid, 'IPN request validation state is not as expected.');
    }

    /**
     * Data provider for testIsValid()
     *
     * @return array
     */
    public function providerGetValidationFailureMessage()
    {
        $cases = array();
        $messages = array(
            'Shop owner'           => 'none@oxid-esales.com', 'PayPal ID' => 'none2@oxid-esales.com', 'PayPal ACK' => 'VERIFIED',
            'PayPal Full Request'  => 'Array(    [receiver_email] => none2@oxid-esales.com)',
            'PayPal Full Response' => 'Array(    [VERIFIED] => )',
        );
        $cases[] = array($messages, 'none@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('VERIFIED' => ''));

        $messages = array(
            'Shop owner'           => '', 'PayPal ID' => '', 'PayPal ACK' => 'NOT VERIFIED',
            'PayPal Full Request'  => '',
            'PayPal Full Response' => '',
        );
        $cases[] = array($messages, null, null, null);

        $messages = array(
            'Shop owner'           => 'none2@oxid-esales.com', 'PayPal ID' => 'none2@oxid-esales.com', 'PayPal ACK' => 'NOT VERIFIED',
            'PayPal Full Request'  => 'Array(    [receiver_email] => none2@oxid-esales.com)',
            'PayPal Full Response' => 'Array(    [NOT_VERIFIED] => )',
        );
        $cases[] = array($messages, 'none2@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('NOT_VERIFIED' => ''));

        $messages = array(
            'Shop owner'           => 'none@oxid-esales.com', 'PayPal ID' => 'none2@oxid-esales.com', 'PayPal ACK' => 'NOT VERIFIED',
            'PayPal Full Request'  => 'Array(    [receiver_email] => none2@oxid-esales.com)',
            'PayPal Full Response' => 'Array(    [someString] => )',
        );
        $cases[] = array($messages, 'none@oxid-esales.com', array('receiver_email' => 'none2@oxid-esales.com'), array('someString' => ''));

        return $cases;
    }

    /**
     * @param bool   $validationMessageExpected
     * @param string $shopOwnerUserName
     * @param array  $payPalRequest
     * @param array  $payPalResponseData
     *
     * @dataProvider providerGetValidationFailureMessage
     */
    public function testGetValidationFailureMessage($validationMessageExpected, $shopOwnerUserName, $payPalRequest, $payPalResponseData)
    {
        $payPalIPNRequestValidator = $this->getIPNRequestValidator($payPalRequest, $payPalResponseData, $shopOwnerUserName);
        $validationMessage = $payPalIPNRequestValidator->getValidationFailureMessage();

        foreach ($validationMessage as &$value) {
            $value = str_replace("\n", '', $value);
        }

        $this->assertEquals($validationMessageExpected, $validationMessage, 'IPN request validation message is not as expected.');
    }

    protected function getIPNRequestValidator($payPalRequest, $payPalResponseData, $shopOwnerUserName)
    {
        $payPalResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseDoVerifyWithPayPal();
        $payPalResponse->setData($payPalResponseData);

        $payPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNRequestValidator();
        $payPalIPNRequestValidator->setPayPalRequest($payPalRequest);
        $payPalIPNRequestValidator->setPayPalResponse($payPalResponse);
        $payPalIPNRequestValidator->setShopOwnerUserName($shopOwnerUserName);

        return $payPalIPNRequestValidator;
    }
}
