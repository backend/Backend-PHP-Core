<?php
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
 * Give custom JSON encoding functionality to objects
 *
 * @package Decorators
 */
namespace Backend\Core\Decorators;
class JsonDecorator implements \Backend\Core\Interfaces\DecoratorInterface
{
    /**
     * @var Object The object this class is decorating
     */
    protected $_object;

    /**
     * The constructor for the class
     *
     * @param ModelInterface The model to decorate
     */
    function __construct(\Backend\Core\Interfaces\DecorableInterface $object)
    {
        $this->_object = $object;
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_object, $method), $args);
    }

    /**
     * Get the normal properties of the Object
     */
    public function getProperties()
    {
        if (function_exists($this->_object, 'getProperties')) {
            return $this->_object->getProperties();
        }
        $properties = get_object_vars($this);
        $filter     = function($value) { return substr($value, 0, 1) != '_'; };
        $allowed    = array_filter(array_keys($properties), $filter);
        $properties = array_intersect_key($properties, array_flip($allowed));
        return $properties;
    }

    /**
     * JSON encode the object, including all properties
     */
    public function _toJson()
    {
        $properties     = $this->_object->getProperties();
        $object         = new \StdClass();
        $object->_class = get_class($this->_object);
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return json_encode($object);
    }
}
