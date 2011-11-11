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
     * @param Request The Request to use to determine the view
     * @return View The view that can handle the Request
     */
    public static function build(\Backend\Core\Request $request)
    {

        //Check the View Folder
        $views = array();
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

                $views[] = $viewName;
            }
        }

        $formats = array_filter(
            array(
                $request->getSpecifiedFormat(),
                $request->getExtension(),
                $request->getMimeType(),
            )
        );

        foreach ($formats as $format) {
            foreach ($views as $viewName) {
                if ($view = self::checkView($viewName, $format)) {
                    return $view;
                }
            }
        }

        throw new \Backend\Core\Exceptions\UnrecognizedRequestException('Unrecognized Format');
        return false;
    }

    private static function checkView($viewName, $format)
    {
        if (in_array($format, $viewName::$handledFormats)) {
            $renderer = \Backend\Core\Application::getTool('Renderer');
            $view = new $viewName($renderer);
            if (!($view instanceof \Backend\Core\View)) {
                throw new \Backend\Core\Exceptions\UnknownViewException('Invalid View: ' . get_class($view));
            }
            return $view;
        }
        return false;
    }
}
