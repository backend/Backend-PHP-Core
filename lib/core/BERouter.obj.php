<?php
/**
 * File defining BERouter
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
 * The Router class.
 *
 * @package Core
 */
class BERouter
{
    /**
     * This contains the route's model
     * @var string
     */
    protected $_model;

    /**
     * This contains the route's action
     * @var string
     */
    protected $_action;

    /**
     * This contains the route's identifier
     * @var integer
     */
    protected $_identifier;

    /**
     * This contains the route's arguments
     * @var array
     */
    protected $_arguments;


    /**
     * The class constructor
     *
     * We use REST URI's, so the following structure should be followed.
     * $resource/$identifier/$extra/$parameters
     * See the BERequest class for how the action on the resource is determined
     * *
     *
     * @param BERequest request A request object to serve
     */
    function __construct(BERequest $request)
    {
        $query = $request->getQuery();
        if ($query == '') {
            //TODO Make the default query configurable
            $query = 'home';
        }

        //Map the REST verbs to CRUD
        switch ($request->getMethod()) {
        case 'POST':
            $action = 'create';
            break;
        case 'GET':
            $action = 'read';
            break;
        case 'PUT':
            $action = 'update';
            break;
        case 'DELETE':
            $action = 'delete';
            break;
        }

        $query = explode('/', $query);
        //A zero identifier indicates that the action refers to the whole collection
        if (count($query) == 1) {
            $query[1] = 0;
        }

        $this->_model      = $query[0];
        $this->_action     = $action;
        $this->_identifier = $query[1];
        $this->_arguments  = count($query) > 2 ? array_slice($query, 2) : array();

        $message = 'Route: ' . $request->getMethod() . ': ' . $query[0] . '/' . $action . '/' . $query[1];
        BEApplication::log($message, 4);
    }

    /**
     * Make the route's protected properties readonly.
     */
    function __get($property)
    {
        $property = '_' . $property;
        return property_exists($this, $property) ? $this->$property : null;
    }
}
