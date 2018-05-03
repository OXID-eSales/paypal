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

namespace OxidEsales\PayPalModule\Core;

if (!defined('CURL_SSLVERSION_TLSV1_2')) {
    define('CURL_SSLVERSION_TLSV1_2', 6);
}

/**
 * PayPal Curl class
 */
class Curl
{
    /**
     * Host for header.
     *
     * @var string
     */
    protected $host = null;

    /**
     * Curl instance.
     *
     * @var resource
     */
    protected $curl = null;

    /**
     * Connection Charset.
     *
     * @var string
     */
    protected $connectionCharset = "UTF-8";

    /**
     * Data Charset.
     *
     * @var string
     */
    protected $dataCharset = "UTF-8";

    /**
     * Curl default parameters.
     *
     * @var array
     */
    protected $environmentParameters = array(
        'CURLOPT_VERBOSE'        => 0,
        'CURLOPT_SSL_VERIFYPEER' => false,
        'CURLOPT_SSL_VERIFYHOST' => false,
        'CURLOPT_SSLVERSION'     => CURL_SSLVERSION_TLSV1_2,
        'CURLOPT_RETURNTRANSFER' => 1,
        'CURLOPT_POST'           => 1,
        'CURLOPT_HTTP_VERSION'   => CURL_HTTP_VERSION_1_1,
    );

    /**
     * Parameter to be added to call url
     *
     * @var array | null
     */
    protected $parameters = null;


    /**
     * PayPal URL to call. Usually API address.
     *
     * @var string | null
     */
    protected $urlToCall = null;

    /**
     * Query like "param1=value1&param2=values2.."
     *
     * @return string
     */
    protected $query = null;

    /**
     * Curl call header.
     *
     * @var array
     */
    protected $header = null;

    /**
     * Sets host.
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Returns host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set header.
     *
     * @param array $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Forms header from host.
     *
     * @return array
     */
    public function getHeader()
    {
        if (is_null($this->header)) {
            $host = $this->getHost();

            $header = array();
            $header[] = 'POST /cgi-bin/webscr HTTP/1.1';
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
            if (isset($host)) {
                $header[] = 'Host: ' . $host;
            }
            $header[] = 'Connection: close';

            $this->setHeader($header);
        }

        return $this->header;
    }

    /**
     * Set connection charset
     *
     * @param string $charset charset
     */
    public function setConnectionCharset($charset)
    {
        $this->connectionCharset = $charset;
    }

    /**
     * Return connection charset
     *
     * @return string
     */
    public function getConnectionCharset()
    {
        return $this->connectionCharset;
    }

    /**
     * Set data charset
     *
     * @param string $dataCharset
     */
    public function setDataCharset($dataCharset)
    {
        $this->dataCharset = $dataCharset;
    }

    /**
     * Return data charset
     *
     * @return string
     */
    public function getDataCharset()
    {
        return $this->dataCharset;
    }

    /**
     * Return environment parameters
     *
     * @return array
     */
    public function getEnvironmentParameters()
    {
        return $this->environmentParameters;
    }

    /**
     * Sets one of Curl parameter.
     *
     * @param string $name  Curl parameter name.
     * @param mixed  $value Curl parameter value
     */
    public function setEnvironmentParameter($name, $value)
    {
        $this->environmentParameters[$name] = $value;
    }

    /**
     * Sets parameters to be added to call url.
     *
     * @param array $parameters parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Return parameters to be added to call url.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set query like "param1=value1&param2=values2.."
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Builds query like "param1=value1&param2=values2.."
     *
     * @return string
     */
    public function getQuery()
    {
        if (is_null($this->query)) {
            $params = $this->getParameters();
            $params = array_map(array($this, 'htmlDecode'), $params);
            $params = array_map(array($this, 'encode'), $params);

            $this->setQuery(http_build_query($params, "", "&"));
        }

        return $this->query;
    }

    /**
     * Set PayPal URL to call.
     *
     * @param string $urlToCall PayPal URL to call.
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalException if url is not valid
     */
    public function setUrlToCall($urlToCall)
    {
        if (false === filter_var($urlToCall, FILTER_VALIDATE_URL)) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalException $exception
             */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalException::class, 'URL to call is not valid.');
            throw $exception;
        }
        $this->urlToCall = $urlToCall;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrlToCall()
    {
        return $this->urlToCall;
    }

    /**
     * Sets resource
     *
     * @param resource $curl curl.
     */
    protected function setResource($curl)
    {
        $this->curl = $curl;
    }

    /**
     * Returns curl resource
     *
     * @return resource
     */
    protected function getResource()
    {
        if (is_null($this->curl)) {
            $this->setResource(curl_init());
        }

        return $this->curl;
    }

    /**
     * Executes curl call and returns response data as associative array.
     *
     * @return array
     */
    public function execute()
    {
        $this->setOptions();

        $response = $this->parseResponse($this->curlExecute());

        $this->close();

        return $response;
    }

    /**
     * Set Curl Parameters.
     */
    protected function setOptions()
    {
        foreach ($this->getEnvironmentParameters() as $name => $value) {
            $this->setOption(constant($name), $value);
        }

        $this->setOption(CURLOPT_HTTPHEADER, $this->getHeader());
        $this->setOption(CURLOPT_URL, $this->getUrlToCall());
        $this->setOption(CURLOPT_POSTFIELDS, $this->getQuery());
    }

    /**
     * Wrapper function to be mocked for testing.
     *
     * @param string $name  curl field name to set value to.
     * @param mixed  $value curl field value to set.
     */
    protected function setOption($name, $value)
    {
        curl_setopt($this->getResource(), $name, $value);
    }

    /**
     * Wrapper function to be mocked for testing.
     *
     * @return string
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalException on curl errors
     */
    protected function curlExecute()
    {
        $response = curl_exec($this->getResource());

        $curlErrorNumber = $this->getErrorNumber();
        if ($curlErrorNumber) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalException $exception
             */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalException::class, 'Curl error: ' . $curlErrorNumber);
            throw $exception;
        }

        return $response;
    }

    /**
     * Wrapper function to be mocked for testing.
     */
    protected function close()
    {
        curl_close($this->getResource());
    }

    /**
     * Parse curl response and strip it to safe form.
     *
     * @param string $response curl response.
     *
     * @return array
     */
    protected function parseResponse($response)
    {
        $result = [];

        // processing results
        parse_str($response, $result);

        // stripping slashes
        $result = array_map(array($this, 'decode'), $result);

        return $result;
    }

    /**
     * Check if curl has errors. Set error message if has.
     *
     * @return int
     */
    protected function getErrorNumber()
    {
        return curl_errno($this->getResource());
    }

    /**
     * Decode (if needed) given query from UTF8
     *
     * @param string $string query
     *
     * @return string
     */
    protected function htmlDecode($string)
    {
        $string = html_entity_decode($string, ENT_QUOTES, $this->getConnectionCharset());

        return $string;
    }

    /**
     * Decode (if needed) given query from UTF8
     *
     * @param string $string query
     *
     * @return string
     */
    protected function decode($string)
    {
        $charset = $this->getDataCharset();
        if ($charset !== $this->getConnectionCharset()) {
            $string = iconv($this->getConnectionCharset(), $charset, $string);
        }
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }

        return $string;
    }

    /**
     * Encodes (if needed) given query to UTF8
     *
     * @param string $string query
     *
     * @return string
     */
    protected function encode($string)
    {
        $charset = $this->getDataCharset();
        if ($charset !== $this->getConnectionCharset()) {
            $string = iconv($charset, $this->getConnectionCharset(), $string);
        }

        return $string;
    }
}
