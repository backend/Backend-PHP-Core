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

    public function __construct($content = '', $status = 200, $headers = null)
    {
        $this->_content = $content;
        $this->_status  = $status;
        $this->_headers = $headers;
    }

    public function content($content)
    {
        $this->_content[] = $content;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent($content)
    {
        $this->_content = $content;
    }

    public function header($name, $content)
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
        echo $this->_content;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();
    }

    public function __toString()
    {
        $this->send();
    }
}
