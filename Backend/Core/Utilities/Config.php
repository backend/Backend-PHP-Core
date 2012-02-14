<?php
namespace Backend\Core\Utilities;
/**
 * File defining Core\Utilities\Config
 *
 * Copyright (c) 2011 JadeIT cc
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR
 * A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package UtiltityFiles
 */
/**
 * Class to handle application configs
 *
 * @package Utiltities
 */
class Config
{
    /**
     * @var object Store for all the config values
     */
    protected $_values = null;

    /**
     * Construct the config class.
     *
     * @param string The name of the config file to use. Defaults to PROJECT_FOLDER . 'config/default.yaml'
     * @todo Allow passing an array of filesnames to parse. This will let you parse default as well as environment
     */
    public function __construct($filename = false)
    {
        $filename = $filename ? $filename : PROJECT_FOLDER . 'config/default.yaml';
        if (!file_exists($filename)) {
            throw new \Exception('Invalid Config File: ' . $filename);
        }
        if (!is_readable($filename)) {
            throw new \Exception('Unreadable Config File: ' . $filename);
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $info = pathinfo($filename);
        switch ($ext) {
        case 'json':
            $this->_values = json_decode(file_get_contents($filename), true);
            break;
        case 'yaml':
            if (function_exists('yaml_parse_file')) {
                $this->_values = \yaml_parse_file($filename);
            } else if (fopen('SymfonyComponents/YAML/sfYamlParser.php', 'r', true)) {
                include('SymfonyComponents/YAML/sfYamlParser.php');
                $yaml = new \sfYamlParser();
                $this->_values = $yaml->parse(file_get_contents($filename));
            }
        }
        if (is_null($this->_values)) {
            throw new \Exception('Could not parse Config File using extension ' . $ext);
        }
    }

    /**
     * Magic function that returns the config values on request
     */
    public function __get($propertyName)
    {
        if (array_key_exists($propertyName, $this->_values)) {
            return $this->_values[$propertyName];
        }
        return null;
    }

    /**
    * Get the named config value from the specified section.
    *
    * @param string The name of the config section
    * @param string The name of the config value
    * @return mixed The config setting
    */
    public function get($section = false, $name = false)
    {
        if ($section) {
            $section = $this->__get($section);
            if ($name && !is_null($section)) {
                if (array_key_exists($name, $section)) {
                    return $section[$name];
                } else {
                    return null;
                }
            }
            return $section;
        } else {
            return $this->_values;
        }
        return null;
    }
}
