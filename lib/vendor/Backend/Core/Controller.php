<?php
namespace Backend\Core;
/**
 * File defining Core\Controller
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
     * This contains the model on which this controller will execute
     * @var Core\Model
     */
    private $_modelObj = null;

    /**
     * This contains the view for which this controller will execute
     * @var Core\View
     */
    private $_viewObj = null;

    /**
     * An array of names of decorators to apply to the controller
     * @var array
     */
    protected $_decorators = array();

    /**
     * The class constructor
     *
     * @param Core\Model The Model the controller should execute on
     * @param Core\View The View the controller should execute with
     */
    function __construct(Interfaces\ModelInterface $modelObj, View $viewObj)
    {
        $this->_modelObj = $modelObj;
        $this->_viewObj  = $viewObj;

        $this->_viewObj->bind('modelObj', $this->_modelObj);
    }

    /**
     * The main controller function
     *
     * Any Application logic can be put into this function
     * @param string The action the controller should execute
     * @param mixed The identifier that should be passed to the executing function
     * @param array The extra arguments that should be passed to the executing function
     * @return mixed The result of the execution
     */
    public function execute($action, $identifier, array $arguments)
    {
        $parameters = array($identifier, $arguments);
        //Get and check the method
        $controllerFunc = array($this, $action);
        $modelFunc      = array($this->_modelObj, $action);
        if (is_callable($controllerFunc)) {
            $function = $controllerFunc;
        } else if (is_callable($modelFunc)) {
            $function = $modelFunc;
        } else {
            throw new \BadMethodCallException(
                'Uncallable Method: ' . get_class($this->_modelObj) . "->$action()"
            );
        }
        //Execute the Business Logic
        Application::log('Executing ' . get_class($function[0]) . '::' . $function[1], 4);
        $result = call_user_func_array($function, $parameters);

        return $result;
    }

    public function getDecorators()
    {
        return $this->_decorators;
    }

    public function addDecorator($decorator)
    {
        $this->_decorators[] = $decorator;
    }

    public function removeDecorator($decorator)
    {
        $key = array_search($decorator, $this->_decorators);
        if ($key !== false) {
            unset($this->_decorators[$key]);
        }
    }

    /*public function accept(Visitor $visitor)
    {
        return $visitor->visit($this);
    }*/
}
