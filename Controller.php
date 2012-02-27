<?php
namespace Backend\Core;
/**
 * File defining Controller
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
 * The main controller class.
 *
 * @package Core
 */
class Controller extends Decorable implements Interfaces\ControllerInterface
{
    /**
     * @var Route This contains the route object that will help decide what controller
     * and action to execute
     */
    protected $_route = null;

    /**
     * @var Request This contains the Request that's being actioned
     */
    protected $_request = null;

    /**
     * The constructor for the class
     *
     * @param Request The request object for the execution of the action
     */
    function __construct(Request $request = null)
    {
        //Setup the request
        $this->_request = $request;
    }

    public function setRequest(Request $request)
    {
        $this->_request = $request;
    }

    /**
     * Create a redirection Response
     *
     * @param string $location The location to redirect to
     * @param int $responseCode The HTTP status code to use
     *
     * @return Response The Response object
     */
    public function redirect($location, $responseCode = 302)
    {
        $response = new Response('Redirecting to ' . $location, $responseCode);
        $response->addHeader('Location', $location);
        return $response;
    }

    /**
     * @return ModelInterface The model associated with this controller
     */
    public function getModel()
    {
        //Get and check the model
        $modelName = 'Backend\Models\\' . class_name($this->_route->getArea());
        if (!class_exists($modelName, true)) {
            return null;
        }
        $model = new $modelName();
        if ($model instanceof Interfaces\Decorable) {
            foreach ($model->getDecorators() as $decorator) {
                $model = new $decorator($model);
                if (!($model instanceof \Backend\Core\Decorators\ModelDecorator)) {
                    //TODO Use a specific Exception
                    throw new \Exception(
                        'Class ' . $decorator . ' is not an instance of \Backend\Core\Decorators\ModelDecorator'
                    );
                }
            }
        }
        return $model;
    }
}
