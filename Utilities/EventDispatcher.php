<?php
/**
 * File defining Backend\Core\Utilities\EventDispatcher.
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Utilities;

/**
 * An EventDispatcher to manage events and their listeners.
 *
 * @category   Backend
 * @package    Core 
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 * @todo       Implement the EventDispatcherInterface
 */
use Backend\Interfaces\DependencyInjectionContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Backend\Interfaces\EventInterface;
use Backend\Core\Exceptions\ConfigException;

class EventDispatcher extends SymfonyEventDispatcher
{
    /**
     * The DI Container used to get and add registered listeners.
     *
     * @var Backend\Interfaces\DependencyInjectionContainerInterface
     */
    private $container;

    /**
     * DI Container
     *
     * @param Backend\Interfaces\DependencyInjectionContainerInterface $container
     * The DI Container for the Dispatcher.
     */
    public function __construct(DependencyInjectionContainerInterface $container)
    {
        $this->container = $container;

        // Register the Listeners
        $this->registerListeners();
    }

    protected function registerListeners()
    {
        $listeners = $this->container->findTaggedServiceIds('core.listener');
        foreach ($listeners as $serviceId => $events) {
            foreach ($events as $tag) {
                if (empty($tag['event'])) {
                    throw new ConfigException('No Event specified for core.listener on Service ' . $id);
                }
                if (empty($tag['method'])) {
                    $tag['method'] = $this->getMethod($tag['event']);
                }

                // TODO Lazy Loading of service ?
                $callback = array($this->container->get($serviceId), $tag['method']);
                $priority = array_key_exists('priority', $tag) ? $tag['priority'] : 0;

                $this->addListener($tag['event'], $callback, $priority);
            }
        }
    }

    /**
     * Transform the event name into a method name.
     *
     * @param string $event The event name.
     *
     * @return string The deduced method name.
     */
    protected function getMethod($event)
    {
        return lcfirst(
            str_replace(
                ' ', '', ucwords(str_replace(array('.', ',', ':'), ' ', $event))
            )
        ) . 'Event';
    }

    public function dispatch($eventName, Event $event = null)
    {
        $result = parent::dispatch($eventName, $event);
        return $result;
    }
}
