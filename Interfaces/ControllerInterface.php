<?php
/**
 * File defining Core\Interfaces\ControllerInterface
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Interfaces
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Interfaces;
/**
 * The base Controller interface
 *
 * @category   Backend
 * @package    Core
 * @subpackage Interfaces
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
interface ControllerInterface
{
    /**
     * Set the Request for the Controller
     *
     * @param \Backend\Core\Request $request The request for the Controller
     *
     * @return \Backend\Core\Controller The current object
     */
    public function setRequest(\Backend\Core\Request $request);
}
