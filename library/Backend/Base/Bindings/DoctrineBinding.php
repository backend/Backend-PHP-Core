<?php
namespace Backend\Base\Bindings;
/**
 * File defining DoctrineBinding
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
 *
 * @package Bindings
 */
abstract class DoctrineBinding extends Binding
{
    protected $_manager;

    public function __construct($bindingName = 'default')
    {
        // Get the DB configuration parameters
        $config = \Backend\Core\Application::getTool('Config');
        $config = $config->get('database', $bindingName);
        if (empty($config)) {
            throw new \Exception('Unknown Doctrine Setup: ' . $bindingName);
        }
        if (empty($config['connection'])) {
            throw new \Exception('No Doctrine Connection Setup for ' . $bindingName);
        }
        $connection = $config['connection'];

        //Get the entities Folder
        $entitiesFolder = array_key_exists('entitiesFolder', $config) ?
            $entitiesFolder :
            PROJECT_FOLDER . 'configs/entities';
        if (!is_array($entitiesFolder)) {
            $entitiesFolder = array($entitiesFolder);
        }

        //Setup Doctrine
        require_once "Doctrine/ORM/Tools/Setup.php";
        \Doctrine\ORM\Tools\Setup::registerAutoloadPEAR();
        $isDevMode      = (SITE_STATE != 'production');
        $doctrineConfig = \Doctrine\ORM\Tools\Setup::createYAMLMetadataConfiguration(
            $entitiesFolder,
            $isDevMode
        );

        // obtaining the entity manager
        $this->_manager = \Doctrine\ORM\EntityManager::create($connection, $doctrineConfig);
    }
}
