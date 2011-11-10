<?php
namespace Backend\Models;
/**
 * File defining HomeModel
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
 * @package ApplicationFiles
 */
/**
 * The main controller class.
 *
 * @package Application
 */
class Homes extends \Backend\Core\Model implements \Backend\Core\Interfaces\RestModel
{
    /**
     * Create an instance of the model on the source
     *
     * @param mixed The identifier to use when creating the instance on the source
     * @param array Any additional arguments to pass when creating the instance
     */
    public function createAction($identifier, array $arguments = array())
    {
        return true;
    }

    /**
     * Read the model from the source
     *
     * @param mixed The identifier to use when reading the instance from the source
     * @param array Any additional arguments to pass when readin the instance
     */
    public function readAction($identifier, array $arguments = array())
    {
        return true;
    }

    /**
     * Update the model on the source
     *
     * @param mixed The identifier to use when update the instance on the source
     * @param array Any additional arguments to pass when updating the instance
     */
    public function updateAction($identifier, array $arguments = array())
    {
        return true;
    }

    /**
     * Delete the model from the source
     *
     * @param mixed The identifier to use when deleting the instance from the source
     * @param array Any additional arguments to pass when deleting the instance
     */
    public function deleteAction($identifier, array $arguments = array())
    {
        return true;
    }
}
