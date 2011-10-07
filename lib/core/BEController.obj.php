<?php
/**
 * File defining BEController
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
class BEController
{
    /**
     * This contains the router object that will help decide what model and action to execute
     * @var BERouter
     */
    private $_router = null;

    /**
     * The class constructor
     *
     * @param BERequest request A request object to serve
     */
    function __construct(BERouter $router = null)
    {
        $this->_router = is_null($router) ? new BERouter(new BERequest()) : $router;
    }

    /**
     * The main controller function
     *
     * The supplied router will be used to determine what action should be executed
     * on which model and / or controller
     */
    public function execute()
    {
        try {
            //Get and check the model
            $model = self::translateModel($this->_router->model);
            if (!class_exists($model, true)) {
                throw new UnknownModelException('Unkown Model: ' . $model);
            }
            $modelObj = new $model();

            //See if a controller exists for this model
            $controller = self::translateController($this->_router->model);
            if (class_exists($controller, true)) {
                //TODO Execute the action for this $controller
                $controllerObj = new $controller($modelObj);
                //$result =
            } else {
                $result = null;
            }

            //Get and check the method
            $method = $this->_router->action . 'Action';
            $function = array($modelObj, $method);
            if (!is_callable($function)) {
                throw new UncallableMethodException("Uncallable Method: $model->$method()");
            }

            BEApplication::log('Executing ' . get_class($modelObj) . '::' . $method, 4);
            $parameters = array($this->_router->identifier, $this->_router->arguments);
            $result = call_user_func_array($function, $parameters);

        } catch (Exception $e) {
            BEApplication::log($e->getMessage(), 1);
            //TODO Get the Error Model, and execute
            //TODO Handle UknownRouteException
            //TODO Handle UnknownModelException
            //TODO Handle UnsupportedMethodException
        }
    }

    /**
     * Utility function to translate a URL part to a Controller Name
     *
     * All Controllers are plural, and ends with Controller
     * @todo We need to define naming standards
     */
    public static function translateController($resource)
    {
        return class_name($resource) . 'Controller';
    }

    /**
     * Utility function to translate a URL part to a Model Name
     *
     * All Models are plural, and ends with Model
     * @todo We need to define naming standards
     */
    public static function translateModel($resource)
    {
        return class_name($resource) . 'Model';
    }
}
