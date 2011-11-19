<?php
namespace Backend\Base\Bindings;
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
 *
 * @package Bindings
 */
abstract class Binding
{
    /**
     * Find instances of the binding in the resource. Does not nead to be bound.
     *
     * Store the instances found in the _list property.
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
    abstract public function read($identifier);

    /**
     * Update the current instance of the resource on it's source.
     *
     * The binding needs to be bound to use this function. The data currently in @_resource
     * will be used. Use the @_modified flag to prevent unnecessary updates.
     * @param mixed A respresentation of the data with which to update the instance
     * @return mixed A respresentation of the updated instance of the resource if succesful.
     */
    abstract public function update($identifier, $data);

    /**
     * Delete the bound instance of the resource from it's source.
     *
     * The binding needs to be bound to use this function.
     * @return boolean If the update was succesful or not.
     */
    abstract public function delete($identifier);

    /**
     * Return an array of fields required to create a new instance on the source
     *
     * The binding does not need to be bound to use this function.
     * @return array An array of fields required. The array keys should be the simple names,
     * with the value containing options.
     */
    abstract public function fieldNames();
}
