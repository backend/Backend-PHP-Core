<?php
/**
 * Copyright (c) 2011 JadeIT cc
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
define('BACKEND_FOLDER', dirname(getcwd()) . '/lib');
define('APP_FOLDER', BACKEND_FOLDER . '/webapp');
define('WEB_FOLDER', BACKEND_FOLDER . '/public');
//define('SITE_FOLDER', APP_FOLDER . '/sites/liveserver.com');

switch ($_SERVER['HTTP_HOST']) {
case 'www.liveserver.com':
    if (!defined('SITE_STATE')) {
        define('SITE_STATE', 'production');
    }
    break;
case 'localhost':
default:
    if (!defined('SITE_STATE')) {
        define('SITE_STATE', 'local');
    }
    break;
}

require(BACKEND_FOLDER . '/core/BEApplication.obj.php');
$application = new BEApplication();
$application->main();

//Done
