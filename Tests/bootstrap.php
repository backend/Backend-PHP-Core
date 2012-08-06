<?php
/**
 * The bootstrapping script
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Core
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
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
 */
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    die("This file cannot be executed directly");
}
date_default_timezone_set('Africa/Johannesburg');

/**
 * The project folder, containing the public folder, all libraries and configs.
 *
 * @var string
 */
define('PROJECT_FOLDER', realpath(dirname(__FILE__) . '/../') . '/');

/**
 * The root vendor folder, containing all libraries, including Backend-Core.
 *
 * @var string
 */
define('VENDOR_FOLDER', PROJECT_FOLDER . 'vendor/');

/**
 * The root application folder, containing all application space code.
 *
 * @var string
 */
define('SOURCE_FOLDER', PROJECT_FOLDER . 'app/');

/**
 * The root folder for Backend-Core source files
 *
 * @var string
 */
define('BACKEND_FOLDER', VENDOR_FOLDER . 'Backend/');

/**
 * The publicly accessable part of the installation
 *
 * @var string
 */
define('WEB_FOLDER', PROJECT_FOLDER . 'public/');

/**
 * The default extension for config files
 *
 * @var string
 */
define('CONFIG_EXT', 'yaml');

if (!defined('BACKEND_SITE_STATE')) {
    if (PHP_SAPI == 'cli') {
        define('BACKEND_SITE_STATE', 'testing');
    } else if (in_array($_SERVER['SERVER_ADDR'], array('::1', '127.0.0.1'))) {
        define('BACKEND_SITE_STATE', 'development');
    } else {
        define('BACKEND_SITE_STATE', 'production');
    }
}

/**
 * Register and  load available autoloaders
 */
spl_autoload_register(function ($class) {
    if (0 === strpos(ltrim($class, '/'), 'Backend\Core')) {
        if (file_exists($file = __DIR__.'/../'.substr(str_replace('\\', '/', $class), strlen('Backend\Core')).'.php')) {
            require_once $file;
        }
    }
});

if (file_exists($loader = __DIR__.'/../vendor/autoload.php')) {
    require_once $loader;
}