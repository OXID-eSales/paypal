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

/**
 * PayPal escape class
 */
class Escape
{
    /**
     * Checks if passed parameter has special chars and replaces them.
     * Returns checked value.
     *
     * @param mixed $value value to process escaping
     *
     * @return mixed
     */
    public function escapeSpecialChars($value)
    {
        if (is_object($value)) {
            return $value;
        }

        if (is_array($value)) {
            $value = $this->escapeArraySpecialChars($value);
        } elseif (is_string($value)) {
            $value = $this->escapeStringSpecialChars($value);
        }

        return $value;
    }

    /**
     * Checks if passed parameter has special chars and replaces them.
     * Returns checked value.
     *
     * @param array $value value to process escaping
     *
     * @return array
     */
    private function escapeArraySpecialChars($value)
    {
        $newValue = array();
        foreach ($value as $key => $val) {
            $validKey = $key;
            $validKey = $this->escapeSpecialChars($validKey);
            $val = $this->escapeSpecialChars($val);
            if ($validKey != $key) {
                unset($value[$key]);
            }
            $newValue[$validKey] = $val;
        }

        return $newValue;
    }

    /**
     * Checks if passed parameter has special chars and replaces them.
     * Returns checked value.
     *
     * @param string $value value to process escaping
     *
     * @return string
     */
    private function escapeStringSpecialChars($value)
    {
        $value = str_replace(
            array('&', '<', '>', '"', "'", chr(0), '\\'),
            array('&amp;', '&lt;', '&gt;', '&quot;', '&#039;', '', '&#092;'),
            $value
        );

        return $value;
    }
}
