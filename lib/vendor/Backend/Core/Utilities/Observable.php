<?php
namespace Backend\Core\Utilities;
/**
 * File defining Observable
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
 * Class to automatically attach observers to subjects
 *
 * @package Utilities
 */
class Observable
{
    /**
     * Check for observers for the given subject
     *
     * An observer is only eligible if it implements \SplObserver, and can be retrieved
     * using \Backend\Core\getTool
     */
    public static function execute(\SplSubject $subject)
    {
        $config = \Backend\Core\Application::getTool('Config');
        if (!$config) {
            return false;
        }
        //Attach Observers to Subjects
        $config = $config->get('subjects', get_class($subject));
        if (!empty($config['observers'])) {
            foreach ($config['observers'] as $observerName) {
                $observer = \Backend\Core\Application::getTool($observerName);
                if ($observer instanceof \SplObserver) {
                    $subject->attach($observer);
                }
            }
        }
        return true;
    }
}
