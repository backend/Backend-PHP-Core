<?php
namespace Backend\Core;
/**
 * File defining Autoloader
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
 * The main autoloader.
 *
 * @package Core
 */
class Autoloader
{
    /**
     * Register the autload function
     */
    public static function register()
    {
        //Prepend the master autoload function to the beginning of the stack
        spl_autoload_register(array('\Backend\Core\Autoloader', '__autoload'), true, true);
    }

    /**
     * Function to autoload Backend-CoreFiles classes
     *
     * Register this function for use by calling \Backend\Core\Autoloader::register()
     * @param string The class name to auto load
     * @return boolean If the class file was found and included
     */
    public static function __autoload($className)
    {
        //Application::log('Checking for ' . $className, 5);

        $className = ltrim($className, '\\');
        $parts  = explode('\\', $className);
        $vendor = false;
        $base   = false;
        if (count($parts) > 1) {
            $vendor = $parts[0];
            if (count($parts) > 2) {
                $base = $parts[1];
            }
        }
        if ($vendor && $vendor == 'Backend' && self::loadBackendClass($className)) {
            return true;
        }

        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (file_exists(VENDOR_FOLDER . $fileName)) {
            require_once(VENDOR_FOLDER . $fileName);
            return true;
        } else {
            return false;
        }
        return false;
    }

    /**
     * Load backend specific classes
     *
     * @param string The class name to be loaded
     * @return boolean If a class was loeded or not
     */
    private static function loadBackendClass($className)
    {
        if (!class_exists('Application', false)) {
            return false;
        }
        $bases = Application::getNamespaces();
        if (!$base || in_array($base, $bases)) {
            return false;
        }
        //Not in a defined Base, check all
        $bases = array_reverse($bases);
        foreach ($bases as $base) {
            $namespace = implode('/', array_slice($parts, 1, count($parts) - 2));
            if (
                file_exists(BACKEND_FOLDER . '/' . $base . '/' . $namespace . '/' . $className . '.php')
            ) {
                require_once(BACKEND_FOLDER . '/' . $base . '/' . $namespace . '/' . $className . '.php');
                return true;
            }
        }
        return false;
    }
}
