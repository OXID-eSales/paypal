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
 * Testing \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder class.
 */
class PayPalRequestBuilderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSetGetRequest()
    {
        $data = array('DATA' => 'data');
        $request = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $request->setData($data);
        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder();
        $builder->setRequest($request);

        $this->assertEquals($request, $builder->getRequest());
    }

    public function testSetAuthorizationId()
    {
        $authorizationId = 'AuthorizationId';
        $expected = array('AUTHORIZATIONID' => $authorizationId);

        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder();
        $builder->setAuthorizationId($authorizationId);

        $request = $builder->getRequest();

        $this->assertEquals($expected, $request->getData());
    }

    public function testSetAmount()
    {
        $amount = 99.99;
        $currency = 'EUR';
        $expected = array(
            'AMT'          => $amount,
            'CURRENCYCODE' => $currency
        );

        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder();
        $builder->setAmount($amount);

        $request = $builder->getRequest();

        $this->assertEquals($expected, $request->getData());
    }

    public function testSetCompleteType()
    {
        $completeType = 'Full';
        $expected = array('COMPLETETYPE' => $completeType);

        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder();
        $builder->setCompleteType($completeType);

        $request = $builder->getRequest();

        $this->assertEquals($expected, $request->getData());
    }

    public function testSetRefundType()
    {
        $refundType = 'Complete';
        $expected = array('REFUNDTYPE' => $refundType);

        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder();
        $builder->setRefundType($refundType);

        $request = $builder->getRequest();

        $this->assertEquals($expected, $request->getData());
    }

    public function testSetComment()
    {
        $comment = 'Comment';
        $expected = array('NOTE' => $comment);

        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder();
        $builder->setComment($comment);

        $request = $builder->getRequest();

        $this->assertEquals($expected, $request->getData());
    }
}