<?php
namespace Backend\Core;
/**
 * File defining Controller
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
 * The main controller class.
 *
 * @package Core
 */
class Controller implements Interfaces\ControllerInterface, Interfaces\Decorable
{
    /**
     * @var Route This contains the route object that will help decide what controller,
     * model and action to execute
     */
    protected $_route = null;

    /**
     * @var Response This contains the Response that will be returned
     */
    protected $_response = null;

    /**
     * @var ModelInterface This contains the model on which this controller will execute
     */
    protected $_model = null;

    /**
     * @var array An array of names of decorators to apply to the controller
     */
    protected $_decorators = array();

    /**
     * The constructor for the class
     *
     * @param Response A response the controller should manipulate and return
     */
    function __construct(Response $response = null)
    {
        //Setup the response
        $this->_response = is_null($response) ? new \Backend\Core\Response() : $response;
    }

    /**
     * The main controller function
     *
     * This function can be called multiple times, although it's probably better to
     * run {@link Application::main} to get the desired effect.
     *
     * @param Route The route the controller should execute
     * @return Response The response to send to the client
     */
    public function execute(Route $route)
    {
        //Get Route
        $this->_route = $route;
        $area         = $this->_route->getArea();
        $action       = $this->_route->getAction();

        //Get and check the model
        $modelName = 'Backend\Models\\' . class_name($area);
        if (class_exists($modelName, true)) {
            $this->_model = new $modelName();
        }

        //Setup the function
        $function   = $action . 'Action';
        $parameters = array(
            $this->_route->getIdentifier(),
            $this->_route->getArguments()
        );

        //Determine the method to call. Application takes precedence over Business Logic
        if (is_callable(array($this, $function))) {
            $functionCall = array($this, $function);
        } else if (is_callable(array($this->_model, $function))) {
            $functionCall = array($this->_model, $function);
        } else {
            throw new \BadMethodCallException(
                "Uncallable Method: $area::$action()"
            );
        }

        //Execute the Controller or Model method
        Application::log('Executing ' . get_class($functionCall[0]) . '::' . $functionCall[1], 4);
        $result = call_user_func_array($functionCall, $parameters);

        //Execute the View related method
        $view = \Backend\Core\Application::getTool('View');
        if ($view) {
            $viewMethod = $this->getViewMethod('create', $view);
            if ($viewMethod instanceof \ReflectionMethod) {
                $prototype = $viewMethod->getPrototype();
                Application::log('Executing ' . $prototype['class'] . '::' . $prototype['name'], 4);
                $result = $viewMethod->invoke($this, $view, $result);
            }
        }

        $this->_response->content($result);

        return $this->_response;
    }

    /**
     * Get an array of decorators for the class
     *
     * @return array The decorators to apply to the class
     */
    public function getDecorators()
    {
        return $this->_decorators;
    }

    /**
     * Add a decorator to the class
     *
     * @param string The name of the decorator class to add
     */
    public function addDecorator($decorator)
    {
        $this->_decorators[] = $decorator;
    }

    /**
     * Remove a decorator from the class
     *
     * @param string The name of the decorator class to remove
     */
    public function removeDecorator($decorator)
    {
        $key = array_search($decorator, $this->_decorators);
        if ($key !== false) {
            unset($this->_decorators[$key]);
        }
    }

    /**
     * Return a view method for the specified action
     *
     * @param string The action to check for
     */
    public function getViewMethod($action, View $view = null)
    {
        $view = is_null($view) ? \Backend\Core\Application::getTool('View') : $view;
        if (!$view) {
            return null;
        }
        //Check for a transform for the current view in the controller
        $methodName = strtolower(get_class($view));
        $methodName = substr($methodName, strrpos($methodName, '\\') + 1);
        $methodName = $action . ucwords($methodName);

        try {
            $reflector  = new \ReflectionClass(get_class($this));
            $viewMethod = $reflector->getMethod($methodName);
        } catch (\Exception $e) {
            return null;
        }
        return $viewMethod;
    }
}
