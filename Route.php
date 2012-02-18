<?php
namespace Backend\Core;
/**
 * File defining Route
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
 * The Route class that uses the query string to help determine the controller, action
 * and arguments for the request.
 *
 * @package Core
 */
class Route
{
    /**
     * @var array An array of predefined routes
     */
    protected $_routes;
    
    public function addRoute($name, $route)
    {
        $this->_routes[$name] = $route;
    }
    
    public function getRoute($name)
    {
        return array_key_exists($name, $this->_routes) ? $this->_routes[$name] : null;
    }

    /**
     * The constructor for the class
     *
     * @param Request A request object to serve
     */
    function __construct($routesFile = false)
    {
        $routesFile = $routesFile ?: PROJECT_FOLDER . 'configs/routes.yaml';
        if (!file_exists($routesFile)) {
            return false;
        }
        $routes = array();
        
        $ext  = pathinfo($routesFile, PATHINFO_EXTENSION);
        $info = pathinfo($routesFile);
        switch ($ext) {
        case 'json':
            $routes = json_decode(file_get_contents($routesFile), true);
            break;
        case 'yaml':
            if (function_exists('yaml_parse_file')) {
                $routes = \yaml_parse_file($routesFile);
            } else if (class_exists('\sfYamlParser')) {
                $yaml   = new \sfYamlParser();
                $routes = $yaml->parse(file_get_contents($routesFile));
            }
        }
        if (!array_key_exists('routes', $routes)) {
            $routes['routes'] = array();
        }
        if (!array_key_exists('controllers', $routes)) {
            $routes['controllers'] = array();
        }
        $this->_routes = $routes;
    }
    
    public function resolve($request)
    {
        //Setup and split the query
        if ($routePath = $this->checkDefinedRoutes($request)) {
            return $routePath;
        }
        return $this->checkGeneratedRoutes($request);
    }
    
    function checkGeneratedRoutes($request) {
        $query = explode('/', ltrim($request->getQuery(), '/'));
        
        //Resolve the controller
        $controller = $query[0];
        if (array_key_exists($controller, $this->_routes['controllers'])) {
            $controller  = $this->_routes['controllers'][$controller];
        } else {
            $controller = Utilities\Strings::className($query[0]);
        }
        
        $action = strtolower($request->getMethod());
        if ($action == 'get' && count($query) == 1) {
            $action = 'list';
        }
        
        return new Utilities\RoutePath(
            $controller,
            $action,
            count($query) > 1 ? array_slice($query, 1) : array()
        );
    }
    
    function checkDefinedRoutes($request)
    {
        $query = $request->getQuery();
        foreach($this->_routes['routes'] as $name => $routeInfo) {
            //If the verb is defined, and it doesn't match, skip
            if (
                array_key_exists('_verb', $routeInfo) &&
                $request->getMethod() != strtoupper($routeInfo['_verb'])
            ) {
                continue;
            }
            //Try to match the route
            if ($routeInfo['route'] == $query) {
                return new Utilities\RoutePath(
                    $routeInfo['controller'],
                    $routeInfo['action'],
                    array() //TODO
                );
            } //TODO Add regex support
        }
        return false;
    }
}
