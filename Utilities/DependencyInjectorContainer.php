<?php
/**
 * File defining Backend\Core\Utilities\DependencyInjectorContainer
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
use Backend\Interfaces\DependencyInjectorContainerInterface;
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
class DependencyInjectorContainer extends ContainerBuilder
    implements DependencyInjectorContainerInterface
{
    protected $container = null;

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
        foreach ($services as $component => $implementation) {
            $this->addComponent($component, $implementation);
        }
    }

    protected function addComponent($component, $config)
    {
        if (is_string($config)) {
            $config = array('class' => $config);
        }
        $defaults = array(
            'arguments' => array(), 'calls' => array()
        );
        $config += $defaults;

        $definition = $this->register($component, $config['class']);
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
     * Remove the Implementation of the specified Component.
     *
     * @param string $component The name of the Component to remove.
     *
     * @return void
     */
    public function remove($component)
    {
        parent::removeDefinition($component);
    }

    /**
     * Get the Implementation of the specified Component.
     *
     * @param string $component The name of the Component to get.
     *
     * @return object
     * @throws \Backend\Core\Exception
     */
    public function get($component,
        $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (parent::has($component)) {
            return parent::get($component, $invalidBehavior);
        } else {
            throw new CoreException('Undefined Implementation for ' . $component);
        }
    }
}
