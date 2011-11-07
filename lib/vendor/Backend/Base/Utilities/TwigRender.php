<?php
namespace Backend\Base\Utilities;
/**
 * File defining Base\Utilities\TwigRender
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
 * Render templates
 *
 * @package Utility
 */
class TwigRender implements \Backend\Base\Interfaces\RenderUtility
{
    /**
     * @var Core\View The view used to render
     */
    protected $_view = null;

    /**
     * @var Twig The twig used to render
     */
    protected $_twig = null;

    public function __construct(\Backend\Core\View $view = null)
    {
        if ($view) {
            $this->setView($view);
        }
    }

    public function setView(\Backend\Core\View $view)
    {
        require_once('Twig/Autoloader.php');
        \Twig_Autoloader::register();

        $this->_view = $view;
        $loader      = new \Twig_Loader_Filesystem($this->_view->templateLocations);
        $this->_twig = new \Twig_Environment($loader);
    }

    public function file($template, array $values = array())
    {
        if (!$this->_view) {
            return false;
        }

        $values = array_merge($this->_view->getVariables(), $values);

        return $this->_twig->render($template, $values);
    }
}
