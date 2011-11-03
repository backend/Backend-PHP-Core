<?php
namespace Base;
/**
 * File defining \Base\Binding
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
 * @package BindingFiles
 */
/**
 * Abstract class for bindings for a model
 *
 * Bindings define the source of a model. This can be databases, URI's and files. Anything
 * from where a Model can be read, where models can be written and updated to, and deleted from.
 * When a binding has been bound with an instance of the resource, it can be saved,
 * updated and deleted. Unbound, it can return lists of the resource.
 *
 * @package Bindings
 */
abstract class Binding
{
    /**
     * @var mixed The identifier for the instance of the binding if bound.
     */
    protected $_id = null;

    /**
     * @var mixed An array or an object containing an instance of the binding.
     */
    protected $_resource = null;

    /**
     * @var boolean Flag that specifies if the current instance of the binding has any unsaved changes.
     */
    protected $_modified = false;

    /**
     * Get values from the current instance of the binding.
     */
    public function __get($propertyName)
    {
        if (!$this->isBound()) {
            return null;
        }
        switch (true) {
        case is_array($this->_resource):
            if (array_key_exists($propertyName, $this->_resource)) {
                return $this->_resource[$propertyName];
            }
            break;
        case is_object($this->_resource):
            if (property_exists($propertyName, $this->_resource)) {
                return $this->_resource->$propertyName;
            }
            break;
        }
        return null;
    }

    /**
     * Set values for the current instance of the binding.
     */
    public function __set($propertyName, $value)
    {
        if (!$this->isBound()) {
            return false;
        }
        switch (true) {
        case is_array($this->_resource):
            if ($this->_resource[$propertyName] !== $value) {
                $this->_modified = true;
            }
            $this->_resource[$propertyName] = $value;
            return $this->_resource[$propertyName];
            break;
        case is_object($this->_resource):
            if ($this->_resource->$propertyName !== $value) {
                $this->_modified = true;
            }
            $this->_resource->$propertyName = $value;
            return $this->_resource->$propertyName;
            break;
        }
        return false;
    }

    /**
     * Get the identifier for the current instance of the binding
     *
     * @return mixed The identifier for binding
     */
    public function getIdentifier()
    {
        return $this->isBound() ? $this->_id : null;
    }

    /**
     * Set the identifier for the binding and bind it to the specified instance
     *
     * @param mixed The identifier for binding
     */
    public function setIdentifier($identifier)
    {
        $this->_id = $identifier;
        $this->bind();
        return $this;
    }

    /**
     * Check the supplied identifier, or, if none is supplied, return the current identifier
     *
     * @param mixed An optional identifier
     * @return mixed The identifier for the current instance
     */
    public function checkIdentifier($identifier = null)
    {
        $identifier = is_null($identifier) ? $this->getIdentifier() : $identifier;
        if (is_null($identifier)) {
            return null;
        }
        return $identifier;
    }

    /**
     * Check to see if the binding is bound to an instance of the resource
     *
     * @return boolean If the binding is bound to a instance of the resource
     */
    public function isBound()
    {
        return !is_null($this->_identifier) && !is_null($this->_resource);
    }

    /**
     * Bind to a specified resource
     *
     * The @_id and @_resource should be set in this function.
     * @param mixed The unique identifier of the resource to bind to. If not supplied, use
     * the set id.
     * @return boolean If the binding was succesful.
     */
    abstract public function bind($identifier = null);

    /**
     * Find instances of the binding in the resource. Does not nead to be bound.
     *
     * Don't specify any criteria to retrieve a full list of instances.
     * @return array An array of representations of the resource
     */
    abstract public function find();

    /**
     * Create an instance of the source, and return the instance.
     *
     * @param mixed The unique identifier for the instance.
     * @param mixed A respresentation of the data with which to create the instance
     * @return mixed A respresentation of the create instance of the resource if succesful.
     */
    abstract public function create($identifier, $data);

    /**
     * Bind to an instance of the source, and return the instance.
     *
     * The binding does not need to be bound if an identifier is supplied.
     * @param mixed The unique identifier for the instance.
     * @return mixed A respresentation of the specified instance of the resource.
     */
    abstract public function read($identifier = null);

    /**
     * Update the current instance of the resource on it's source.
     *
     * The binding needs to be bound to use this function. The data currently in @_resource
     * will be used. Use the @_modified flag to prevent unnecessary updates.
     * @param mixed A respresentation of the data with which to update the instance
     * @return mixed A respresentation of the updated instance of the resource if succesful.
     */
    abstract public function update($data);

    /**
     * Delete the bound instance of the resource from it's source.
     *
     * The binding needs to be bound to use this function.
     * @return boolean If the update was succesful or not.
     */
    abstract public function delete();
}
