<?php
/**
 * File defining Backend\Core\Utilities\DependencyInjectionContainer
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Utilities
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core\Utilities;
use Backend\Interfaces\DependencyInjectionContainerInterface;
use Backend\Interfaces\ConfigInterface;
use Backend\Core\Exception as CoreException;
use Backend\Core\Exceptions\ConfigException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
/**
 * A Dependency Injection Container. Currently we just wrap the Symfony
 * DependencyInjection Component
 *
 * @category Backend
 * @package  Utilities
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class DependencyInjectionContainer extends ContainerBuilder
    implements DependencyInjectionContainerInterface
{
    protected $container = null;

    /**
     * The object constructor.
     *
     * @param Backend\Interfaces\ConfigInterface|array $config The config to check
     * service and parameter definitions as a Config object or an array.
     */
    public function __construct($config = array())
    {
        if ($config instanceof ConfigInterface) {
            $config = $config->get();
        } elseif (is_object($config)) {
            $config = (array) $config;
        } elseif (is_array($config) === false) {
            throw new ConfigException('Invalid DIC Configuration');
        }
        parent::__construct();

        $this->container = new ContainerBuilder();
        //Parameters
        $parameters = empty($config['parameters']) ? array() : $config['parameters'];
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
        //Services
        $services = empty($config['services']) ? array() : $config['services'];
        foreach ($services as $id => $implementation) {
            $this->addComponent($id, $implementation);
        }
    }

    /**
     * Utility function to add Copmonents to the container.
     *
     * @param string $id     The component identifier.
     * @param mixed  $config The component definition.
     *
     * @return void
     * @todo  This currently only implements a small subset of the Symfony
     * DI Component. Extend it.
     */
    protected function addComponent($id, $config)
    {
        if (is_string($config)) {
            $config = array('class' => $config);
        }
        $defaults = array(
            'arguments' => array(), 'calls' => array()
        );
        $config += $defaults;

        if (empty($config['class'])) {
            throw new ConfigException('Invalid Service Definition for ' . $id);
        }

        $definition = $this->register($id, $config['class']);
        if (empty($config['factory_class']) === false
            && empty($config['factory_method']) === false
        ) {
            $definition->setFactoryClass($config['factory_class']);
            $definition->setFactoryMethod($config['factory_method']);
        }
        foreach ($config['calls'] as $name => $arguments) {
            foreach($arguments as &$argument) {
                if (substr($argument, 0, 1) === '@') {
                    $argument = new Reference(substr($argument, 1));
                }
            }
            $definition->addMethodCall($name, $arguments);
        }
        foreach ($config['arguments'] as $value) {
            if (is_string($value) && substr($value, 0, 1) === '@') {
                $definition->addArgument(new Reference(substr($value, 1)));
            } else {
                $definition->addArgument($value);
            }
        }
    }

    /**
     * Get the Implementation of the specified Component.
     *
     * @param string  $id               The Component identifier.
     * @param integer $invalidBehaviour The behavior when the service does not exist.
     *
     * @return object
     * @throws \Backend\Core\Exception
     */
    public function get($id,
        $invalidBehaviour = ContainerInterface::IGNORE_ON_INVALID_REFERENCE
    ) {
        if (parent::has($id)) {
            return parent::get($id, $invalidBehaviour);
        } else {
            throw new CoreException('Undefined Implementation for ' . $id);
        }
    }

    /**
     * Register an Implementation of the specified Component.
     *
     * @param string $id      The component identifier.
     * @param object $service The component to register.
     * @param int    $scope   The scope of the component.
     *
     * @return object
     * @throws \Backend\Core\Exception
     */
    public function set($id, $service, $scope = ContainerInterface::SCOPE_CONTAINER)
    {
        return parent::set($id, $service, $scope);
    }

    /**
     * Check if the specified Component has been registered with the container.
     *
     * @param string $id The Component identifier.
     *
     * @return boolean
     */
    public function has($id)
    {
        return parent::has($id);
    }

    /**
     * Get the specified Parameter.
     *
     * @param string $name The Parameter name.
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return parent::getParameter($name);
    }

    /**
     * Set the value of the specified Parameter.
     *
     * @param string $name  The Parameter name.
     * @param mixed  $value The value of the Parameter.
     *
     * @return void
     */
    public function setParameter($name, $value)
    {
        return parent::setParameter($name, $value);
    }

    /**
     * Check if the specified Parameter has been registered with the container.
     *
     * @param string $name The Parameter name.
     *
     * @return boolean
     */
    public function hasParameter($name)
    {
        return parent::hasParameter($name);
    }

    /**
     * Remove the Implementation of the specified Component.
     *
     * @param string $id The identifier of the Component to remove.
     *
     * @return void
     */
    public function removeDefinition($id)
    {
        return parent::removeDefinition($id);
    }
}
