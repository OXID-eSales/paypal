<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Example;
use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Page\Checkout\ThankYou;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\Codeception\Step\ProductNavigation;
use OxidEsales\Codeception\Page\Checkout\PaymentCheckout;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\Codeception\Module\Translation\Translator;

/**
 * @group oepaypal
 * @group oepaypal_standard
 * @group oepaypal_callback_controller
*/
class CallbackControllerCest extends BaseCest
{
    /**
     * @dataProvider providerCallBackTests
     */
    public function testCallbackResponse(AcceptanceTester $I, Example $data): void
    {
        $I->wantToTest('callback response depending on session information');

        $home = $I->openShop();
        $I->waitForText(Translator::translate('HOME'));

        if ($data['doLogInUser']) {
            $home->loginUser(Fixtures::get('userName'), Fixtures::get('userPassword'));
        }

        if ($data['doFillBasket']) {
            $this->fillBasket($I);
        }

        $token = (string) $I->grabValueFrom('//input[@name="stoken"]');
        $sid = (string) $I->grabCookie('sid');

        //Check callback
        $callbackUrl = $this->getCallbackUrl($I, $sid, $token, $data['payPalData']);
        $result = $this->getCallbackResponse($callbackUrl, $sid);
        $this->assertResults($I, $data['expected'], $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    protected function providerCallBackTests()
    {
        $data = [];

        //Test case that callback is provided a session id but the basket related to that SID is empty and PP data empty.
        $data['CallbackEmptyBasketNoPayPalData'] = [
            'payPalData' => [],
            'expected' => [
                'METHOD' => 'CallbackResponse',
                'NO_SHIPPING_OPTION_DETAILS' => 1],
            'doLogInUser' => false,
            'doFillBasket' => true
        ];

        //Test case that callback data from PayPal is incomplete. Basket filled.
        $data['LoggedInUserEmptyBasketUnknownCountry'] = [
            'payPalData' => [
                'FIRSTNAME' => 'gerName',
                'LASTNAME' => 'gerlastname',
                'SHIPTONAME' => "Testuser",
                'SHIPTOSTREET' => 'Musterstr. 123',
                'SHIPTOCITY' => 'Musterstadt',
                'SHIPTOZIP' => '79098'
            ],
            'expected' => [
                'METHOD' => 'CallbackResponse',
                'NO_SHIPPING_OPTION_DETAILS' => 1
            ],
            'doLogInUser' => true,
            'doFillBasket' => true
        ];

        //Test case that user enters different address in PayPal for country that has PP attached in shop
        $data['CallbackOkForLoggedInUserGetDelSet'] = [
            'payPalData' => [
                'FIRSTNAME' => 'gerName',
                'LASTNAME' => 'gerlastname',
                'SHIPTONAME' => "Testuser",
                'SHIPTOSTREET' => 'Universitätsring 1',
                'SHIPTOCITY' => 'Wien',
                'SHIPTOZIP' => '1010',
                'SHIPTOCOUNTRY' => 'AT',
                'SHIPTOCOUNTRYCODE' => 'AT',
                'SHIPTOCOUNTRYNAME' => 'Austria'],
            'expected' => [
                'METHOD' => 'CallbackResponse',
                'L_SHIPPINGOPTIONNAME0' => 'Standard',
                'L_SHIPPINGOPTIONLABEL0' => 'Preis',
                'L_SHIPPINGOPTIONAMOUNT0' => '6.90',
                'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                'L_TAXAMT0' => '0.00',
                'L_SHIPPINGOPTIONNAME1' => 'Beispiel+Set1%3A+UPS+48+Std.',
                'L_SHIPPINGOPTIONLABEL1' => 'Preis',
                'L_SHIPPINGOPTIONAMOUNT1' => '9.90',
                'L_SHIPPINGOPTIONISDEFAULT1' => 'false',
                'L_TAXAMT1' => '0.00',
                'L_SHIPPINGOPTIONNAME2' => 'Beispiel+Set1%3A+UPS+24+Std.+Express',
                'L_SHIPPINGOPTIONLABEL2' => 'Preis',
                'L_SHIPPINGOPTIONAMOUNT2' => '12.90',
                'L_SHIPPINGOPTIONISDEFAULT2' => 'false',
                'L_TAXAMT2' => '0.00',
                'OFFERINSURANCEOPTION' => 'false'],
            'doLogInUser' => true,
            'doFillBasket' => true
        ];

        //Test case that callback data from PayPal is complete. Basket empty.
        $data['LoggedInUserFilledBasketUnknownCountry'] = [
            'payPalData' => [
                'FIRSTNAME' => 'gerName',
                'LASTNAME' => 'gerlastname',
                'SHIPTONAME' => "Testuser",
                'SHIPTOSTREET' => 'Musterstr. 123',
                'SHIPTOCITY' => 'Musterstadt',
                'SHIPTOZIP' => '79098',
                'SHIPTOCOUNTRY' => 'DE',
                'SHIPTOCOUNTRYCODE' => 'DE',
                'SHIPTOCOUNTRYNAME' => 'Germany'
            ],
            'expected' => [
                'METHOD' => 'CallbackResponse',
                'L_SHIPPINGOPTIONNAME0' => 'Standard',
                'L_SHIPPINGOPTIONLABEL0' => 'Preis',
                'L_SHIPPINGOPTIONAMOUNT0' => '0.00',
                'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                'L_TAXAMT0' => '0.00',
                'L_SHIPPINGOPTIONNAME1' => 'Beispiel+Set1%3A+UPS+48+Std.',
                'L_SHIPPINGOPTIONLABEL1' => 'Preis',
                'L_SHIPPINGOPTIONAMOUNT1' => '0.00',
                'L_SHIPPINGOPTIONISDEFAULT1' => 'false',
                'L_TAXAMT1' => '0.00',
                'OFFERINSURANCEOPTION' => 'false'
            ],
            'doLogInUser' => true,
            'doFillBasket' => false
        ];

        //Test case that callback data from PayPal is complete. Basket filled.
        $data['CallbackOkForLoggedInUserGermany'] = [
            'payPalData' => [
                'FIRSTNAME' => 'gerName',
                'LASTNAME' => 'gerlastname',
                'SHIPTONAME' => "Testuser",
                'SHIPTOSTREET' => 'Musterstr. 123',
                'SHIPTOCITY' => 'Musterstadt',
                'SHIPTOZIP' => '79098',
                'SHIPTOCOUNTRY' => 'DE',
                'SHIPTOCOUNTRYCODE' => 'DE',
                'SHIPTOCOUNTRYNAME' => 'Germany'
            ],
            'expected' => [
                'METHOD' => 'CallbackResponse',
                'L_SHIPPINGOPTIONNAME0' => 'Standard',
                'L_SHIPPINGOPTIONLABEL0' => 'Preis',
                'L_SHIPPINGOPTIONAMOUNT0' => '0.00',
                'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                'L_TAXAMT0' => '0.00',
                'L_SHIPPINGOPTIONNAME1' => 'Beispiel+Set1%3A+UPS+48+Std.',
                'L_SHIPPINGOPTIONLABEL1' => 'Preis',
                'L_SHIPPINGOPTIONAMOUNT1' => '9.90',
                'L_SHIPPINGOPTIONISDEFAULT1' => 'false',
                'L_TAXAMT1' => '0.00',
                'OFFERINSURANCEOPTION' => 'false'
            ],
            'doLogInUser' => true,
            'doFillBasket' => true
        ];

        //Test case that user enters different address in PayPal for country that has no PP attached in shop
        $data['CallbackOkForLoggedInUserChange'] = [
            'payPalData' => [
                'FIRSTNAME' => 'gerName',
                'LASTNAME' => 'gerlastname',
                'SHIPTONAME' => "Testuser",
                'SHIPTOSTREET' => 'Musterstr. 123',
                'SHIPTOCITY' => 'Antwerp',
                'SHIPTOZIP' => '2000',
                'SHIPTOCOUNTRY' => 'BE',
                'SHIPTOCOUNTRYCODE' => 'BE',
                'SHIPTOCOUNTRYNAME' => 'Belgien'
            ],
            'expected' => [
                'METHOD' => 'CallbackResponse',
                'NO_SHIPPING_OPTION_DETAILS' => 1
            ],
            'doLogInUser' => true,
            'doFillBasket' => true
        ];

        return $data;
    }

    /**
     * Test helper to assemble call back URl from PayPal to shop.
     *
     * @param string $sid Session id.
     * @param string $token Rtoken.
     * @param array $paypalData Optional paypal data.
     * @param int $language Optional language id.
     *
     * @return string
     */
    private function getCallbackUrl(AcceptanceTester $I, $sid, $token, $paypalData = [], $language = 0)
    {
        $callbackUrl = $I->getShopUrl() . 'index.php?';

        $data = [
            'lang' => $language,
            'sid' => $sid,
            'rtoken' => $token,
            'shp' => 1,
            'cl' => 'oepaypalexpresscheckoutdispatcher',
            'fnc' => 'processCallBack'
        ];

        $data = array_merge($data, $paypalData);

        foreach ($data as $key => $value) {
            $callbackUrl .= '&' . $key . '=' . urlencode($value);
        }

        return $callbackUrl;
    }

    /**
     * Get response from callback as array.
     *
     * @param array $requestParameters
     *
     * @return array
     */
    private function getCallbackResponse($callbackUrl, $sid)
    {
        $result = [];

        $curlHandler = oxNew(\OxidEsales\Eshop\Core\Curl::class);
        $curlHandler->setUrl($callbackUrl);
        $cookieString = 'sid=' . $sid . '; sid_key=oxid;';

        $curlHandler->setOption('CURLOPT_COOKIE', $cookieString);
        $curlHandler->setOption('CURLOPT_COOKIESESSION', true);
        $curlHandler->setOption('CURLOPT_USERAGENT', 'test user agent');

        $raw = $curlHandler->execute();

        $tmp = explode('&', $raw);
        if (is_array($tmp) && !empty($tmp)) {
            foreach ($tmp as $keyValue) {
                $sub = explode('=', $keyValue);
                $result[$sub[0]] = $sub[1];
            }
        }
        return $result;
    }

    /**
     * Test helper to check results.
     *
     * @param array $toBeAsserted
     * @param array $result
     */
    private function assertResults(AcceptanceTester $I, $toBeAsserted, $result)
    {
        foreach ($toBeAsserted as $key => $expected) {
            $I->assertTrue(array_key_exists($key, $result), "Key '{$key}' missing in result array.");
            $I->assertEquals($expected, $result[$key], "Value '{$expected}' for key '{$key}' not as expected.");
        }
    }
}
