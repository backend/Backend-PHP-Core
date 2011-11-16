<?php
/**
 * File defining Core\Decorators\ControllerDecorator
 *
 * Copyright (c) 2011 JadeIT cc
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
 * @package   DecoratorFiles
 * @author    "J Jurgens du Toit" <jrgns@jadeit.co.za>
 * @copyright 2011 JadeIT cc
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Backend\Core\Decorators;
/**
 * Abstract base class for Model decorators
 *
 * @package   Decorators
 * @author    "J Jurgens du Toit" <jrgns@jadeit.co.za>
 * @copyright 2011 JadeIT cc
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */
abstract class ControllerDecorator
    extends \Backend\Core\Controller
        implements \Backend\Core\Interfaces\ControllerInterface, \Backend\Core\Interfaces\Decorator
{
    /**
     * @var ControllerInterface The controller this class is decorating
     */
    protected $_decoratedController;

    /**
     * The constructor for the class
     *
     * @param Decorable $controller The controller to decorate
     * @param Response  $response   The reponse for the controller
     */
    function __construct(\Backend\Core\Interfaces\Decorable $controller, Response $response = null)
    {
        $this->_decoratedController = $controller;
        parent::__construct($response);
    }

    public function __call($method, $args) {
        return call_user_func_array(
            array($this->decoratedController, $method),
            $args
        );
    }
}
