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

namespace OxidEsales\PayPalModule\Tests\Acceptance;

/**
 * Class CallbackFromPayPalTest
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class CallbackFromPayPalTest extends BaseAcceptanceTestCase
{
    /**
     * Set up fixture.
     */
    protected function setUp()
    {
        parent::setUp();

        //Add delivery methods
        $this->importSql(__DIR__ . '/testSql/newDeliveryMethod_' . SHOP_EDITION . '.sql');
    }

    // ------------------------ PayPal module ----------------------------------

    /**
     * Data provider.
     *
     * @return array
     */
    public function providerCallBackTests()
    {
        $data = [];

        //Test case that callback is provided a session id but the basket related to that SID is empty and PP data empty.
        $data['CallbackEmptyBasketNoPayPalData'] = ['payPalData' => [],
                                                    'expected'   => ['METHOD' => 'CallbackResponse',
                                                                     'NO_SHIPPING_OPTION_DETAILS' => 1],
                                                    'doLogInUser' => false,
                                                    'changeAmount' => ''];

        //Test case that callback data from PayPal is incomplete. Basket empty.
        $data['LoggedInUserEmptyBasketUnknownCountry'] = ['payPalData'  => ['FIRSTNAME'    => 'gerName',
                                                                            'LASTNAME'     => 'gerlastname',
                                                                            'SHIPTONAME'   => "Testuser",
                                                                            'SHIPTOSTREET' => 'Musterstr. 123',
                                                                            'SHIPTOCITY'   => 'Musterstadt',
                                                                            'SHIPTOZIP'    => '79098'],
                                                          'expected'    => ['METHOD'                     => 'CallbackResponse',
                                                                            'NO_SHIPPING_OPTION_DETAILS' => 1],
                                                          'doLogInUser' => true,
                                                          'changeAmount' => ''];

        //Test case that callback data from PayPal is incomplete. Basket filled.
        $data['LoggedInUserEmptyBasketUnknownCountry'] = ['payPalData'  => ['FIRSTNAME'    => 'gerName',
                                                                            'LASTNAME'     => 'gerlastname',
                                                                            'SHIPTONAME'   => "Testuser",
                                                                            'SHIPTOSTREET' => 'Musterstr. 123',
                                                                            'SHIPTOCITY'   => 'Musterstadt',
                                                                            'SHIPTOZIP'    => '79098'],
                                                          'expected'    => ['METHOD'                     => 'CallbackResponse',
                                                                            'NO_SHIPPING_OPTION_DETAILS' => 1],
                                                          'doLogInUser' => true,
                                                          'changeAmount' => ''];

        //Test case that callback data from PayPal is complete. Basket empty.
        $data['LoggedInUserFilledBasketUnknownCountry'] = ['payPalData'  => ['FIRSTNAME'         => 'gerName',
                                                                             'LASTNAME'          => 'gerlastname',
                                                                             'SHIPTONAME'        => "Testuser",
                                                                             'SHIPTOSTREET'      => 'Musterstr. 123',
                                                                             'SHIPTOCITY'        => 'Musterstadt',
                                                                             'SHIPTOZIP'         => '79098',
                                                                             'SHIPTOCOUNTRY'     => 'DE',
                                                                             'SHIPTOCOUNTRYCODE' => 'DE',
                                                                             'SHIPTOCOUNTRYNAME' => 'Germany'],
                                                           'expected'    => ['METHOD'                     => 'CallbackResponse',
                                                                             'L_SHIPPINGOPTIONNAME0'      => 'Test+S%26H+set',
                                                                             'L_SHIPPINGOPTIONLABEL0'     => 'Preis',
                                                                             'L_SHIPPINGOPTIONAMOUNT0'    => '0.00',
                                                                             'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                                                                             'L_TAXAMT0'                  => '0.00',
                                                                             'L_SHIPPINGOPTIONNAME1'      => 'Beispiel+Set1%3A+UPS+48+Std.',
                                                                             'L_SHIPPINGOPTIONLABEL1'     => 'Preis',
                                                                             'L_SHIPPINGOPTIONAMOUNT1'    => '0.00',
                                                                             'L_SHIPPINGOPTIONISDEFAULT1' => 'false',
                                                                             'L_TAXAMT1'                  => '0.00',
                                                                             'OFFERINSURANCEOPTION'       => 'false'],
                                                           'doLogInUser' => true,
                                                           'changeAmount' => ''];

        //Test case that callback data from PayPal is complete. Basket filled.
        $data['CallbackOkForLoggedInUserGermany'] = ['payPalData'  => ['FIRSTNAME'         => 'gerName',
                                                                       'LASTNAME'          => 'gerlastname',
                                                                       'SHIPTONAME'        => "Testuser",
                                                                       'SHIPTOSTREET'      => 'Musterstr. 123',
                                                                       'SHIPTOCITY'        => 'Musterstadt',
                                                                       'SHIPTOZIP'         => '79098',
                                                                       'SHIPTOCOUNTRY'     => 'DE',
                                                                       'SHIPTOCOUNTRYCODE' => 'DE',
                                                                       'SHIPTOCOUNTRYNAME' => 'Germany'],
                                                     'expected'    => ['METHOD'                     => 'CallbackResponse',
                                                                       'L_SHIPPINGOPTIONNAME0'      => 'Test+S%26H+set',
                                                                       'L_SHIPPINGOPTIONLABEL0'     => 'Preis',
                                                                       'L_SHIPPINGOPTIONAMOUNT0'    => '0.00',
                                                                       'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                                                                       'L_TAXAMT0'                  => '0.00',
                                                                       'L_SHIPPINGOPTIONNAME1'      => 'Beispiel+Set1%3A+UPS+48+Std.',
                                                                       'L_SHIPPINGOPTIONLABEL1'     => 'Preis',
                                                                       'L_SHIPPINGOPTIONAMOUNT1'    => '0.00',
                                                                       'L_SHIPPINGOPTIONISDEFAULT1' => 'false',
                                                                       'L_TAXAMT1'                  => '0.00',
                                                                       'OFFERINSURANCEOPTION'       => 'false'],
                                                     'doLogInUser' => true,
                                                     'changeAmount' => ''];

        //Test case that user enters different address in PayPal for country that has no PP attached in shop
        $data['CallbackOkForLoggedInUserChange'] = ['payPalData'  => ['FIRSTNAME'         => 'gerName',
                                                                      'LASTNAME'          => 'gerlastname',
                                                                      'SHIPTONAME'        => "Testuser",
                                                                      'SHIPTOSTREET'      => 'Musterstr. 123',
                                                                      'SHIPTOCITY'        => 'Antwerp',
                                                                      'SHIPTOZIP'         => '2000',
                                                                      'SHIPTOCOUNTRY'     => 'BE',
                                                                      'SHIPTOCOUNTRYCODE' => 'BE',
                                                                      'SHIPTOCOUNTRYNAME' => 'Belgien'],
                                                     'expected'    => ['METHOD'                     => 'CallbackResponse',
                                                                       'NO_SHIPPING_OPTION_DETAILS' => 1],
                                                     'doLogInUser' => true,
                                                     'changeAmount' => ''];

        //Test case that user enters different address in PayPal for country that has PP attached in shop
        $data['CallbackOkForLoggedInUserGetDelSet'] = ['payPalData'  => ['FIRSTNAME'         => 'gerName',
                                                                         'LASTNAME'          => 'gerlastname',
                                                                         'SHIPTONAME'        => "Testuser",
                                                                         'SHIPTOSTREET'      => 'Universitätsring 1',
                                                                         'SHIPTOCITY'        => 'Wien',
                                                                         'SHIPTOZIP'         => '1010',
                                                                         'SHIPTOCOUNTRY'     => 'AT',
                                                                         'SHIPTOCOUNTRYCODE' => 'AT',
                                                                         'SHIPTOCOUNTRYNAME' => 'Austria'],
                                                       'expected'    => ['METHOD'                     => 'CallbackResponse',
                                                                         'L_SHIPPINGOPTIONNAME0'      => 'Test+Paypal%3A6hour',
                                                                         'L_SHIPPINGOPTIONLABEL0'     => 'Preis',
                                                                         'L_SHIPPINGOPTIONAMOUNT0'    => '0.50',
                                                                         'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                                                                         'L_TAXAMT0'                  => '0.00',
                                                                         'L_SHIPPINGOPTIONNAME1'      => 'Test+Paypal%3A12hour',
                                                                         'L_SHIPPINGOPTIONLABEL1'     => 'Preis',
                                                                         'L_SHIPPINGOPTIONAMOUNT1'    => '0.90',
                                                                         'L_SHIPPINGOPTIONISDEFAULT1' => 'false',
                                                                         'L_TAXAMT1'                  => '0.00',
                                                                         'L_SHIPPINGOPTIONNAME2'      => 'Beispiel+Set1%3A+UPS+48+Std.',
                                                                         'L_SHIPPINGOPTIONLABEL2'     => 'Preis',
                                                                         'L_SHIPPINGOPTIONAMOUNT2'    => '0.00',
                                                                         'L_SHIPPINGOPTIONISDEFAULT2' => 'false',
                                                                         'L_TAXAMT2'                  => '0.00',
                                                                         'OFFERINSURANCEOPTION'       => 'false'],
                                                       'doLogInUser' => true,
                                                       'changeAmount' => ''];

        //Test case that delivery costs depend on article overall amount
        $data['CallbackOkForLoggedInUserATAmount200'] = ['payPalData'  => ['FIRSTNAME'         => 'gerName',
                                                                          'LASTNAME'          => 'gerlastname',
                                                                          'SHIPTONAME'        => "Testuser",
                                                                          'SHIPTOSTREET'      => 'Universitätsring 1',
                                                                          'SHIPTOCITY'        => 'Wien',
                                                                          'SHIPTOZIP'         => '1010',
                                                                          'SHIPTOCOUNTRY'     => 'AT',
                                                                          'SHIPTOCOUNTRYCODE' => 'AT',
                                                                          'SHIPTOCOUNTRYNAME' => 'Austria'],
                                                        'expected'    => ['METHOD' => 'CallbackResponse',
                                                                          'L_SHIPPINGOPTIONNAME0' => 'Beispiel+Set1%3A+UPS+48+Std.',
                                                                          'L_SHIPPINGOPTIONLABEL0' => 'Preis',
                                                                          'L_SHIPPINGOPTIONAMOUNT0' => '0.00',
                                                                          'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                                                                          'L_TAXAMT0' => '0.00',
                                                                          'OFFERINSURANCEOPTION' => 'false'],
                                                        'doLogInUser' => true,
                                                        'changeAmount' => 'changeAmountTo200'];

        //Test case that delivery costs depend on article overall amount
        $data['CallbackOkForLoggedInUserATAmount20'] = ['payPalData'  => ['FIRSTNAME'         => 'gerName',
                                                                           'LASTNAME'          => 'gerlastname',
                                                                           'SHIPTONAME'        => "Testuser",
                                                                           'SHIPTOSTREET'      => 'Universitätsring 1',
                                                                           'SHIPTOCITY'        => 'Wien',
                                                                           'SHIPTOZIP'         => '1010',
                                                                           'SHIPTOCOUNTRY'     => 'AT',
                                                                           'SHIPTOCOUNTRYCODE' => 'AT',
                                                                           'SHIPTOCOUNTRYNAME' => 'Austria'],
                                                         'expected'    => ['METHOD' => 'CallbackResponse',
                                                                           'L_SHIPPINGOPTIONNAME0' => 'Test+Paypal%3A6hour',
                                                                           'L_SHIPPINGOPTIONLABEL0' => 'Preis',
                                                                           'L_SHIPPINGOPTIONAMOUNT0' => '0.40',
                                                                           'L_SHIPPINGOPTIONISDEFAULT0' => 'true',
                                                                           'L_TAXAMT0' => '0.00',
                                                                           'L_SHIPPINGOPTIONNAME1' => 'Test+Paypal%3A12hour',
                                                                           'L_SHIPPINGOPTIONLABEL1' => 'Preis',
                                                                           'L_SHIPPINGOPTIONAMOUNT1' => '0.80',
                                                                           'L_SHIPPINGOPTIONISDEFAULT1' => 'false',
                                                                           'L_TAXAMT1' => '0.00',
                                                                           'L_SHIPPINGOPTIONNAME2' => 'Beispiel+Set1%3A+UPS+48+Std.',
                                                                           'L_SHIPPINGOPTIONLABEL2' => 'Preis',
                                                                           'L_SHIPPINGOPTIONAMOUNT2' => '0.00',
                                                                           'L_SHIPPINGOPTIONISDEFAULT2' => 'false',
                                                                           'L_TAXAMT2' => '0.00',
                                                                           'OFFERINSURANCEOPTION' => 'false'],
                                                         'doLogInUser' => true,
                                                         'changeAmount' => 'changeAmountTo20'];

        return $data;
    }

    /**
     * Testing different countries with shipping rules assigned to these countries
     *
     * @group paypal_standalone
     * @group paypal_external
     *
     * @dataProvider providerCallBackTests
     */
    public function testPayPalCallback($payPalData, $expected, $doLogInUser, $changeAmount)
    {
        $this->openShop();

        //Search for the product and add to cart
        $this->searchFor('1001');
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);
        $this->openBasket('German');

        if ($doLogInUser) {
            $this->logUserIn();
        }
        //Change amount, only works for logged in user.
        if ($changeAmount) {
            $this->clickNextStepInShopBasket();
            $this->changeCountryInBasketStepTwo('Austria');
            $this->$changeAmount();
        }

        //Go to PayPal via PayPal Express with "Display cart in PayPal"
        $this->selectPayPalExpressCheckout();

        //Check callback
        $token = $this->extractToken($this->getRawCallbackUrl());
        $sid = $this->getBrowserSessionId();
        $callbackUrl = $this->getCallbackUrl($sid, $token, $payPalData);
        $result = $this->getCallbackResponse($callbackUrl, $sid);
        $this->assertResults($expected, $result);
    }

    /**
     * Get browser session id that was sent to PayPal.
     *
     * @return string
     */
    private function getBrowserSessionId()
    {
        $data = $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'getLogData');

        //last thing in log has to be the response from PayPal
        $response = array_pop($data);
        $sessionId = $response->sid;

        return $sessionId;
    }

    /**
     * Test helper to get raw callback url.
     *
     * @return string
     */
    private function getRawCallbackUrl()
    {
        $data = $this->callShopSC(\OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper::class, 'getLogData');

        //next last thing in log has to be the request to PayPal
        $callbackUrl = '';
        array_pop($data);
        $request = array_pop($data);
        if (is_object($request)) {
            $callbackUrl = $request->data['CALLBACK'];
        }

        return $callbackUrl;
    }

    /**
     * Test helper to assemble call back URl from PayPal to shop.
     *
     * @param string $sid        Session id.
     * @param string $token      Rtoken.
     * @param array  $paypalData Optional paypal data.
     * @param int    $language   Optional language id.
     *
     * @return string
     */
    private function getCallbackUrl($sid, $token, $paypalData = [], $language = 0)
    {
        $shopUrl = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopUrl();
        $callbackUrl = $shopUrl . 'index.php?';
        $shopId = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();

        $data = ['lang' => $language,
                 'sid' => $sid,
                 'rtoken' => $token,
                 'shp' => $shopId,
                 'cl' => 'oepaypalexpresscheckoutdispatcher',
                 'fnc' => 'processCallBack'];

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
    private function assertResults($toBeAsserted, $result)
    {
        foreach ($toBeAsserted as $key => $expected) {
            $this->assertTrue(array_key_exists($key, $result), "Key '{$key}' missing in result array.");
            $this->assertEquals($expected, $result[$key], "Value '{$expected}' for key '{$key}' not as expected.");
        }
    }

    /**
     * Test helper to log in user.
     */
    private function logUserIn()
    {
        //Login to shop and go to the basket
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("link=Test product 1", "Purchased product name is not displayed");
        $this->assertElementPresent("//tr[@id='cartItem_1']/td[3]/div[2]");
        $this->assertEquals("Grand total: 0,99 €", $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), "Grand total is not displayed correctly");
        $this->assertTextPresent("Shipping costs:", "Shipping costs is not displayed correctly");
        $this->assertTextPresent("?");
        $this->assertTrue($this->isChecked("//input[@name='displayCartInPayPal' and @value='1']"));
        $this->assertTextPresent("Display cart in PayPal", "Text:Display cart in PayPal for checkbox not displayed");
        $this->assertElementPresent("displayCartInPayPal", "Checkbox:Display cart in PayPal not displayed");
    }

    /**
     * Q&D to get the token from original callback url.
     *
     * @param string $url
     *
     * @return string
     */
    private function extractToken($url)
    {
        $tmp = explode('rtoken=', $url);
        $tmp = explode('&', $tmp[1]);
        return $tmp[0];
    }

    /**
     * Test helper to change amount of items in basket.
     */
    private function changeAmountTo200()
    {
        $this->clickFirstStepInShopBasket();

        $this->assertEquals('Total products (incl. tax): 0,99 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")), 'Total price not displayed in basket');
        $this->assertEquals('Total products (excl. tax): 0,83 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")), 'Total price not displayed in basket');
        $this->assertEquals('Shipping costs: 0,50 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), 'Grand total is not displayed correctly');
        $this->assertEquals('Grand total: 1,49 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), 'Grand total is not displayed correctly');
        $this->type('id=am_1', '200');
        $this->click('id=basketUpdate');
        sleep(3);
        $this->assertEquals('Total products (incl. tax): 198,00 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")), 'Total price not displayed in basket');
        $this->assertEquals('Total products (excl. tax): 166,39 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")), 'Total price not displayed in basket');
        $this->assertEquals('Shipping costs: 6,90 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), 'Grand total is not displayed correctly');
        $this->assertEquals('Grand total: 204,90 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), 'Grand total is not displayed correctly');
    }

    /**
     * Test helper to change amount of items in basket.
     */
    private function changeAmountTo20()
    {
        $this->clickFirstStepInShopBasket();

        $this->assertEquals('Total products (incl. tax): 0,99 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")), 'Total price not displayed in basket');
        $this->assertEquals('Total products (excl. tax): 0,83 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")), 'Total price not displayed in basket');
        $this->assertEquals('Shipping costs: 0,50 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), 'Grand total is not displayed correctly');
        $this->assertEquals('Grand total: 1,49 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), 'Grand total is not displayed correctly');
        $this->type('id=am_1', '20');
        $this->click('id=basketUpdate');
        sleep(3);
        $this->assertEquals('Total products (incl. tax): 19,80 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[3]")), 'Total price not displayed in basket');
        $this->assertEquals('Total products (excl. tax): 16,64 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[1]")), 'Total price not displayed in basket');
        $this->assertEquals('Shipping costs: 0,40 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[4]")), 'Grand total is not displayed correctly');
        $this->assertEquals('Grand total: 20,20 €', $this->clearString($this->getText("//div[@id='basketSummary']//tr[5]")), 'Grand total is not displayed correctly');
    }
}
