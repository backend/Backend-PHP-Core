<?php
/**
 * File defining TestController.
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Auxiliary
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */

/**
 * Class to test the autoloading of classes in the include path and other controller
 * related functions.
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class TestController implements \Backend\Interfaces\ControllerInterface
{
    protected $request;

    /**
     * Set the Request for the Controller.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request for the
     * Controller.
     *
     * @return \Backend\Interfaces\ControllerInterface The current object.
     */
    public function setRequest(\Backend\Interfaces\RequestInterface $request)
    {
        $this->request = $request;
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
}
