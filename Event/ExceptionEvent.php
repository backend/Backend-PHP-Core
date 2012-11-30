<?php
/**
 * File defining \Backend\Core\Event\ExceptionEvent
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Events
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Event;

use Symfony\Component\EventDispatcher\Event;
use Backend\Interfaces\ResponseInterface;

/**
 * The Exception Event.
 *
 * This event is triggered when an unhandled Exception is thrown.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Events
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ExceptionEvent extends Event
{
    /**
     * The exception associated with the event.
     *
     * @var \Exception
     */
    private $exception;

    /**
     * The Response associated with the event.
     *
     * @var Backend\Interfaces\ResponseInterface
     */
    private $response;

    /**
     * The object constructor.
     *
     * @param \Exception $exception The exception associated with the event.
     */
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Get the current Exception.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set the Exception for the event.
     *
     * @param  \Exception                        $exception
     * @return Backend\Core\Event\ExceptionEvent The current object.
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Get the current Response.
     *
     * @return Backend\Interfaces\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the Response for the Event.
     *
     * @param  Backend\Interfaces\ResponseInterface $response
     * @return Backend\Core\Event\ExceptionEvent    The current object.
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }
}
