<?php
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
 * The main application class.
 *
 * The application will / should be the only singleton in the framework, acting as
 * a Toolbox. That means that any resource that should be globally accessable (and
 * some times a singleton) should be passed to the Application. Read more at
 * http://www.ibm.com/developerworks/webservices/library/co-single/index.html#h3
 *
 * @package Utility
 */
class ViewFactory
{
    /**
     * Build a view with the supplied (or current) request
     *
     * @param BERequest The Request to use to determine the view
     * @return BEView The view that can handle the Request
     */
    public static function build(BERequest $request = null)
    {
        $viewFolder = BACKEND_FOLDER . '/views/';

        //Check the View Folder
        $request = is_null($request) ? new BERequest() : $request;
        if (
            !file_exists($viewFolder)
            || !($handle = opendir($viewFolder))
        ) {
            throw new Exception('Cannot open View Folder: ' . $viewFolder);
        }

        //Loop through all the available views
        while (false !== ($file = readdir($handle))) {
            if ($file == '.' || $file == '..' || substr($file, -9) != '.view.php') {
                continue;
            }

            //Check the view class
            $viewName = substr($file, 0, strlen($file) - 9);
            if (!class_exists($viewName, true) || !($viewName instanceof BEView)) {
                continue;
            }

            //Check if the view can handle the request
            if (call_user_func(array($viewName, 'handleRequest'), $request)) {
                return new $viewName();
            }
        }
        throw new UnknownViewException('Unrecognized Format: ' . $request->getFormat());
        return false;
    }
}
