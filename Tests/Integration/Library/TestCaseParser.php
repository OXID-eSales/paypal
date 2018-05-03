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
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->_sDirectory = $directory;
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
     * @param array $testCases
     */
    public function setTestCases($testCases)
    {
        $this->_aTestCases = $testCases;
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
     * @param array $replacements
     */
    public function setReplacements($replacements)
    {
        $this->_aReplacements = $replacements;
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
        $testCasesData = array();
        $directory = $this->getDirectory();
        $testCases = $this->getTestCases();

        $files = $this->getDirectoryTestCasesFiles($directory, $testCases);
        print(count($files) . " test files found\r\n");
        foreach ($files as $filename) {
            $data = $this->getDataFromFile($filename);
            $data = $this->parseTestData($data);
            $testCasesData[$filename] = array($data);
        }

        return $testCasesData;
    }

    /**
     * Returns directory files. If test cases is passed - only those files is checked in given directory
     *
     * @param $dir
     * @param $testCases
     *
     * @return array
     */
    protected function getDirectoryTestCasesFiles($dir, $testCases)
    {
        $path = $this->_sTestCasesPath . $dir . "/";
        print("Scanning dir {$path}\r\n");
        $files = array();
        if (empty($testCases)) {
            $files = $this->getFilesInDirectory($path);
        } else {
            foreach ($testCases as $testCase) {
                $files[] = $path . $testCase;
            }
        }

        return $files;
    }

    /**
     * Returns php files list from given directory and all subdirectories
     *
     * @param string $path
     *
     * @return array
     */
    private function getFilesInDirectory($path)
    {
        $files = array();
        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDir() && !$file->isDot()) {
                $files = array_merge($files, $this->getFilesInDirectory($file->getPathname()));
            }
            if ($file->isFile() && preg_match('/\.php$/', $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Loads data from file
     *
     * @param string $filename
     *
     * @return array
     */
    protected function getDataFromFile($filename)
    {
        $data = array();
        include($filename);

        return $data;
    }

    /**
     * Parses given data
     *
     * @param array $data
     *
     * @return array
     */
    protected function parseTestData($data)
    {
        foreach ($data as &$value) {
            $value = $this->parseTestValue($value);
        }

        return $data;
    }

    /**
     * Parses given test case value
     *
     * @param $value
     *
     * @return array
     */
    protected function parseTestValue($value)
    {
        if (is_array($value)) {
            return $this->parseTestData($value);
        }
        if (is_string($value)) {
            $replacements = $this->getReplacements();
            $value = str_replace(array_keys($replacements), $replacements, $value);
        }

        return $value;
    }
}