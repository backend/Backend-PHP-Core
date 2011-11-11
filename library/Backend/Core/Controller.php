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
    private $_route = null;

    /**
     * @var Request This contains the Request that is being handled
     */
    private $_request = null;

    /**
     * @var Response This contains the Response that will be returned
     */
    private $_response = null;

    /**
     * @var View This contains the view object that display the executed request
     */
    private $_view = null;

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
     * @param Request The request for the controller to handle
     * @param View The view that determines the format of the response
     * @param Response A response the controller should manipulate and return
     */
    function __construct(Request $request = null, View $view = null, Response $response = null)
    {
        //Setup the request and the response
        $this->_request  = is_null($request)  ? new \Backend\Core\Request()  : $request;
        $this->_response = is_null($response) ? new \Backend\Core\Response() : $response;

        //Get the View
        if ($view instanceof View) {
            $this->_view = $view;
        } else {
            try {
                $view = Utilities\ViewFactory::build($this->_request);
            } catch (\Exception $e) {
                Application::log('View Exception: ' . $e->getMessage(), 2);
                $view = new View();
            }
            $this->_view = $view;
        }
        Application::log('Running Controller in ' . get_class($this->_view) . ' View');
    }

    /**
     * The main controller function
     *
     * @param Route The route the controller should execute
     * @return Response The response to send to the client
     */
    public function execute(Route $route = null)
    {
        //Get Route
        $this->_route = is_null($route) ? new Route($this->_request) : $route;
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

        //Execute the Business Logic
        if (!is_callable(array($this, $function))) {
            throw new \BadMethodCallException(
                'Uncallable Method: ' . get_class($this) . "->$function()"
            );
        }

        Application::log('Executing ' . get_class($this) . '::' . $function, 4);
        $result = call_user_func_array(array($this, $function), $parameters);

        $this->_response->content($result);

        //Pass the result to the View
        $this->_response = $this->_view->transform($this->_response);

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
}
