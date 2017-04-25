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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Integration\Library;

class TestCaseParser
{

    /**
     * Directory where to search for test cases
     *
     * @var string
     */
    protected $_sDirectory = null;

    /**
     * Test cases filter. Specify names of cases which should be run
     *
     * @var array
     */
    protected $_aTestCases = array();

    /** @var string  */
    protected $_sTestCasesPath = '';

    /**
     * Array of replacements for test values.
     *
     * @var array
     */
    protected $_aReplacements = array();

    /**
     * Sets directory to search for test cases
     *
     * @param string $sDirectory
     */
    public function setDirectory($sDirectory)
    {
        $this->_sDirectory = $sDirectory;
    }

    /**
     * Returns directory to search for test cases
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->_sDirectory;
    }

    /**
     * Sets test cases to be run
     *
     * @param array $aTestCases
     */
    public function setTestCases($aTestCases)
    {
        $this->_aTestCases = $aTestCases;
    }

    /**
     * Returns test cases to be run
     *
     * @return array
     */
    public function getTestCases()
    {
        return $this->_aTestCases;
    }

    /**
     * Sets Replacement
     *
     * @param array $aReplacements
     */
    public function setReplacements($aReplacements)
    {
        $this->_aReplacements = $aReplacements;
    }

    /**
     * Returns Replacement
     *
     * @return array
     */
    public function getReplacements()
    {
        return $this->_aReplacements;
    }

    /**
     * Getting test cases from specified
     *
     * @return array
     */
    public function getData()
    {
        $aTestCasesData = array();
        $sDirectory = $this->getDirectory();
        $aTestCases = $this->getTestCases();

        $aFiles = $this->_getDirectoryTestCasesFiles($sDirectory, $aTestCases);
        print(count($aFiles) . " test files found\r\n");
        foreach ($aFiles as $sFilename) {
            $aData = $this->_getDataFromFile($sFilename);
            $aData = $this->_parseTestData($aData);
            $aTestCasesData[$sFilename] = array($aData);
        }

        return $aTestCasesData;
    }

    /**
     * Returns directory files. If test cases is passed - only those files is checked in given directory
     *
     * @param $sDir
     * @param $aTestCases
     *
     * @return array
     */
    protected function _getDirectoryTestCasesFiles($sDir, $aTestCases)
    {
        $sPath = $this->_sTestCasesPath . $sDir . "/";
        print("Scanning dir {$sPath}\r\n");
        $aFiles = array();
        if (empty($aTestCases)) {
            $aFiles = $this->_getFilesInDirectory($sPath);
        } else {
            foreach ($aTestCases as $sTestCase) {
                $aFiles[] = $sPath . $sTestCase;
            }
        }

        return $aFiles;
    }

    /**
     * Returns php files list from given directory and all subdirectories
     *
     * @param string $sPath
     *
     * @return array
     */
    private function _getFilesInDirectory($sPath)
    {
        $aFiles = array();
        foreach (new \DirectoryIterator($sPath) as $oFile) {
            if ($oFile->isDir() && !$oFile->isDot()) {
                $aFiles = array_merge($aFiles, $this->_getFilesInDirectory($oFile->getPathname()));
            }
            if ($oFile->isFile() && preg_match('/\.php$/', $oFile->getFilename())) {
                $aFiles[] = $oFile->getPathname();
            }
        }

        return $aFiles;
    }

    /**
     * Loads data from file
     *
     * @param string $sFilename
     *
     * @return array
     */
    protected function _getDataFromFile($sFilename)
    {
        $aData = array();
        include($sFilename);

        return $aData;
    }

    /**
     * Parses given data
     *
     * @param array $aData
     *
     * @return array
     */
    protected function _parseTestData($aData)
    {
        foreach ($aData as &$mValue) {
            $mValue = $this->_parseTestValue($mValue);
        }

        return $aData;
    }

    /**
     * Parses given test case value
     *
     * @param $mValue
     *
     * @return array
     */
    protected function _parseTestValue($mValue)
    {
        if (is_array($mValue)) {
            return $this->_parseTestData($mValue);
        }
        if (is_string($mValue)) {
            $aReplacements = $this->getReplacements();
            $mValue = str_replace(array_keys($aReplacements), $aReplacements, $mValue);
        }

        return $mValue;
    }
}