<?php
/**
 * File defining Controller
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Core
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core;
use Backend\Interfaces\ControllerInterface;
use Backend\Interfaces\RequestInterface;
use Backend\Core\Exception as CoreException;
/**
 * Controller that acts as the connection between Models and Views.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Controller implements ControllerInterface
{
    /**
     * @var \Backend\nterfaces\RequestInterface This contains the Request that's
     * being actioned
     */
    protected $request = null;

    /**
     * The constructor for the object
     */
    function __construct()
    {
    }

    /**
     * Set the Request for the Controller.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request for the
     * Controller.
     *
     * @return \Backend\Interfaces\ControllerInterface The current object.
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get the Controller's Request
     *
     * @return \Backend\Interfaces\RequestInterface The Controller's Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Create a redirection Response
     *
     * @param string $location     The location to redirect to
     * @param int    $responseCode The HTTP status code to use
     *
     * @return \Backend\Core\Response The Response object
     */
    public function redirect($location, $responseCode = 302)
    {
        if (substr($responseCode, 0, 1) !== '3') {
            throw new CoreException('Invalid Redirection Response Code');
        }
        $response = new Response('Redirecting to ' . $location, $responseCode);
        $response->addHeader('Location', $location);
        return $response;
    }
}
