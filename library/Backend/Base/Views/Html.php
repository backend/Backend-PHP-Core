<?php
namespace Backend\Base\Views;
/**
 * File defining \Base\Views\Html
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
class Html extends \Backend\Core\View
{
    /**
     * @var array Handle HTML requests
     */
    public static $handledFormats = array('html', 'htm', 'text/html', 'application/xhtml+xml');

    /**
     * @var array Location for template files. List them in order of preference
     */
    public $templateLocations = array();

    /**
     * @var array Content blocks built up to send later
     */
    protected $_content = array();

    function __construct($renderer = null)
    {
        ob_start();

        self::setupConstants();

        $this->templateLocations = array(
            APP_FOLDER . 'templates/',
            BACKEND_FOLDER . 'templates/',
        );

        $this->templateLocations = array_filter($this->templateLocations, 'file_exists');

        parent::__construct($renderer);
    }

    function redirect($location)
    {
        header('X-Redirector: ' . get_class($this));
        header('Location: ' . $location);
        die('<html><body>Redirecting to <a href="' . $location . '">' . $location . '</a></body></html>');
    }
    /**
     * Set up a number of constants / variables to make creating and parsing templates easier.
     */
    private function setupConstants()
    {
        //Get the current URL
        $url = 'http';
        if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) {
            $url .= 's';
        }
        $url .= '://' . $_SERVER['HTTP_HOST'];

        $url .= $_SERVER['PHP_SELF'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }

        //Parse the current URL to get the SITE_SUB_FOLDER
        $url = parse_url($url);
        $folder = !empty($url['path']) ? $url['path'] : '/';
        if (substr($folder, -1) != '/' && substr($folder, -1) != '\\') {
            $folder = dirname($folder);
        }
        if ($folder != '.') {
            if (substr($folder, strlen($folder) - 1) != '/') {
                $folder .= '/';
            }
            define('SITE_SUB_FOLDER', $folder);
        } else {
            define('SITE_SUB_FOLDER', '/');
        }
        $this->bind('SITE_SUB_FOLDER', SITE_SUB_FOLDER);

        //Parse the current URL to get the SITE_DOMAIN
        $domain = !empty($url['host']) ? $url['host'] : 'localhost';
        define('SITE_DOMAIN', $domain);
        $this->bind('SITE_DOMAIN', SITE_DOMAIN);

        //Use SITE_DOMAIN and SITE_SUB_FOLDER to create a SITE_LINK
        $scheme = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url = SITE_DOMAIN . SITE_SUB_FOLDER;
        define('SITE_LINK', $scheme . $url);
        $this->bind('SITE_LINK', SITE_LINK);
    }

    function transform(\Backend\Core\Response $response)
    {
        $this->_content = array();
        //Render content blocks, get a title
        foreach ($response->getContent() as $content) {
            //Check for an exception
            $this->transformContent($content);
        }
        $this->bind('title', 'Result: Unknown', false);

        //Get buffered output
        $buffered = ob_get_clean();
        $this->_content[] = $this->render('buffered.tpl', array('buffered' => $buffered));

        $content = array(
            $this->render('index.tpl',
                array(
                    'content'  => $this->_content,
                    'response' => $response,
                )
            )
        );

        //Replace the current content with the new transformed content
        $response->setContent($content);

        return $response;
    }

    public function transformContent($content)
    {
        if (is_scalar($content)) {
            if (strlen($content) > 24) {
                $title = substr($content, 0, 24) . '&hellip;';
            } else {
                $title = $content;
            }
            $this->bind('title', 'Result: ' . $title, false);
            $this->_content[] = $content;
        } else {
            //Set the title
            if (is_object($content)) {
                $this->bind('title', 'Object: ' . get_class($content), false);
            } else if (is_array($content)) {
                $this->bind('title', 'Array(' . count($content) . ')', false);
            }
            if ($content instanceof \Exception) {
                $this->_content[] = $this->render('exception.tpl', array('object' => $content));
            } else if ($content instanceof \Traversable || is_array($content)) {
                $prefix = (is_array($content) ? 'Array' : get_class($content)) . '-';
                foreach ($content as $key => $value) {
                    $this->_content[] = '<h3>' . $prefix . $key . '</h3>';
                    $this->transformContent($value);
                    $this->_content[] = '<hr>';
                }
            }
        }
    }
}
