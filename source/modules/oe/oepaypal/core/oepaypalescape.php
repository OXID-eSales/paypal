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
 * PayPal escape class
 */
class oePayPalEscape
{
    /**
     * Checks if passed parameter has special chars and replaces them.
     * Returns checked value.
     *
     * @param mixed $sValue value to process escaping
     *
     * @return mixed
     */
    public function escapeSpecialChars( $sValue )
    {
        if ( is_object( $sValue ) ) {
            return $sValue;
        }

        if ( is_array( $sValue ) ) {
            $sValue = $this->_escapeArraySpecialChars( $sValue );
        } elseif ( is_string( $sValue ) ) {
            $sValue = $this->_escapeStringSpecialChars( $sValue );
        }
        return $sValue;
    }

    /**
     * Checks if passed parameter has special chars and replaces them.
     * Returns checked value.
     *
     * @param array $sValue value to process escaping
     *
     * @return array
     */
    private function _escapeArraySpecialChars( $sValue )
    {
        $newValue = array();
        foreach ( $sValue as $sKey => $sVal ) {
            $sValidKey = $sKey;
            $sValidKey = $this->escapeSpecialChars( $sValidKey );
            $sVal = $this->escapeSpecialChars( $sVal );
            if ($sValidKey != $sKey) {
                unset ($sValue[$sKey]);
            }
            $newValue[$sValidKey] = $sVal;
        }
        return $newValue;
    }

    /**
     * Checks if passed parameter has special chars and replaces them.
     * Returns checked value.
     *
     * @param string $sValue value to process escaping
     *
     * @return string
     */
    private function _escapeStringSpecialChars( $sValue )
    {
        $sValue = str_replace( array( '&',     '<',    '>',    '"',      "'",      chr(0), '\\' ),
            array( '&amp;', '&lt;', '&gt;', '&quot;', '&#039;', '',     '&#092;' ),
            $sValue );

        return $sValue;
    }
}