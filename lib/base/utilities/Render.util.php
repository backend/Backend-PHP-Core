<?php
/**
 * File defining Render
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
 * @package UtilityFiles
 */
/**
 * Render templates
 *
 * @package Utility
 */
class Render
{
    /**
     * @var CoreView The view used to render
     */
    private $_view;

    function __construct(CoreView $view)
    {
        $this->_view = $view;
    }

    /**
     * Render the specified file
     *
     * @param string The name of the template
     * @param array Extra variables to consider
     * @return string The contents of the rendered template
     */
    public function file($template, array $values = array())
    {
        $file = $this->templateFile($template);
        if (!$file) {
            //TODO Throw an exception, make a fuss?
            CoreApplication::log('Missing Template: ' . $template, 4);
            return false;
        }

        //TODO Add Caching

        ob_start();
        include($file);
        $result = ob_get_clean();

        //Substitute Variables into the templates
        $result = $this->parseVariables($result, $values);

        return $result;
    }

    /**
     * Get the file name for the specified template
     *
     * @param string The name of the template
     * @return string The absolute path to the template file to render
     */
    protected function templateFile($template)
    {
        if (substr($template, -4) != '.php') {
            if (substr($template, -4 != '.tpl')) {
                $template .= '.tpl';
            }
            $template .= '.php';
        }
        $locations = array();
        if (!empty($this->_view->templateLocations) && is_array($this->_view->templateLocations)) {
            $locations = array_unique(array_merge($locations, $this->_view->templateLocations));
        }
        foreach ($locations as $location) {
            if (file_exists($location . '/' . $template)) {
                return $location . '/' . $template;
            }
        }
        return false;
    }

    /**
     * Check the string for variables (#VarName#) and replace them with the appropriate values
     *
     * The values currently bound to the view will be used.
     *
     * @param string The string to check for variable names
     * @param array Extra variables to consider
     * @return string The string with the variables replaced
     */
    function parseVariables($string, array $values = array())
    {
        $values = array_merge($this->_view->getVariables(), $values);
        foreach ($values as $name => $value) {
            if (is_string($name) && is_string($value)) {
                $search[] = '#' . $name . '#';
                $replace[] = $value;
            }
        }
        $string = str_replace($search, $replace, $string);
        return $string;
    }
}
