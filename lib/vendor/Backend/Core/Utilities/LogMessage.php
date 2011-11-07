<?php
namespace Backend\Core\Utilities;
/**
 * File defining LogMessage
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
 * @package UtilityFiles
 */
/**
 * LogMessage
 *
 * Log levels:
 * 1. Critical Messages
 * 2. Warning | Alert Messages
 * 3. Important Messages
 * 4. Debugging Messages
 * 5. Informative Messages
 * @package Utilities
 */
class LogMessage implements \SplSubject
{
    const LEVEL_CRITICAL    = 1;
    const LEVEL_WARNING     = 2;
    const LEVEL_IMPORTANT   = 3;
    const LEVEL_DEBUGGING   = 4;
    const LEVEL_INFORMATION = 5;

    protected $_observers = array();

    protected $_level;

    protected $_message;

    public function __construct($message, $level)
    {
        $this->_message = $message;

        $this->_level   = $level;

        Observable::execute($this);

        $this->notify();
    }

    /**
     * Accessor for message
     *
     * @return string The contents of the message
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Accessor for level
     *
     * @return int The level of the message
     */
    public function getLevel()
    {
        return $this->_level;
    }

    //SplSubject functions

    public function attach(\SplObserver $observer)
    {
        $id = spl_object_hash($observer);
        $this->_observers[$id] = $observer;
    }

    public function detach(\SplObserver $observer)
    {
        $id = spl_object_hash($observer);
        unset($this->_observers[$id]);
    }

    public function notify()
    {
        foreach ($this->_observers as $obs) {
            $obs->update($this);
        }
    }

    function __toString()
    {
        return $this->_message;
    }
}
