<?php
namespace Backend\Base\Models;
/**
 * File defining \Base\BoundModel
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
 * @package ModelFiles
 */
/**
 * Abstract class for models that are bound to a specific binding
 *
 * @package Models
 */
abstract class BoundModel extends \Backend\Core\Model
{
    /**
     * @define Shows that the model hasn't been bound or that any attributes have been created or updated
     */
    const BOUND_MODEL_EMPTY   = 1;

    /**
     * @define Shows that the model hasn't been bound, but that attributes have been created
     */
    const BOUND_MODEL_NEW     = 2;

    /**
     * @define Shows that the model has been bound, and the attributes are in sync with the binding
     */
    const BOUND_MODEL_SYNCED  = 3;

    /**
     * @define Shows that the model was bound, and that attributes have been updated
     */
    const BOUND_MODEL_UPDATED = 4;

    /**
     * @var Binding The binding for the model
     */
    protected $_binding = null;

    protected $_state = self::BOUND_MODEL_EMPTY;

    /**
     * The constructor for the class
     *
     * @param Binding The binding for the model
     */
    public function __construct(Bindings\Binding $binding = null)
    {
        $this->_binding = $binding;
    }

    public function __get($propertyName)
    {
        if ($this->_state == self::BOUND_MODEL_EMPTY) {
            //TODO Use a specific exception
            throw new Exception('Trying to get the property of an empty Model');
        }
        return parent::__get($propertyName);
    }

    public function __set($propertyName, $value)
    {
        switch ($this->_state) {
        case self::BOUND_MODEL_SYNCED:
            $this->_state = self::BOUND_MODEL_UPDATED;
            break;
        case self::BOUND_MODEL_EMPTY:
            $this->_state = self::BOUND_MODEL_NEW;
            break;
        }
        return parent::__set($propertyName, $value);
    }

    /**
     * Return the model's current identifier.
     *
     * This assumes that the model has an identifier field called "id".
     * Overwrite this function if your model behaves differently
     * @return mixed The model's identifier
     */
    public function getIdentifier()
    {
        if (array_key_exists('id', $this->_attributes)) {
            return $this->_attributes['id'];
        }
        return null;
    }

    /**
     * Set the model's attributes all in one go
     *
     * @param array An associactive array containing the attributes to set
     * @param boolean Set this to true to remove previously set attributes
     */
    public function setAttributes(array $attributes, $overwrite = false)
    {
        if ($overwrite) {
            $this->_attributes = array_merge($this->_attributes, $attributes);
        } else {
            $this->_attributes = $attributes;
        }
        switch ($this->_state) {
        case self::BOUND_MODEL_SYNCED:
            $this->_state = self::BOUND_MODEL_UPDATED;
            break;
        case self::BOUND_MODEL_EMPTY:
            $this->_state = self::BOUND_MODEL_NEW;
            break;
        }
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Return the Model's Binding.
     *
     * @return Binding The Model's Binding
     */
    public function getBinding()
    {
        if (is_null($this->_binding)) {
            //TODO Use a specific Exception
            throw new \Exception('Trying to get a non-existant Binding');
        }
        return $this->_binding;
    }

    /**
     * Set the Model's Binding.
     *
     * @param Binding The Request Method
     */
    public function setBinding(\Backend\Base\Bindings\Binding $binding)
    {
        $this->_binding = $binding;
    }

    /**
     * Create an instance of the model on the binding
     *
     * The model needs to be in the {@link BOUND_MODEL_NEW} state to run this function,
     * and will be in the {@link BOUND_MODEL_SYNCED} state directly after creation.
     *
     * @param mixed The identifier to use when creating the instance on the binding
     */
    public function create()
    {
        if ($this->_state != self::BOUND_MODEL_NEW) {
            return null;
        }
        $binding = $this->getBinding();
        return $binding->create($this->_attributes);
    }

    /**
     * Read the model from the binding
     *
     * The model will be in the {@link BOUND_MODEL_SYNCED} state directly after reading.
     *
     * @param mixed The identifier to use when reading the instance from the binding
     * @param array Any additional arguments to pass when readin the instance
     */
    public function read($identifier = null)
    {
        $identifier = is_null($identifier) ? $this->getIdentifier() : $identifier;
        if (empty($identifier)) {
            //TODO Use a specific Exception
            throw new \Exception('Trying to read an unidentified Model');
        }
        $binding = $this->getBinding();
        if ($result = $binding->read($identifier)) {
            $this->_attributes = $result;
            $this->_state = self::BOUND_MODEL_SYNCED;
            return $result;
        }
        //TODO Use a specific Exception
        throw new \Exception('Model not Read');
    }

    /**
     * Update the model on the binding
     *
     * The model needs to be in the {@link BOUND_MODEL_UPDATED} state to run this function,
     * and will be in the {@link BOUND_MODEL_SYNCED} state directly after updating.
     *
     * @param mixed The identifier to use when update the instance on the binding
     * @param array Any additional arguments to pass when updating the instance
     */
    public function update()
    {
        if ($this->_state != self::BOUND_MODEL_UPDATED) {
            return null;
        }
        $binding = $this->getBinding();
        if ($result = $binding->update($this->getIdentifier(), $this->_attributes)) {
            $this->_state = self::BOUND_MODEL_SYNCED;
            return $result;
        }
        //TODO Use a specific Exception
        throw new \Exception('Model not Updated');
    }

    /**
     * Delete the model from the binding
     *
     * The model needs to be in the {@link BOUND_MODEL_UPDATED} or {@link BOUND_MODEL_SYNCED}
     * state to run this function, and will be in the {@link BOUND_MODEL_EMPTY} state
     * directly after deletion.
     *
     * @param mixed The identifier to use when deleting the instance from the binding
     * @param array Any additional arguments to pass when deleting the instance
     */
    public function delete()
    {
        if (!in_array($this->_state, array(self::BOUND_MODEL_UPDATED, self::BOUND_MODEL_SYNCED))) {
            return null;
        }
        $binding = $this->getBinding();
        if ($result = $binding->delete($this->getIdentifier())) {
            $this->_attributes = array();
            $this->_state = self::BOUND_MODEL_EMPTY;
        }
        //TODO Use a specific Exception
        throw new \Exception('Model not Deleted');
    }
}
