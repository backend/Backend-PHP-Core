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
     * @var string The extension of the request.
     */
    private $_extension  = null;

    /**
     * The class constructor
     *
     * If no method is supplied, it's determined by one of the following:
     * 1. A _method POST variable
     * 2. A X_HTTP_METHOD_OVERRIDE header
     * 3. The REQUEST_METHOD
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
                if (from_cli()) {
                    //First CL parameter is the method
                    $method = count($_SERVER['argv']) >= 2 ? $_SERVER['argv'][1] : 'GET';
                } else {
                    $method = strtoupper($_SERVER['REQUEST_METHOD']);
                }
                break;
            }
        }
        if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE', 'HEAD'))) {
            throw new UnsupportedMethodException('Unsupported method ' . $method);
        }
        $this->_method  = $method;
        //Set the payload to request initially
        if (empty($request)) {
            if (from_cli()) {
                $this->_payload = array(
                    //Second CL parameter is the query. This will be picked up later
                    count($_SERVER['argv']) >= 3 ? $_SERVER['argv'][2] : '' => '',
                );
                if (count($_SERVER['argv']) >= 5) {
                    //Fourth CL parameter is a query string
                    parse_str($_SERVER['argv'][4], $queryVars);
                    if (is_array($queryVars)) {
                        $this->_payload = array_merge($this->_payload, $queryVars);
                    }
                }
            } else {
                $this->_payload = $_REQUEST;
            }
        } else {
            $this->_payload = $request;
        }

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
        $this->_extension = $this->getExtension();

        $message = 'Request: ' . $this->getMethod() . ': ' . $this->getQuery();
        BEApplication::log($message, 4);
    }

    /**
     * Determine the requested format for the request
     *
     * @return string The format for the request
     */
    public function getSpecifiedFormat()
    {
        //Third CL parameter is the required format
        if (from_cli() && count($_SERVER['argv']) >= 4) {
            return $_SERVER['argv'][3];
        }

        //Check the format parameter
        if (array_key_exists('format', $this->_payload)) {
            return $this->_payload['format'];
        }
        return false;
    }

    /**
     * Determine the extension for the request
     *
     * @return string The extension for the request
     */
    public function getExtension()
    {
        if (!is_null($this->_extension)) {
            return $this->_extension;
        }
        $parts = preg_split('/[_\.]/', $this->_query);
        if (count($parts) > 1) {
            $extension = end($parts);
            //Check if it's a valid .extension
            if (array_key_exists('QUERY_STRING', $_SERVER)) {
                $test = preg_replace('/[_\.]' . $extension . '$/', '.' . $extension, $this->_query);
                if (strpos($_SERVER['QUERY_STRING'], $test) === false) {
                    $extension = false;
                }
            }
            if ($extension) {
                $this->_query = preg_replace('/[_\.]' . $extension . '$/', '', $this->_query);
                return $extension;
            }
        }
        return false;
    }

    /**
     * Determine the requested MIME Type for the request
     *
     * @return string The MIME Type for the request
     */
    public function getMimeType()
    {
        if (from_cli()) {
            return 'cli';
        } else if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
            //No format found, check if there's an Accept Header
            $mimeType = $_SERVER['HTTP_ACCEPT'];
            //Try to get the first type
            $types = explode(',', $mimeType);
            //Remove the preference variable
            $mimeType = explode(';', reset($types));
            return reset($mimeType);
        }
        return false;
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
     * @return array The Request Payload
     */
    public function getPayload()
    {
        return $this->_payload;
    }
}
