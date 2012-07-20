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
     * @param Backend\Interfaces\ConfigInterface $config The configuration file
     * to check for service and parameter definitions.
     */
    public function __construct(ConfigInterface $config)
    {
        parent::__construct();
        //Services
        $services = $config->get('services');
        if (empty($services)) {
            throw new CoreException('Could not set up Services');
        }

        $this->container = new ContainerBuilder();
        $parameters = $config->get('parameters', array());
        foreach($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
        foreach ($services as $id => $implementation) {
            $this->addComponent($id, $implementation);
        }
    }

    /**
     * Utility function to add Copmonents to the container.
     *
     * @param string $id      The component identifier.
     * @param mixed  $config  The component definition.
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

        $definition = $this->register($id, $config['class']);
        if (empty($config['factory_class']) === false
            && empty($config['factory_method']) === false
        ) {
            $definition->setFactoryClass($config['factory_class']);
            $definition->setFactoryMethod($config['factory_method']);
        }
        foreach($config['calls'] as $name => $value) {
            $definition->addMethodCall($name, $value);
        }
        foreach($config['arguments'] as $value) {
            if (substr($value, 0, 1) === '@') {
                $definition->addArgument(new Reference(substr($value, 1)));
            } else {
                $definition->addArgument($value);
            }
        }

    }

    /**
     * Get the Implementation of the specified Component.
     *
     * @param string $id The Component identifier.
     * @param integer $invalidBehaviour The behavior when the service does not exist.
     *
     * @return object
     * @throws \Backend\Core\Exception
     */
    public function get($id,
        $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
    {
        if (parent::has($id)) {
            return parent::get($id, $invalidBehavior);
        } else {
            throw new CoreException('Undefined Implementation for ' . $id);
        }
    }
}
