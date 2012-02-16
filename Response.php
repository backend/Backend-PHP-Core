<?php
namespace Backend\Core;
/**
 * File defining Response
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
 * The response that will be sent back to the client
 *
 * @package Core
 */
class Response
{
    /**
     * @var array An array containing response components
     */
    protected $_content = array();

    /**
     * @var int The HTTP response code
     */
    protected $_status  = 200;
    
    /**
     * @var string The HTTP version
     */
    protected $_http_version = 'HTTP/1.1';

    /**
     * @var array A list of HTTP Response Codes with their default texts
     *
     * Copied from the Zend_Http_Response object
     */
    protected static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    
    /**
     * @var array An associative array containing headers to be sent along with the response
     */
    protected $_headers = array();

    public function __construct($content = array(), $status = 200, array $headers = array())
    {
        $this->_content = $content;
        $this->_status  = $status;
        $this->_headers = $headers;
    }

    public function getStatusCode()
    {
        return $this->_status;
    }

    public function setStatusCode($code)
    {
        $this->_status = $code;
    }

    public function addContent($content, $append = false)
    {
        if ($append) {
            array_unshift($this->_content, $content);
        } else {
            $this->_content[] = $content;
        }
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent(array $content)
    {
        $this->_content = $content;
    }

    public function addHeader($name, $content)
    {
        $this->_headers[$name] = $content;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
    }

    public function output()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    public function sendHeaders()
    {
        if (headers_sent($file, $line)) {
            throw new \Exception('Headers already sent in ' . $file . ', line ' . $line);
        }
        //Always send the HTTP status header first
        header($this->_http_version . ' ' . $this->_status . ' ' . $this->getStatusText($this->_status));
        if (!array_key_exists('X-Application', $this->_headers)) {
            header('X-Application: Backend-PHP (Core)');
        }
        foreach ($this->_headers as $name => $content) {
            header($name . ': ' . $content);
        }
    }

    public function sendContent()
    {
        echo implode(PHP_EOL, $this->_content);
    }
    
    public function getStatusText($code)
    {
        if (array_key_exists($code, self::$messages)) {
            return self::$messages[$code];
        }
        return 'Unknown Status';
    }

    public function __toString()
    {
        ob_start();
        $this->sendContent();
        return ob_get_clean();
    }
}
