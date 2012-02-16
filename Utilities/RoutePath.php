<?php
namespace Backend\Core\Utilities;
/**
 * File defining RoutePath
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
 * The RoutePath class stores and manages information about a single Route
 *
 * @package Core
 */
class RoutePath
{
    /**
     * @var string The route path's controller
     */
    protected $_controller;
    
    /**
     * @var string The route path's action
     */
    protected $_action;
    
    /**
     * @var string The route path's arguments
     */
    protected $_arguments;
    
    function __construct($controller, $action, array $arguments)
    {
        $this->_controller = $controller;
        $this->_action     = $action;
        $this->_arguments  = $arguments;
    }

    /**
     * Get the RoutePath's controller
     *
     * @return string The controller for the route path
     */
    public function getController()
    {
        return $this->_controller;
    }    

    /**
     * Get the RoutePath's action
     *
     * @return string The action for the route path
     */
    public function getAction()
    {
        return $this->_action;
    }    

    /**
     * Get the RoutePath's arguments
     *
     * @return array The arguments for the route path
     */
    public function getArguments()
    {
        return $this->_arguments;
    }    
}

