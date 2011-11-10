<?php
namespace Backend\Core;
/**
 * File defining Core\Model
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
 * The main Model class.
 *
 * @package Core
 */
class Model implements Interfaces\ModelInterface, Interfaces\Decorable
{
    /**
     * @var array An array of names of decorators to apply to the model
     */
    protected $_decorators = array();

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
