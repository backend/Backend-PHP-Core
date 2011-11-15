<?php
namespace Backend\Core\Decorators;
/**
 * File defining Core\Decorators\PrettyExceptionDecorator
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
 * @package DecoratorFiles
 */
/**
 * Abstract base class for Model decorators
 *
 * @package Decorators
 */
class PrettyExceptionDecorator extends \Exception
{
    protected $_exception;

    /**
     * The constructor for the class
     *
     * @param Exception The exception to decorate
     */
    function __construct(\Exception $exception, $message = null, $code = 0)
    {
        $this->_exception = $exception;
        parent::__construct($message, $code);
    }

    /**
     * Return the exception as a string
     *
     * @return string The exception as a string
     * @todo Use the kohana code to format the exception properly.
     * * /kohana/system/classes/kohana/kohana/exception.php
     * * /kohana/system/views/kohana/error.php
     */
    public function __toString()
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            get_class($this->_exception),
            $this->_exception->getCode(),
            strip_tags($this->_exception->getMessage()),
            $this->_exception->getFile(),
            $this->_exception->getLine()
        );
    }
}
