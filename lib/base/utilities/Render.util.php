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
     * @var BEView The view used to render
     */
    private $_view;

    function __construct(BEView $view)
    {
        $this->_view = $view;
    }

    /**
     * Render the specified file
     *
     * @param string The name of the template
     * @return string The contents of the rendered template
     */
    public function file($template)
    {
        $file = $this->templateFile($template);
        if (!$file) {
            //TODO Throw an exception, make a fuss?
            BEApplication::log('Missing Template: ' . $template, 4);
            return false;
        }
        ob_start();
        include($file);
        return ob_get_clean();
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
}
