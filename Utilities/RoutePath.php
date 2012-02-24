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
     * @var string The route for this RoutePath
     */
    protected $_route;

    /**
     * @var callback The RoutePath's callback
     */
    protected $_callback;

    /**
     * @var string The HTTP verb for this RoutePath
     */
    protected $_verb;

    /**
     * @var array Defaults for the arguments of this RoutePath
     */
    protected $_defaults;

    /**
     * @var array The RoutePath's arguments
     */
    protected $_arguments = array();

    function __construct(array $options)
    {
        $this->_route     = $options['route'];

        //Construct the Callback
        $this->_callback  = $this->constructCallback($options['callback']);

        $this->_verb      = array_key_exists('verb', $options) ? strtoupper($options['verb']) : false;

        $this->_defaults  = array_key_exists('defaults', $options) ? $options['defaults'] : array();

        $this->_arguments = array_key_exists('arguments', $options) ? $options['arguments'] : array();
    }

    public function check($request)
    {
        //If the verb is defined, and it doesn't match, skip
        if ($this->_verb && $request->getMethod() != $this->_verb) {
            return false;
        }

        //Try to match the route
        $query = $request->getQuery();
        if ($this->_route == $query) {
            //Straight match, no arguments
            return $this;
        } else if (preg_match_all('/\/<([a-zA-Z][a-zA-Z0-9]*)>/', $this->_route, $matches)) {
            //Compile the Regex
            $varNames = $matches[1];
            $search   = $matches[0];
            $replace  = '(/([^/]*))?';
            $regex    = str_replace('/', '\/', str_replace($search, $replace, $this->_route));
            if (preg_match_all('/' . $regex . '/', $query, $matches)) {
                $arguments = array();
                $i = 2;
                foreach($varNames as $name) {
                    $arguments[$name] = $matches[$i][0];
                    $i = $i + 2;
                }
                //Regex Match
                $this->_arguments = $this->constructArguments($arguments);
                return $this;
            }
        }
        return false;
    }

    protected function constructCallback($callback)
    {
        $callbackArray = explode('::', $callback);
        if (count($callbackArray) == 1) {
            $callback = $callback[0];
        } else if (count($callbackArray) != 2) {
            throw new \Exception('Invalid Callback: ' . $callback);
        } else {
            $controllerClass = \Backend\Core\Application::resolveClass($callbackArray[0], 'controller');
            $methodName      = Strings::camelCase($callbackArray[1] . ' Action');

            if (!class_exists($controllerClass, true)) {
                throw new \Exception('Unknown Controller: ' . $callbackArray[0]);
            }

            $callback = array(
                new $controllerClass(),
                $methodName
            );

            //Decorate the Controller
            $callback[0] = \Backend\Core\Decorable::decorate($callback[0]);
        }
        return $callback;
    }

    protected function constructArguments($arguments)
    {
        if (is_array($this->_callback)) {
            $refMethod = new \ReflectionMethod($this->_callback[0], $this->_callback[1]);
        } else {
            $refMethod = new \ReflectionFunction($this->_callback);
        }
        //Get the parameters in the correct order
        $parameters = array();
        foreach ($refMethod->getParameters() as $param) {
            if (!empty($arguments[$param->getName()])) {
                $parameters[] = $arguments[$param->getName()];
            } else if (isset($this->_defaults[$param->getName()])) {
                $parameters[] = $this->_defaults[$param->getName()];
            } else if (!$param->isOptional()) {
                throw new \Exception('Missing argument ' . $param->getName());
            }
        }
        return $parameters;
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
