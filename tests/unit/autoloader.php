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

class Autoloader
{

    /**
     * @param $sClass
     */
    protected $_sBasePath = '';

    /*
     * Auto load given class
     */
    public function autoload( $sClass )
    {
        if ( !class_exists( $sClass ) ) {
            $sFileName = strtolower( $sClass ) . '.php';
            $sFile = $this->_findFile( $this->getBasePath(), $sFileName );

            if ( $sFile ) {
                require_once ( $sFile );
            }
        }
    }

    /**
     * @param $sBasePath
     */
    public function setBasePath( $sBasePath )
    {
        $this->_sBasePath = $sBasePath;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->_sBasePath;
    }

    /**
     * Search through directories recursively for this file
     *
     * @param $sPath
     * @param $sFileName
     * @return string
     */
    protected function _findFile( $sPath, $sFileName )
    {
        $oFile = new DirectoryIterator( $sPath );
        $sFile = null;
        while( $oFile->valid() ) {
            if ( !$oFile->isDot() ) {
                if ( $oFile->isDir() ) {
                    $sFile = $this->_findFile( $oFile->getPathname(), $sFileName );
                } else if ( $oFile->getFilename() === $sFileName ) {
                    $sFile = $oFile->getPathname();
                }
                if ( $sFile ) {
                    break;
                }
            }
            $oFile->next();
        }

        return $sFile;
    }
}