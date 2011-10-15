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
     * @var string The format of the request
     */
    private $_format  = null;


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

        $this->_format = $this->determineFormat();

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
     * Determine the requested format for the request
     *
     * @return string The format for the request
     * @todo Find a way for Views to specify what formats they support
     */
    private function determineFormat()
    {
        //Check the query's extension
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
                //TODO Check extension against supported extensions
                $this->_query = preg_replace('/[_\.]' . $extension . '$/', '', $this->_query);
                return $extension;
            }
        }

        //Third CL parameter is the required format
        if (from_cli() && count($_SERVER['argv']) >= 4) {
            return $_SERVER['argv'][3];
        }

        //Check the format parameter
        if (array_key_exists('format', $this->_payload)) {
            return $this->_payload['format'];
        }

        //No format found, check if there's an Accept Header
        if (array_key_exists('HTTP_ACCEPT_HEADER', $_SERVER)) {
            switch ($_SERVER['HTTP_ACCEPT_HEADER']) {
            case 'text/html':
            case 'application/xhtml+xml':
                return 'html';
                break;
            case 'application/xml':
                return 'xml';
                break;
            case 'text/plain':
                return 'plain';
                break;
            case 'text/json':
                return 'json';
                break;
            case 'text/css':
                return 'css';
                break;
            default:
                //Simple Accept header not sent, parse it further
                //TODO Not implementing this, as it requires a complicated workaround to work in IE
                break;
            }
        }

        //We got nothing. Use cli format for cl requests, html for web
        if (from_cli()) {
            return 'cli';
        } else {
            return 'html';
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

    /**
     * Return the request's format.
     *
     * @return string The Request Format
     */
    public function getFormat()
    {
        return $this->_format;
    }
}
