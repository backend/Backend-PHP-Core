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
     * @var string The route path's callback
     */
    protected $_callback;
    
    /**
     * @var string The route path's arguments
     */
    protected $_arguments;
    
    function __construct($callback, array $arguments)
    {
        if (is_array($callback)) {
            $controllerClass = \Backend\Core\Application::resolveClass($callback[0], 'controller');
            $methodName      = Strings::camelCase($callback[1] . ' Action');

            if (!class_exists($controllerClass, true)) {
                throw new \Exception('Unknown Controller: ' . $callback[0]);
            }
            $callback[0] = new $controllerClass();

            //Decorate the Controller
            if ($callback[0] instanceof Interfaces\Decorable) {
                foreach ($callback[0]->getDecorators() as $decorator) {
                    $callback[0] = new $decorator($callback[0]);
                    if (!($callback[0] instanceof \Backend\Core\Decorators\ControllerDecorator)) {
                        throw new \Exception(
                            'Class ' . $decorator . ' is not an instance of \Backend\Core\Decorators\ControllerDecorator'
                        );
                    }
                }
            }

            $callback[1] = $methodName;
        }
        $this->_callback  = $callback;
        $this->_arguments = $arguments;
    }

    /**
     * Get the RoutePath's callback
     *
     * @return callback The callback for the route path
     */
    public function getCallback()
    {
        return $this->_callback;
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

