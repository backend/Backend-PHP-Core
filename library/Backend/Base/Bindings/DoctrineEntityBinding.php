<?php
namespace Backend\Base\Bindings;
/**
 * File defining DoctrineEntityBinding
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
 * @package BindingFiles
 */
/**
 * Binding for Doctrine connections.
 *
 * This class assumes that you installed Doctrine using PEAR.
 * @todo Most of the functions in this class hasn't been implemented yet. We're still testing.
 *
 * @package Bindings
 */
class DoctrineEntityBinding extends DoctrineBinding
{
    protected $_entityName;

    protected $_repository;

    public function __construct($entityName, $bindingName = 'default')
    {
        parent::__construct($bindingName);
        $this->_entityName = is_object($entityName) ? get_class($entityName) : $entityName;
        $this->_repository = $this->_manager->getRepository($this->_entityName);
    }

    public function find()
    {
    }

    public function create($identifier, $data)
    {
    }

    public function read($identifier)
    {
        return $this->_manager->find($this->_entityName, $identifier);
    }

    public function update($identifier, $data)
    {
    }

    public function delete($identifier)
    {
    }

    public function fieldNames()
    {
        //The repository won't grant us access to the meta data, so do it the old fashioned way.
        $query = $this->_manager->createQuery('SELECT e FROM ' . $this->_entityName . ' e');
        //It will probably also fail with an empty table
        $query->setMaxResults(1);
        $results = $query->getArrayResult();
        $row = reset($results);
        return array_keys($row);
    }
}
