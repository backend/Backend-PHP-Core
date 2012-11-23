<?php
/**
 * File defining \Backend\Core\Listener\CoreListener
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Listeners
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Listener;

use Backend\Core\Exception as CoreException;
use Backend\Interfaces\DependencyInjectionContainerInterface;
use Backend\Interfaces\ResponseInterface;
use Backend\Interfaces\CallbackInterface;

/**
 * The Core Listener.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Listeners
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class CoreListener
{
    /**
     * The DI Container for the Listener
     *
     * @var \Backend\Interfaces\DependencyInjectionContainerInterface
     */
    private $container;

    /**
     * The object constructor.
     *
     * @param \Backend\Interfaces\DependencyInjectionContainerInterface $container
     * The DI Container.
     */
    public function __construct(DependencyInjectionContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Method to handle core.init Events.
     *
     * It starts an output buffer.
     *
     * @param  \Symfony\Component\EventDispatcher\Event $event The event to handle.
     * @return void
     */
    public function coreInitEvent(\Symfony\Component\EventDispatcher\Event $event)
    {
        ob_start();
    }

    /**
     * Method to handle core.callback Events.
     *
     * It applies a couple of transforms on the object, ensuring consistency.
     *
     * @param  \Backend\Core\Event\CallbackEvent $event The event to handle
     * @return void
     */
    public function coreCallbackEvent(\Backend\Core\Event\CallbackEvent $event)
    {
        $callback = $event->getCallback();
        $event->setCallback($this->transformCallback($callback));
    }

    /**
     * Method to handle core.result Events.
     *
     * It will try to format the result.
     *
     * @param  \Backend\Core\Event\CallbackEvent $event The event to handle
     * @return void
     */
    public function coreResultEvent(\Backend\Core\Event\ResultEvent $event)
    {
        // Get and Check the initial callback
        $request  = $this->container->get('request');
        $callback = $this->container->get('router')->inspect($request);
        if (empty($callback)
            || ($callback instanceof CallbackInterface) === false
        ) {
            return;
        }
        // Transform the Controller
        $callback = $this->transformCallback($callback);

        // Check the Method
        $method = $callback->getMethod();
        if (empty($method)) {
            return;
        }

        $result = $event->getResult();

        $formatter = $this->container->get('formatter');
        if (empty($formatter)) {
            if ($result instanceof ResponseInterface) {
                $event->setResponse($result);
                return;
            }
            throw new CoreException('Unsupported format requested', 415);
        }

        // Setup the formatting callback
        $class = get_class($formatter);
        $class = explode('\\', $class);
        $method = str_replace('Action', end($class), $method);
        $callback->setMethod($method);

        // Execute
        try {
            $result = $callback->execute(array($result));
        } catch (CoreException $e) {
            // If the callback is invalid, it won't be called, result won't change
        }

        // TODO This isn't optimal
        if (ob_get_level() > 1 && in_array('ob_gzhandler', ob_list_handlers()) === false) {
            $buffered = ob_get_clean();
            if (is_callable(array($formatter, 'setValue'))) {
                $formatter->setValue('buffered', $buffered);
            }
        }
        $response = $formatter->transform($result);

        $event->setResponse($response);
    }

    /**
     * Transform the Callback.
     *
     * Transform any ControllerInterface classes into objects. Add Action to
     * ControllerInterface methods.
     *
     * @param  \Backend\Interfaces\CallbackInterface $callback The callback to transform.
     * @return \Backend\Interfaces\CallbackInterface The transformed callback.
     */
    private function transformCallback(CallbackInterface $callback)
    {
        //Transform the callback a bit if it's a controller
        $class = $callback->getClass();
        if ($class) {
            // Check for a ControllerInterface, and adjust accordingly
            $interfaces = class_implements($class);
            $implements = array_key_exists(
                'Backend\Interfaces\ControllerInterface',
                $interfaces
            );
            if ($implements === true) {
                $controller = new $class(
                    $this->container,
                    $this->container->get('request')
                );
                $callback->setObject($controller);
                //Set the method name as actionAction
                if (substr($callback->getMethod(), -6) !== 'Action') {
                    $callback->setMethod($callback->getMethod() . 'Action');
                }
            }
        }
        return $callback;
    }

    /**
     * Get the DI Container.
     *
     * @return \Backend\Interfaces\DependencyInjectionContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}