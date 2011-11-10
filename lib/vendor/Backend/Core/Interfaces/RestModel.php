<?php
namespace Backend\Core\Interfaces;
/**
 * File defining iRestModel
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
 * @package InterfacesFiles
 */
/**
 * A Model class that provides basic REST rest functions
 *
 * @package Interfaces
 */
interface RestModel extends ModelInterface
{
    /**
     * Create function called by the POST HTTP verb
     *
     * @param mixed The identifier. Set to 0 to reference the collection
     */
    public function createAction($identifier, array $arguments = array());

    /**
     * Read function called by the GET HTTP verb
     *
     * @param mixed The identifier. Set to 0 to reference the collection
     */
    public function readAction($identifier, array $arguments = array());

    /**
     * Update function called by the PUT HTTP verb
     *
     * @param mixed The identifier. Set to 0 to reference the collection
     */
    public function updateAction($identifier, array $arguments = array());

    /**
     * Delete function called by the DELETE HTTP verb
     *
     * @param mixed The identifier. Set to 0 to reference the collection
     */
    public function deleteAction($identifier, array $arguments = array());
}
