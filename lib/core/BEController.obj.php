<?php
/**
 * Copyright (c) 2011 JadeIT cc
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
 * @package BECoreFiles
 */
/**
 * The main controller class.
 *
 * @package BECore
 */
class BEController
{
    /**
     * This contains the request object that we're currently serving
     * @var BERequest
     */
    private $_request = null;
    /**
     * This contains the router object that will help decide what model and action to execute
     * @var BERouter
     */
    private $_router = null;

    /**
     * The class constructor
     *
     * @param BERequest request A request object to serve
     */
    function __construct(BERequest $request = null)
    {
        $this->_request = $request ? $request : new BERequest();

        try {
            $this->_router = new BERouter($this->_request);

            //Get and check the model
            $model = $this->_router->model;
            //TODO Check

            //Get and check the method
            $method = $this->_router->method;
            if (!is_callable(array($model, $method))) {
                throw new UncallableMethodException($model, $method);
            }

        } catch (Exception $e) {
            //TODO Get the Error Model, and execute
            //TODO Handle UknownRouteException
        }

    }
}
