<?php
/**
 * File defining CoreRouter
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
 * The Router class that uses the query string to help determine the area, action
 * and identifier for the request.
 *
 * @package Core
 */
class CoreRouter
{
    /**
     * This contains the route's request
     * @var CoreRequest
     */
    protected $_request;

    /**
     * This contains the route's model
     * @var string
     */
    protected $_area;

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
     * See the CoreRequest class for how the action on the resource is determined
     * *
     *
     * @param CoreRequest A request object to serve
     */
    function __construct(CoreRequest $request = null)
    {
        $this->_request = is_null($request) ? new CoreRequest() : $request;

        //Setup and split the query
        $query = $this->_request->getQuery();
        if ($query == '') {
            //TODO Make the default query configurable
            $query = 'home';
        }
        $query = explode('/', $query);

        //Set the area
        $this->_area = $query[0];

        //Map the REST verbs to CRUD
        switch ($this->_request->getMethod()) {
        case 'GET':
            $action = 'read';
            break;
        case 'PUT':
            $action = 'update';
            break;
        case 'POST':
            $action = 'create';
            break;
        case 'DELETE':
            $action = 'delete';
            break;
        }
        $this->_action = $action;

        //Determine the resource identifier
        if (count($query) == 1) {
            //A zero identifier indicates that the action refers to the whole collection
            $query[1] = 0;
        }
        $this->_identifier = $query[1];

        //Determine the additional arguments
        $this->_arguments = count($query) > 2 ? array_slice($query, 2) : array();

        $message = 'Route: ' . $this->_request->getMethod() . ': ' . $this->getQuery();
        CoreApplication::log($message, 4);
    }

    /**
     * Return the Request Query String
     *
     * @return string The query string for the request
     */
    function getQuery()
    {
        $result = $this->_area . '/' . $this->_action . '/' . $this->_identifier
            . implode('/', $this->_arguments);
        return $result;
    }

    /**
     * Return the Request
     *
     * @return CoreRequest The Route's Request
     */
    function getRequest()
    {
        return $this->_request;
    }

    /**
     * Return the Area component of the Request
     *
     * @return string The area component of the Request
     */
    function getArea()
    {
        return $this->_area;
    }

    /**
     * Return the Action component of the Request
     *
     * @return string The action component of the Request
     */
    function getAction()
    {
        return $this->_action;
    }

    /**
     * Return the Identifier of the Request
     *
     * @return string The identifier of the Request
     */
    function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Return the Arguments of the Request
     *
     * @return array The arguments of the Request
     */
    function getArguments()
    {
        return $this->_arguments;
    }
}
