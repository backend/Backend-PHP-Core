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
 * Normal / bindable properties should NOT start with an underscore. Meta properties should.
 *
 * @package Core
 */
class Model extends Decorable implements Interfaces\ModelInterface
{
    /**
     * @var array The human friendly name for the model
     */
    protected $_name = 'Backend Model';

    /**
     * @var array An array of names of decorators to apply to the model
     */
    protected $_decorators = array();

    public function getName()
    {
        $class = get_class($this);
        $class = explode('\\', $class);
        return end($class);
    }

    public function __get($propertyName)
    {
        $funcName = 'get' . Utilities\Strings::className($propertyName);
        if (method_exists($this, $funcName)) {
            $this->$funcName($value);
        } else if (property_exists($this, $propertyName)) {
            return $this->$propertyName;
        }
        return null;
    }

    public function __set($propertyName, $value)
    {
        $funcName = 'set' . Utilities\Strings::className($propertyName);
        if (method_exists($this, $funcName)) {
            $this->$funcName($value);
        } else {
            $this->$propertyName = $value;
        }
        return $this;
    }

    /**
     * Populate the Model with the specified properties.
     *
     * The function will use any `set` functions defined.
     * @param array An array containing the properties for the model
     * @return Object The object that was populated
     */
    public function populate(array $properties)
    {
        foreach ($properties as $name => $value) {
            $funcName = 'set' . Utilities\Strings::className($name);
            if (method_exists($this, $funcName)) {
                $this->$funcName($value);
            } else if (property_exists($this, $name)) {
                $this->$name = $value;
            } else {
                throw new \Exception('Undefined property ' . $name . ' for ' . get_class($this));
            }
        }
        return $this;
    }

    /**
     * Get the normal properties of the Model
     */
    public function getProperties()
    {
        $properties = get_object_vars($this);
        $filter     = function($value) { return substr($value, 0, 1) != '_'; };
        $allowed    = array_filter(array_keys($properties), $filter);
        $properties = array_intersect_key($properties, array_flip($allowed));
        return $properties;
    }
}
