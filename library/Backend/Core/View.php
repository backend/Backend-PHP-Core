<?php
namespace Backend\Core;
/**
 * File defining Core\View
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
 * The Base View class.
 *
 * @package Core
 */
class View
{
    /**
     * This contains the variables bound to the view
     * @var array
     */
    protected $_variables = array();

    /**
     * Object to render the view with
     * @var object
     */
    protected $_renderer = null;

    /**
     * Define the formats this view can handle
     * @var array
     */
    public static $handledFormats = array();

    /**
     * The View constructor
     */
    function __construct($renderer = null)
    {
        $this->_renderer = $renderer;
    }

    /**
     * Bind a variable to the view
     *
     * @param string The name of the variable
     * @param mixed The value of the variable
     * @param boolean Set to false to honor previously set values
     */
    function bind($name, $value, $overwrite = true)
    {
        if ($overwrite || !array_key_exists($name, $this->_variables)) {
            $this->_variables[$name] = $value;
        }
        return $this->_variables[$name];
    }

    /**
     * Get the value of a variable
     *
     * @param string The name of the variable
     * @return mixed The value of the variable
     */
    function get($name)
    {
        return array_key_exists($name, $this->_variables) ? $this->_variables[$name] : null;
    }

    /**
     * Get all of the bound variables
     *
     * @return array An array of all the variables bound to the view
     */
    function getVariables()
    {
        return $this->_variables;
    }

    /**
     * Redirect the application to another location
     *
     * Views are welcome to overwrite this to customize the way we redirect
     *
     * @param string The new location
     */
    function redirect($location)
    {
        header('X-Redirector: ' . get_class($this));
        header('Location: ' . $location);
        die;
    }

    function render($template, array $values = array())
    {
        $values = array_merge($this->getVariables(), $values);
        if (is_null($this->_renderer)) {
            $this->_renderer = \Backend\Core\Application::getTool('Render');
            $this->_renderer->setView($this);
        }
        //Render it
        return $this->_renderer->file($template, $values);
    }

    /**
     * Output the request.
     *
     * This function should be overwritten by other views to change the output
     */
    function transform(\Backend\Core\Response $response)
    {
        if (Request::fromCli()) {
            var_export($response->getContent());
        } else {
            echo <<< END
<!DOCTYPE HTML>
<html>
    <head>
        <title>Backend-Core</title>
    </head>
    <body>
END;
            var_dump('Result', $response->getContent());
        }
        if (Request::fromCli()) {
            echo PHP_EOL;
        } else {
            echo <<< END
    </body>
</html>
END;
        }
    }
}
