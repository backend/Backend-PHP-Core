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
        foreach ($this->_headers as $name => $content) {
            header($name . ': ' . $content);
        }
    }

    public function sendContent()
    {
        echo implode(PHP_EOL, $this->_content);
    }

    public function __toString()
    {
        ob_start();
        $this->sendContent();
        return ob_get_clean();
    }
}
