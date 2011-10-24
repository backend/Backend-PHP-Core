<?php
/**
 * File defining BApplication
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
 * @package BaseFiles
 */
/**
 * The Base application class.
 *
 * @package Base
 */
class BApplication extends BEApplication
{
    protected function init()
    {
        if ($this->_initialized) {
            return true;
        }

        //PHP Helpers
        spl_autoload_register(array('BApplication', '__autoload'));

        $result = parent::init();

        return $result;
    }

    public static function __autoload($className, $base = 'base')
    {
        //Check for a Base class
        if ($base == 'base' && preg_match('/^B[A-Z][a-z].*/', $className)) {
            if (file_exists(BACKEND_FOLDER . '/base/' . $className . '.obj.php')) {
                include(BACKEND_FOLDER . '/base/' . $className . '.obj.php');
                return true;
            } else {
                throw new Exception('Missing Base Class: ' . $className);
            }
        }
        return parent::__autoload($className, $base);
    }
}
