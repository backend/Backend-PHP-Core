<?php
namespace Backend\Base\Bindings;
/**
 * File defining \Base\MySQLBinding
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
 * Binding for MySQL Tables
 *
 * @package Bindings
 */
class MySQLBinding extends DatabaseTableBinding
{
    /**
     * Bind to the specified instance of the resource
     *
     * @param mixed The identifier of the instance to bind to
     * @return MySQLBinding The current binding
     */
    public function bind($identifier = null)
    {
        $identifier = $this->checkIdentifier($identifier);
        if (is_null($identifier)) {
            return null;
        }
        $this->_resource = $this->read($identifier);
        return $this;
    }

    /**
     * Find records in the MySQL table according to the specified criteria
     *
     * @todo Create this
     */
    public function find()
    {
        //Reset the list
        $this->_list = null;
        $query = 'SELECT * FROM ' . $this->_table;
        $stmt  = $this->_connection->prepare($query);
        if ($stmt->execute()) {
            $this->_list = $stmt;
        }
        return $this->_list;
    }

    /**
     * Create a record in the MySQL table using the data provided
     *
     * @todo Create this
     */
    public function create($identifier, $data)
    {
    }

    /**
     * Read a record from the MySQL Table and store it in @_resource
     *
     * @param mixed The identifier
     * @todo This is a very basic implementation. Flesh it out.
     */
    public function read($identifier = null)
    {
        //Check if we can return the current resource
        if (
            !is_null($this->_resource)
            && (is_null($identifier) || $identifier == $this->getIdentifier())
        ) {
            return $this->_resource;
        }
        //Reset the resource
        $this->_resource = null;
        $identifier = $this->checkIdentifier($identifier);
        if (is_null($identifier)) {
            return null;
        }
        //Read from the table
        $query = 'SELECT * FROM ' . $this->_table . ' WHERE `id` = :id';
        $stmt  = $this->_connection->prepare($query);
        $stmt->execute(array(':id' => $identifier));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->_resource = $result;
        }
        return $this->_resource;

    }

    /**
     * Update the record in the MySQL table with the current @_resource
     *
     * @todo This hasn't been implemented yet. Implement it.
     */
    public function update($data)
    {
        $identifier = $this->checkIdentifier();
        if (!$this->isBound()) {
            return null;
        }
        if (!$this->_modified) {
            return true;
        }
        //TODO Update using the data currently in resource

        //Succesful update
        $this->_modifed = false;
        return $this;
    }

    /**
     * Delete a record from the MySQL Table
     *
     * @param mixed The identifier
     * @todo This is a very basic implementation. Flesh it out.
     */
    public function delete()
    {
        if (!$this->isBound()) {
            return null;
        }
        $query = 'DELETE FROM ' . $this->_resource . ' WHERE `id` = :id';
        $stmt  = $this->_connection->prepare($query);
        if (!$stmt->execute(array(':id' => $identifier))) {
            return false;
        }
        $this->_resource = null;
        return true;
    }

}
