<?php
namespace Backend\Core\Decorators;
/**
 * File defining Core\Decorators\ModelDecorator
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
 * @package DecoratorFiles
 */
/**
 * Abstract base class for Model decorators
 *
 * @package Decorators
 */
abstract class ModelDecorator
    extends \Backend\Core\Model
    implements \Backend\Core\Interfaces\ModelInterface, \Backend\Core\Interfaces\Decorator
{
    /**
     * @var ModelInterface The model this class is decorating
     */
    protected $_decoratedModel;

    /**
     * The constructor for the class
     *
     * @param ModelInterface The model to decorate
     */
    function __construct(\Backend\Core\Interfaces\Decorable $model)
    {
        $this->_decoratedModel = $model;
    }

    public function __call($method, $args)
    {
        return call_user_func_array(
            array($this->_decoratedModel, $method),
            $args
        );
    }
}
