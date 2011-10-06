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
 * The Request class.
 *
 * @package BECore
 */
class BERequest
{
    /**
     * @var string The query of the request
     */
    private $_query   = null;
    /**
     * @var array The payload of the request
     */
    private $_payload = null;
    /**
     * @var string The method of the request. Can be one of GET, POST, PUT or DELETE
     */
    private $_method  = null;


    /**
     * The class constructor
     *
     * @param array request The request data. Defaults to the HTTP request data
     * @param string method The request method. Can be one of GET, POST, PUT or DELETE
     */
    function __construct(array $request = null, $method = null)
    {
        if (!$method) {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
        }
        $this->_method  = $method;
        //Set the payload to request initially
        $this->_payload = empty($request) ? $_REQUEST : $request;
        foreach($this->_payload as $key => $value) {
            if (empty($value) && !empty($key)) {
                    $this->_query   = $key;
                    unset($this->_payload[$key]);
                    break;
            }
        }
    }

    /**
     * Return the request's method.
     *
     * @return string The Request Method
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Return the request's query.
     *
     * @return string The Request Query
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Return the request's payload.
     *
     * @return string The Request Payload
     */
    public function getPayload()
    {
        return $this->_payload;
    }

}
