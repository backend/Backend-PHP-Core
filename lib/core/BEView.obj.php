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
     * This contains the controller the view is acting on
     * @var BEController
     */
    protected $_controller;

    /**
     * This contains the model the view is acting on
     * @var BEModel
     */
    protected $_model;

    /**
     * This contains the result the view is acting on
     * @var mixed
     */
    protected $_result;

    function __construct(BEController $controller, BEModel $model, $result)
    {
        $this->_controller = $controller;
        $this->_model      = $model;
        $this->_result     = $result;
    }

    function display()
    {
        if (empty($_SERVER['REQUEST_METHOD'])) {
        } else {
            $controllerType = get_class($this->_controller);
            $modelType      = get_class($this->_model);
            echo <<< END
<!DOCTYPE HTML>
<html>
    <head>
        <title>$controllerType::$modelType</title>
    </head>
    <body>
END;
        }
        var_dump('Result', $this->_result);
        if (empty($_SERVER['REQUEST_METHOD'])) {
        } else {
            echo <<< END
    </body>
</html>
END;
        }
    }
}
