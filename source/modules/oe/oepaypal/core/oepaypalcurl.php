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

/**
 * PayPal Curl class
 */
class oePayPalCurl
{
    /**
     * Host for header.
     * @var string
     */
    protected $_sHost = null;

    /**
     * Curl instance.
     * @var resource
     */
    protected $_rCurl = null;

    /**
     * Connection Charset.
     * @var string
     */
    protected $_sConnectionCharset = "UTF-8";

    /**
     * Data Charset.
     * @var string
     */
    protected $_sDataCharset = "UTF-8";

    /**
     * Curl default parameters.
     * @var array
     */
    protected $_aEnvironmentParameters = array(
        'CURLOPT_VERBOSE' => 0,
        'CURLOPT_SSL_VERIFYPEER' => false,
        'CURLOPT_SSL_VERIFYHOST' => false,
        'CURLOPT_SSLVERSION' => 3,
        'CURLOPT_RETURNTRANSFER' => 1,
        'CURLOPT_POST' => 1,
        'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
    );

    /**
     * Parameter to be added to call url
     *
     * @var array | null
    */
    protected $_aParameters = null;


    /**
     * PayPal URL to call. Usually API address.
     * @var string | null
     */
    protected $_sUrlToCall = null;

    /**
     * Query like "param1=value1&param2=values2.."
     *
     * @return string
     */
    protected $_sQuery = null;

    /**
     * Curl call header.
     * @var array
     */
    protected $_aHeader = null;

    /**
     * Sets host.
     * @param string $sHost
     */
    public function setHost( $sHost )
    {
        $this->_sHost = $sHost;
    }

    /**
     * Returns host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_sHost;
    }

    /**
     * Set header.
     * @param array $aHeader
     */
    public function setHeader( $aHeader )
    {
        $this->_aHeader = $aHeader;
    }

    /**
     * Forms header from host.
     *
     * @return array
     */
    public function getHeader()
    {
        if ( is_null( $this->_aHeader ) ) {
            $sHost = $this->getHost();

            $aHeader = array();
            $aHeader[] = 'POST /cgi-bin/webscr HTTP/1.1';
            $aHeader[] = 'Content-Type: application/x-www-form-urlencoded';
            if ( isset( $sHost ) ) {
                $aHeader[] = 'Host: '. $sHost;
            }
            $aHeader[] = 'Connection: close';

            $this->setHeader( $aHeader );
        }

        return $this->_aHeader;
    }

    /**
     * Set connection charset
     *
     * @param string $sCharset charset
     */
    public function setConnectionCharset( $sCharset )
    {
        $this->_sConnectionCharset = $sCharset;
    }

    /**
     * Return connection charset
     *
     * @return string
     */
    public function getConnectionCharset()
    {
        return $this->_sConnectionCharset;
    }

    /**
     * Set data charset
     */
    public function setDataCharset( $sDataCharset )
    {
        $this->_sDataCharset = $sDataCharset;
    }

    /**
     * Return data charset
     *
     * @return string
     */
    public function getDataCharset()
    {
        return $this->_sDataCharset;
    }

    /**
     * Return environment parameters
     *
     * @return array
     */
    public function getEnvironmentParameters()
    {
        return $this->_aEnvironmentParameters;
    }

    /**
     * Sets one of Curl parameter.
     *
     * @param string $sName Curl parameter name.
     * @param mixed $mValue Curl parameter value
     */
    public function setEnvironmentParameter( $sName, $mValue )
    {
        $this->_aEnvironmentParameters[$sName] = $mValue;
    }

    /**
     * Sets parameters to be added to call url.
     *
     * @param array $aParameters parameters
     */
    public function setParameters( $aParameters )
    {
        $this->_aParameters = $aParameters;
    }

    /**
     * Return parameters to be added to call url.
     *
     * return array
     */
    public function getParameters()
    {
        return $this->_aParameters;
    }

    /**
     * Set query like "param1=value1&param2=values2.."
     */
    public function setQuery( $sQuery )
    {
        $this->_sQuery = $sQuery;
    }

    /**
     * Builds query like "param1=value1&param2=values2.."
     *
     * @return string
     */
    public function getQuery()
    {
        if ( is_null( $this->_sQuery ) ) {
            $aParams = $this->getParameters();
            $aParams = array_map(array($this, '_htmlDecode'), $aParams);
            $aParams = array_map(array($this, '_encode'), $aParams);

            $this->setQuery( http_build_query( $aParams, "", "&" ) );
        }

        return $this->_sQuery;
    }

    /**
     * @param string $sUrlToCall PayPal URL to call.
     *
     * @throws oePayPalException if url is not valid
     */
    public function setUrlToCall( $sUrlToCall )
    {
        if ( false === filter_var( $sUrlToCall, FILTER_VALIDATE_URL ) ) {
            /**
             * @var oePayPalException $oException
             */
            $oException = oxNew( 'oePayPalException',  'URL to call is not valid.' ) ;
            throw $oException;
        }
        $this->_sUrlToCall = $sUrlToCall;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrlToCall()
    {
        return $this->_sUrlToCall;
    }

    /**
     * Sets resource
     *
     * @param resource $rCurl curl.
     */
    protected function _setResource( $rCurl )
    {
        $this->_rCurl = $rCurl;
    }

    /**
     * Returns curl resource
     *
     * @return resource
     */
    protected function _getResource()
    {
        if ( is_null( $this->_rCurl ) ) {
            $this->_setResource( curl_init() );
        }

        return $this->_rCurl;
    }

    /**
     * Executes curl call and returns response data as associative array.
     *
     * @return array
     */
    public function execute()
    {
        $this->_setOptions();

        $aResponse = $this->_parseResponse( $this->_execute() );

        $this->_close();

        return $aResponse;
    }

    /**
     * Set Curl Parameters.
     */
    protected function _setOptions()
    {
        foreach( $this->getEnvironmentParameters() as $sName => $mValue  ) {
            $this->_setOption( constant( $sName ), $mValue );
        }

        $this->_setOption( CURLOPT_HTTPHEADER, $this->getHeader() );
        $this->_setOption( CURLOPT_URL, $this->getUrlToCall() );
        $this->_setOption( CURLOPT_POSTFIELDS, $this->getQuery() );
    }

    /**
     * Wrapper function to be mocked for testing.
     *
     * @param string $sName curl field name to set value to.
     * @param string $sValue curl field value to set.
     */
    protected function _setOption( $sName, $sValue )
    {
        curl_setopt( $this->_getResource(), $sName, $sValue );
    }

    /**
     * Wrapper function to be mocked for testing.
     *
     * @return string
     *
     * @throws oePayPalException on curl errors
     */
    protected function _execute()
    {
        $sResponse = curl_exec( $this->_getResource() );

        $iCurlErrorNumber = $this->_getErrorNumber();
        if ( $iCurlErrorNumber ) {
            /**
             * @var oePayPalException $oException
             */
            $oException = oxNew( "oePayPalException",  'Curl error: '. $iCurlErrorNumber ) ;
            throw $oException;
        }

        return $sResponse;
    }

    /**
     * Wrapper function to be mocked for testing.
     */
    protected function _close()
    {
        curl_close( $this->_getResource() );
    }

    /**
     * Parse curl response and strip it to safe form.
     *
     * @param string $sResponse curl response.
     *
     * @return array
     */
    protected function _parseResponse( $sResponse )
    {
        // processing results
        $aResponse = array();
        parse_str( $sResponse, $aResponse );

        // stripping slashes
        $aResponse = array_map( array( $this, '_decode' ), $aResponse );

        return $aResponse;
    }

    /**
     * Check if curl has errors. Set error message if has.
     *
     * @return int
     */
    protected function _getErrorNumber()
    {
        return curl_errno( $this->_getResource() );
    }

    /**
     * Decode (if needed) given query from UTF8
     *
     * @param string $sString query
     *
     * @return string
     */
    protected function _htmlDecode( $sString )
    {
        $sString = html_entity_decode( $sString, ENT_QUOTES, $this->getConnectionCharset() );

        return $sString;
    }

    /**
     * Decode (if needed) given query from UTF8
     *
     * @param string $sString query
     *
     * @return string
     */
    protected function _decode( $sString )
    {
        $sCharset = $this->getDataCharset();
        if ( $sCharset !== $this->getConnectionCharset() ) {
            $sString = iconv( $this->getConnectionCharset(), $sCharset, $sString );
        }
        if ( get_magic_quotes_gpc() ) {
            $sString = stripslashes( $sString );
        }
        return $sString;
    }

    /**
     * Encodes (if needed) given query to UTF8
     *
     * @param string $sString query
     *
     * @return string
     */
    protected function _encode( $sString )
    {
        $sCharset = $this->getDataCharset();
        if ( $sCharset !== $this->getConnectionCharset() ) {
            $sString = iconv( $sCharset, $this->getConnectionCharset(), $sString );

        }

        return $sString;
    }
}