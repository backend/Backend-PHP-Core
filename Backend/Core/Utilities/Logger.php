<?php
namespace Backend\Core\Utilities;
/**
 * File defining Logger
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
 * A Basic Logging Observer
 *
 * @package Utilities
 */
class Logger implements \Backend\Core\Interfaces\LoggingObserver
{
    /**
     * Function to receive an update from a subject
     *
     * @param SplSubject The message to log
     */
    public function update(\SplSubject $message)
    {
        if (!($message instanceof LogMessage)) {
            return false;
        }
        switch ($message->getLevel()) {
        case LogMessage::LEVEL_CRITICAL:
            $message = ' (CRITICAL) ' . $message;
            break;
        case LogMessage::LEVEL_WARNING:
            $message = ' (WARNING) ' . $message;
            break;
        case LogMessage::LEVEL_IMPORTANT:
            $message = ' (IMPORTANT) ' . $message;
            break;
        case LogMessage::LEVEL_DEBUGGING:
            $message = ' (DEBUG) ' . $message;
            break;
        case LogMessage::LEVEL_IMPORTANT:
            $message = ' (INFORMATION) ' . $message;
            break;
        default:
            $message = ' (OTHER - ' . $level . ') ' . $message;
            break;
        }

        $message = date('Y-m-d H:i:s ') . $message;
        echo $message . '<br>' . PHP_EOL;
    }
}
