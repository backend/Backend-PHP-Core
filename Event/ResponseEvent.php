<?php
/**
 * File defining \Backend\Core\Event\ResponseEvent
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
 * The ResponseEvent Event.
 *
 * This event is triggered when the Application has transformed a Request into a Response.
 * It gives applications the opportunity to transform and check the Response before
 * it's returned.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Events
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ResponseEvent extends Event
{
    /**
     * The Response associated with the event.
     *
     * @var Backend\Interfaces\ResponseInterface
     */
    private $response;

    /**
     * The object constructor.
     *
     * @param Backend\Interfaces\ResponseInterface $response The response associated with the event.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
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
     * @return Backend\Core\Event\ResponseEvent     The current object.
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }
}
