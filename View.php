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
     * Define the formats this view can handle
     * @var array
     */
    public static $handledFormats = array();

    /**
     * The View constructor
     */
    function __construct()
    {
    }

    /**
     * Transform the result into a Response Object.
     *
     * This function should be overwritten by other views to change the output
     */
    function transform($result)
    {
        if ($result instanceof \Backend\Core\Response) {
            return $result;
        }
        $response = new \Backend\Core\Response();
        $response->addHeader('X-Backend-View', get_class($this));
        $body  = '';
        switch (gettype($result)) {
        case 'object':
            if ($result instanceof \Exception) {
                $result = new Decorators\PrettyExceptionDecorator($result);
                $result = (string)$result;
            }
            //NO break;
        case 'array':
            $result = var_export($result, true);
            //NO break;
        case 'string':
        default:
            $body = 'Result: ' . $result;
            break;
        }

        //Add some default formatting
        if (!Request::fromCli()) {
            $header = <<< END
<!DOCTYPE HTML>
<html>
    <head>
        <title>Backend-Core</title>
    </head>
    <body>
        <pre>
END;
            $footer = <<< END
        </pre>
    </body>
</html>
END;
            $body = $header . PHP_EOL . $body . PHP_EOL . $footer;
        } else {
            $body .= PHP_EOL;
        }
        $response->setBody($body);
        return $response;
    }
}
