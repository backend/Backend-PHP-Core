<?php
namespace Backend\Core\Utilities;
/**
 * File defining ViewFactory
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
 * Factory class to create Views
 *
 * @package Utility
 */
class ViewFactory
{
    /**
     * Build a view with the supplied (or current) request
     *
     * @param Core\Request The Request to use to determine the view
     * @return Core\View The view that can handle the Request
     */
    public static function build(\Backend\Core\Request $request)
    {

        //Check the View Folder
        $request = is_null($request) ? new \Backend\Core\Request() : $request;

        //Loop through all the available views
        $namespaces = array_reverse(\Backend\Core\Application::getNamespaces());
        foreach ($namespaces as $base) {
            $viewFolder = BACKEND_FOLDER . $base . '/Views/';
            if (
                !file_exists($viewFolder)
                || !($handle = opendir($viewFolder))
            ) {
                continue;
            }
            while (false !== ($file = readdir($handle))) {
                if ($file == '.' || $file == '..' || substr($file, -4) != '.php') {
                    continue;
                }

                //Check the view class
                $viewName = '\Backend\\' . $base . '\Views\\' . substr($file, 0, strlen($file) - 4);
                if (!class_exists($viewName, true)) {
                    continue;
                }

                //Check if the view can handle the request
                if (self::checkView($viewName, $request)) {
                    $view = new $viewName();
                    if (!($view instanceof \Core\View)) {
                        throw new \Backend\Core\Exceptions\UnknownViewException('Invalid View: ' . get_class($view));
                    }
                    return $view;
                }
            }
        }
        throw new \Backend\Core\Exceptions\UnknownViewException('Unrecognized Format');
        return false;
    }

    /**
     * Check the View against the supplied request
     *
     * This was originally implemented in Core\View, but issues with static variables
     * and inheritance prevented it from working properly. Non static properties could
     * not be used, as we do not want to construct each view.
     */
    private static function checkView($viewName, $request)
    {
        if ($format = $request->getSpecifiedFormat()) {
            if (in_array($format, $viewName::$handledFormats)) {
                return true;
            }
            return false;
        }
        if ($extension = $request->getExtension()) {
            if (in_array($extension, $viewName::$handledFormats)) {
                return true;
            }
        }
        if ($mimeType = $request->getMimeType()) {
            if (in_array($mimeType, $viewName::$handledFormats)) {
                return true;
            }
        }
        return false;
    }
}
