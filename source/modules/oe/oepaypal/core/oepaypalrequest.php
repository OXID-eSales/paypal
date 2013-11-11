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
 * PayPal Request class
 */
class oePayPalRequest
{
    /**
     * Get post.
     * @return array
     */
    public function getPost()
    {
        $aPost = array();

        if ( !empty( $_POST ) ) {
            $aPost = $_POST;
        }

        return $aPost;
    }
    /**
     * Get get.
     * @return array
     */
    public function getGet()
    {
        $aGet = array();

        if ( !empty( $_GET ) ) {
            $aGet = $_GET;
        }

        return $aGet;
    }

    /**
     * Returns value of parameter stored in POST,GET.
     *
     * @param string $sName Name of parameter
     * @param bool   $blRaw mark to return not escaped parameter
     *
     * @return mixed
     */
    public function getRequestParameter( $sName, $blRaw = false )
    {
        $sValue = null;

        $sValue = $this->getPostParameter( $sName, $blRaw );
        if ( !isset( $sValue ) ) {
            $sValue = $this->getGetParameter( $sName, $blRaw );
        }

        return $sValue;
    }

    /**
     * Returns value of parameter stored in POST.
     *
     * @param string $sName Name of parameter
     * @param bool   $blRaw mark to return not escaped parameter
     *
     * @return mixed
     */
    public function getPostParameter( $sName, $blRaw = false )
    {
        $sValue = null;
        $aPost = $this->getPost();

        if ( isset( $aPost[ $sName ] ) ) {
            $sValue = $aPost[ $sName ];
        }

        if ( $sValue !== null && !$blRaw ) {
            $sValue = $this->escapeSpecialChars( $sValue );
        }

        return $sValue;
    }

    /**
     * Returns value of parameter stored in GET.
     *
     * @param string $sName Name of parameter
     * @param bool   $blRaw mark to return not escaped parameter
     *
     * @return mixed
     */
    public function getGetParameter( $sName, $blRaw = false )
    {
        $sValue = null;
        $aGet  = $this->getGet();

        if ( isset( $aGet[ $sName ] ) ) {
            $sValue = $aGet[ $sName ];
        }

        if ( $sValue !== null && !$blRaw ) {
            $sValue = $this->escapeSpecialChars( $sValue );
        }

        return $sValue;
    }

    /**
     * Wrapper for PayPal escape class.
     *
     * @param mixed $sValue value to escape
     *
     * @return mixed
     */
    public function escapeSpecialChars( $sValue )
    {
        $oPayPalEscape = oxNew( 'oePayPalEscape' );
        return $oPayPalEscape->escapeSpecialChars( $sValue );
    }
}