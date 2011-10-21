<?php
/**
 * File defining HtmlView
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
 * @package ViewFiles
 */
/**
 * Output a request as HTML.
 *
 * @package Views
 */
class HtmlView extends BEView
{
    /**
     * Handle HTML requests
     * @var array
     */
    public static $handledFormats = array('html', 'htm', 'text/html', 'application/xhtml+xml');

    function __construct()
    {
        ob_start();

        $url = 'http';
        if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) {
            $url .= 's';
        }
        $url .= '://' . $_SERVER['HTTP_HOST'];

        $url .= $_SERVER['PHP_SELF'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }

        $url = parse_url($url);
        $folder = !empty($url['path']) ? $url['path'] : '/';
        if (substr($folder, -1) != '/' && substr($folder, -1) != '\\') {
            $folder = dirname($folder);
        }
        if ($folder != '.') {
            if (substr($folder, strlen($folder) - 1) != '/') {
                $folder .= '/';
            }
            define('WEB_SUB_FOLDER', $folder);
        } else {
            define('WEB_SUB_FOLDER', '/');
        }
        $this->bind('WEB_SUB_FOLDER', WEB_SUB_FOLDER);

        $domain = !empty($url['host']) ? $url['host'] : 'localhost';
        define('SITE_DOMAIN', $domain);
        $this->bind('SITE_DOMAIN', SITE_DOMAIN);

        $scheme = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url = SITE_DOMAIN . WEB_SUB_FOLDER;
        define('SITE_LINK', $scheme . $url);
        $this->bind('SITE_LINK', SITE_LINK);

        parent::__construct();
    }

    function output()
    {
        $render = new Render($this);

        $buffered = ob_get_clean();
        $this->bind('buffered', $buffered);
        $index = $render->file(BACKEND_FOLDER . '/templates/index.tpl.php');
        echo $index;
    }
}
