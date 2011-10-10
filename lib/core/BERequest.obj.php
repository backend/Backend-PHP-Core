<?php
/**
 * File defining BERequest
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
 * @package CoreFiles
 */
/**
 * The Request class.
 *
 * @package Core
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
     * If no method is supplied, it's determined by one of the following:
     * * A _method POST variable
     * * A X_HTTP_METHOD_OVERRIDE header
     * * The REQUEST_METHOD
     *
     * @param array request The request data. Defaults to the HTTP request data
     * @param string method The request method. Can be one of GET, POST, PUT, DELETE or HEAD
     */
    function __construct(array $request = null, $method = null)
    {
        if (!$method) {
            //Copied the way to determine the method from CakePHP
            //http://book.cakephp.org/2.0/en/development/rest.html#the-simple-setup
            switch (true) {
            case array_key_exists('_method', $_POST):
                $method = $_POST['_method'];
                break;
            case array_key_exists('X_HTTP_METHOD_OVERRIDE', $_SERVER):
                $method = $_SERVER['X_HTTP_METHOD_OVERRIDE'];
                break;
            default:
                $method = strtoupper($_SERVER['REQUEST_METHOD']);
                break;
            }
        }
        if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE', 'HEAD'))) {
            throw new UnsupportedMethodException('Unsupported method ' . $method);
        }
        $this->_method  = $method;
        //Set the payload to request initially
        $this->_payload = empty($request) ? $_REQUEST : $request;
        foreach ($this->_payload as $key => $value) {
            if (empty($value) && !empty($key)) {
                    $this->_query   = $key;
                    unset($this->_payload[$key]);
                    break;
            }
        }
        //Clean up the query
        //Decode the URL
        $this->_query = urldecode($this->_query);
        //No trailing slash
        if (substr($this->_query, -1) == '/') {
            $this->_query = substr($this->_query, 0, strlen($this->_query) - 1);
        }

        $message = 'Request: ' . $this->getMethod() . ': ' . $this->getQuery();
        BEApplication::log($message, 4);
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
