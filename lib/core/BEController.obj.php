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
     * This contains the model on which this controller will execute
     * @var BEModel
     */
    private $_modelObj = null;

    /**
     * This contains the view for which this controller will execute
     * @var BEView
     */
    private $_viewObj = null;

    /**
     * The class constructor
     *
     * @param BERequest request A request object to serve
     */
    function __construct(BEModel $modelObj, BEView $viewObj)
    {
        $this->_modelObj = $modelObj;
        $this->_viewObj  = $viewObj;

        $this->_viewObj->bind('modelObj', $this->_modelObj);
    }

    /**
     * The main controller function
     *
     * Any Application logic can be put into this function
     */
    public function execute($action, $identifier, $arguments)
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
            throw new UncallableMethodException('Uncallable Method: ' . get_class($this->_modelObj) . "->$action()");
        }
        //Execute the Business Logic
        $result = call_user_func_array($function, $parameters);
        BEApplication::log('Executing ' . get_class($function[0]) . '::' . $action, 4);

        //Bind the result
        $this->_viewObj->bind('result', $result);
        return $result;
    }
}
