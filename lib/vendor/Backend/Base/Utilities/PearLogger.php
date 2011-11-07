<?php
namespace Backend\Base\Utilities;
/**
 * File defining PearLogger
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
 * A Logging Observer using the PEAR::Log class
 *
 * @package Utilities
 */
require_once('Log.php');
class PearLogger implements \Backend\Core\Interfaces\LoggingObserver
{
    /**
     * @var Log The instance of the PEAR Log class we'll use to log
     */
    protected $_logger;

    /**
     * Constructor
     *
     * @param mixed An array of options for the logger, or a string containing a filename to log to
     */
    public function __construct($options = array())
    {
        if (is_string($options)) {
            $options = array('filename' => $options);
        }
        if (array_key_exists('filename', $options)) {
            if (!array_key_exists('prepend', $options)) {
                $options['prepend'] = 'BackendCore';
            }
            $this->_logger = \Log::factory('file', $options['filename'], $options['prepend']);
        }
    }

    /**
     * Update method called by subjects being observed
     *
     * @param SplSubject The subject, which should be a LogMessage
     */
    public function update(\SplSubject $message)
    {
        if (!$this->_logger) {
            return false;
        }
        if (!($message instanceof LogMessage)) {
            return false;
        }
        switch ($message->getLevel()) {
        case $message::LEVEL_CRITICAL:
            $level = \PEAR_LOG_EMERG;
            break;
        case $message::LEVEL_WARNING:
            $level = \PEAR_LOG_CRIT;
            break;
        case $message::LEVEL_IMPORTANT:
            $level = \PEAR_LOG_WARNING;
            break;
        case $message::LEVEL_DEBUGGING:
            $level = \PEAR_LOG_DEBUG;
            break;
        case $message::LEVEL_INFORMATION:
            $level = \PEAR_LOG_INFO;
            break;
        default:
            $level = $message->getLevel();
            break;
        }
        $this->_logger->log($message->getMessage(), $level);
    }
}
