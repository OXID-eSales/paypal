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
 * Base logger class
 */
class oePayPalLogger
{
    /**
     * Logger session id
     * @var string
     */
    protected $_sLoggerSessionId;

    /**
     * Log title
     */
    protected $_sLogTitle = '';

    /**
     * Sets logger session id
     *
     * @param string $sId session id
     *
     * @return null
     */
    public function setLoggerSessionId( $sId )
    {
        $this->_sLoggerSessionId = $sId;
    }

    /**
     * Returns loggers session id
     *
     * @return string
     */
    public function getLoggerSessionId()
    {
        return $this->_sLoggerSessionId;
    }

    /**
     * Returns full log file path
     *
     * @return string
     */
    protected function _getLogFilePath()
    {
        return getShopBasePath().'modules/oe/oepaypal/logs/log.txt';
    }

    /**
     * Set log title
     *
     * @param string $sTitle Log title
     *
     * @return bool
     */
    public function setTitle( $sTitle )
    {
        $this->_sLogTitle = $sTitle;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_sLogTitle;
    }

    /**
     * Writes log message
     *
     * @param mixed $mLogData logger data
     *
     * @return null
     */
    public function log( $mLogData )
    {
        $oH = @fopen( $this->_getLogFilePath(), "a+" );
        if ( $oH !== false ) {
            if (is_string( $mLogData ) ) {
                parse_str( $mLogData, $aResult );
            } else {
                $aResult = $mLogData;
            }

            if ( is_array( $aResult ) ) {
                foreach ( $aResult as $sKey => $sValue ) {
                    $aResult[$sKey] = urldecode( $sValue );
                }
            }

            fwrite( $oH, "======================= " . $this->getTitle() . " [" . date("Y-m-d H:i:s") . "] ======================= #\n\n" );
            fwrite( $oH, "SESS ID: " . $this->getLoggerSessionId() . "\n" );
            fwrite( $oH, trim( var_export( $aResult, true ) ) . "\n\n" );
            @fclose( $oH );
        }

        //resetting log title
        $this->setTitle( '' );
    }
}
