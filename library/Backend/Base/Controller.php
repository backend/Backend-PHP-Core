<?php
namespace Backend\Base;
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
 * @package BaseFiles
 */
/**
 * The main controller class.
 *
 * @package Base
 */
class Controller extends \Backend\Core\Controller
{
    /**
     * The main controller function
     *
     * Any Application logic can be put into this function
     * @param string The action the controller should execute
     * @param mixed The identifier that should be passed to the executing function
     * @param array The extra arguments that should be passed to the executing function
     * @return mixed The result of the execution
     */
    public function output($result)
    {
        //Get all application values
        $config = \Backend\Core\Application::getTool('Config');
        $values = $config->get('application', 'values');
        if ($values) {
            foreach ($values as $name => $value) {
                $this->_viewObj->bind($name, $value);
            }
        }
        return parent::output($result);
    }
}
