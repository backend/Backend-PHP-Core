<?php
/**
 * Main URL end point
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
define('PROJECT_FOLDER', dirname(getcwd()) . '/');
define('VENDOR_FOLDER', PROJECT_FOLDER . 'lib/vendor/');
define('BACKEND_FOLDER', VENDOR_FOLDER . 'Backend/');
define('APP_FOLDER', BACKEND_FOLDER . 'Application/');
define('WEB_FOLDER', PROJECT_FOLDER . 'public/');
//define('SITE_FOLDER', APP_FOLDER . '/sites/liveserver.com');

if (array_key_exists('HTTP_HOST', $_SERVER)) {
    switch ($_SERVER['HTTP_HOST']) {
    case 'www.liveserver.com':
        if (!defined('SITE_STATE')) {
            define('SITE_STATE', 'production');
        }
        break;
    case 'localhost':
    default:
        if (!defined('SITE_STATE')) {
            define('SITE_STATE', 'development');
        }
        break;
    }
} else {
    define('SITE_STATE', 'development');
}

require(BACKEND_FOLDER . 'Core/Application.php');
//Using Simple Logging, as shipped with the framework
$application = new Backend\Core\Application(
    null,
    null,
    array(
        'Logger'   => '\Backend\Core\Utilities\Logger',
        'Config'   => '\Backend\Core\Utilities\Config',
    )
);
//Using the PEAR Log module
//http://pear.github.com/Log/
/*
require_once('Log.php');
$application = new CoreApplication(
    null,
    null,
    array(
        array('Logger', Log::factory('file', '/tmp/out.log', 'TEST'))
    )
);
*/
$application->main();

//Done
