<?php
/**
 * File defining UncallableMethodException
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
 * The Base View class
 *
 * @package CoreFiles
 */
class BEView
{
    /**
     * This contains the variables bound to the view
     * @var array
     */
    protected $_variables = array();

    /**
     * The View constructor
     */
    function __construct()
    {
    }

    /**
     * Bind a variable to the view
     *
     * @param string The name of the variable
     * @param mixed The value of the variable
     */
    function bind($name, $value)
    {
        $this->_variables[$name] = $value;
    }

    /**
     * Output the request.
     *
     * This function should be overwritten by other views to change the output
     */
    function output()
    {
        if (from_cli()) {
        } else {
            echo <<< END
<!DOCTYPE HTML>
<html>
    <head>
        <title>Backend-Core</title>
    </head>
    <body>
END;
        }
        var_dump('Result', $this->_result);
        if (from_cli()) {
            echo PHP_EOL;
        } else {
            echo <<< END
    </body>
</html>
END;
        }
    }
}
