<?php
/**
 * File defining \Backend\Core\Event\ResultEvent
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
 * The ResultEvent Event.
 *
 * This event is triggered when the Application has received a Result from a Callback.
 * It gives applications the opportunity to transform and check the Result before
 * it's transformed.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Events
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ResultEvent extends Event
{

    /**
     * The result associated with the event.
     *
     * @var Backend\Interfaces\ResponseInterface
     */
    private $result;

    /**
     * The Response associated with the event.
     *
     * @var Backend\Interfaces\ResponseInterface
     */
    private $response;

    /**
     * The object constructor.
     *
     * @param mixed $result The result associated with the event.
     */
    public function __construct($result)
    {
        $this->result = $result;
        if ($result instanceof ResponseInterface) {
            $this->setResponse($result);
        }
    }

    /**
     * Get the current result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set the result for the Event.
     *
     * @param  mixed                            $result
     * @return Backend\Core\Event\ResponseEvent The current object.
     */
    public function setResult($result)
    {
        $this->result = $result;

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
     * @return Backend\Core\Event\ResponseEvent     The current object.
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }
}
