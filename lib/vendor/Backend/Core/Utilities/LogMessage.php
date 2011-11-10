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
    /**
     * @var int Log level for Critical Messages
     */
    const LEVEL_CRITICAL    = 1;

    /**
     * @var int Log level for Warning or Alert Messages
     */
    const LEVEL_WARNING     = 2;

    /**
     * @var int Log level for Important Messages
     */
    const LEVEL_IMPORTANT   = 3;

    /**
     * @var int Log level for Debugging Messages
     */
    const LEVEL_DEBUGGING   = 4;

    /**
     * @var int Log level for Informational Messages
     */
    const LEVEL_INFORMATION = 5;

    /**
     * @var array A set of observers for the log message
     */
    protected $_observers = array();

    /**
     * @var string The log message level
     */
    protected $_level;

    /**
     * @var string The log message
     */
    protected $_message;

    /**
     * Constructor for the class
     *
     * @param string The log message
     * @param string The log message level
     */
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
    /**
     * Attach an observer to the class
     *
     * @param SplObserver The observer to attach
     */
    public function attach(\SplObserver $observer)
    {
        $id = spl_object_hash($observer);
        $this->_observers[$id] = $observer;
    }

    /**
     * Detach an observer from the class
     *
     * @param SplObserver The observer to detach
     */
    public function detach(\SplObserver $observer)
    {
        $id = spl_object_hash($observer);
        unset($this->_observers[$id]);
    }

    /**
     * Notify observers of an update to the class
     */
    public function notify()
    {
        foreach ($this->_observers as $obs) {
            $obs->update($this);
        }
    }

    /**
     * Return a string representation of the class
     */
    function __toString()
    {
        return $this->_message;
    }
}
