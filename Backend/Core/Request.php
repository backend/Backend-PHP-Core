<?php
namespace Backend\Core;
/**
 * File defining Core\Request
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
 * The Request class which helps determine the Query string and request format.
 *
 * @package Core
 */
class Request
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
     * The constructor for the class
     *
     * If no method is supplied, it's determined by one of the following:
     * 1. A _method POST variable
     * 2. A X_HTTP_METHOD_OVERRIDE header
     * 3. The REQUEST_METHOD
     *
     * @param mixed The request data. Defaults to the HTTP request data if not supplied
     * @param string The request method. Can be one of GET, POST, PUT, DELETE or HEAD
     */
    function __construct($request = null, $method = null)
    {
        if (!is_null($method)) {
            $this->setMethod($method);
        }

        if (is_null($request)) {
            $payload = $this->getPayload();
        } else if (is_string($request)) {
            $payload = parse_str($request);
        } else if (is_array($request)) {
            $payload = $request;
        } else if (is_object($request)) {
            $payload = (array)$request;
        }
        $this->setPayload($payload);

        //Get the query
        $query = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

        //Clean up the query
        $this->_query = $this->cleanupQuery($query);

        $message = 'Request: ' . $this->getMethod() . ': ' . $this->getQuery();
        Application::log($message, 4);
    }

    private function cleanupQuery($query)
    {
        //Decode the URL
        $query = urldecode($query);
        //No trailing slash
        if (substr($query, -1) == '/') {
            $query = substr($query, 0, strlen($query) - 1);
        }
        return $query;
    }

    /**
     * Determine the requested format for the request
     *
     * @return string The format for the request
     */
    public function getSpecifiedFormat()
    {
        //Third CL parameter is the required format
        if (self::fromCli() && count($_SERVER['argv']) >= 4) {
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
        if (self::fromCli()) {
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
        if (!is_null($this->_method)) {
            return $this->_method;
        }
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
            if (self::fromCli()) {
                //First CL parameter is the method
                $method = count($_SERVER['argv']) >= 2 ? $_SERVER['argv'][1] : 'GET';
            } else {
                $method = $_SERVER['REQUEST_METHOD'];
            }
            break;
        }
        $this->setMethod($method);
        return $this->_method;
    }

    /**
     * Set the request's method
     *
     * @param string The Request Method
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, array('DELETE', 'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT'))) {
            throw new Exceptions\UnsupportedHttpMethodException('Unsupported method ' . $method);
        }
        $this->_method = $method;
    }

    /**
     * Utility function to check if the current method equals the specified method
     *
     * @param string The method to check
     * @return boolean If the current method equals the specified method
     */
    private function isMethod($method)
    {
        return strtoupper($method) == $this->_method;
    }

    /**
     * Check if the current request is a DELETE request
     *
     * @return boolean If the current request is a DELETE request
     */
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Check if the current request is a GET request
     *
     * @return boolean If the current request is a GET request
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if the current request is a HEAD request
     *
     * @return boolean If the current request is a HEAD request
     */
    public function isHead()
    {
        return $this->isMethod('HEAD');
    }

    /**
     * Check if the current request is a OPTIONS request
     *
     * @return boolean If the current request is a OPTIONS request
     */
    public function isOptions()
    {
        return $this->isMethod('OPTIONS');
    }

    /**
     * Check if the current request is a POST request
     *
     * @return boolean If the current request is a POST request
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * Check if the current request is a PUT request
     *
     * @return boolean If the current request is a PUT request
     */
    public function isPut()
    {
        return $this->isMethod('PUT');
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
        if (!is_null($this->_payload)) {
            return $this->_payload;
        }
        if (self::fromCli()) {
            $payload = array(
                //Second CL parameter is the query. This will be picked up later
                count($_SERVER['argv']) >= 3 ? $_SERVER['argv'][2] : '' => '',
            );
            if (count($_SERVER['argv']) >= 5) {
                //Fourth CL parameter is a query string
                parse_str($_SERVER['argv'][4], $queryVars);
                if (is_array($queryVars)) {
                    $payload = array_merge($this->_payload, $queryVars);
                }
            }
        }
        if (!isset($payload)) {
            $payload = isset($_REQUEST) ? $_REQUEST : array();
        }
        $this->setPayload($payload);
        return $this->_payload;
    }

    /**
     * Set the request's payload.
     *
     * @param array The Request's Payload
     */
    public function setPayload(array $payload)
    {
        $this->_payload = $payload;
    }

    /**
     * Check if this requests originates from a CLI.
     *
     * @return boolean If this is a CLI request
     */
    public static function fromCli()
    {
        return !array_key_exists('REQUEST_METHOD', $_SERVER) && array_key_exists('argv', $_SERVER);
    }
}
