<?php
/**
 * File defining UnknownRouteException
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Exceptions
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Exceptions;
/**
 * UnknownRouteException
 *
 * @category   Backend
 * @package    Core
 * @subpackage Exceptions
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class UnknownRouteException extends \Exception
{
    /**
     * The constructor for the object
     *
     * @param \Backend\Core\Request $request The request which generated the UnknownRouteException
     */
    function __construct(\Backend\Core\Request $request)
    {
        parent::__construct($request->getMethod() . ': ' . $request->getQuery());
    }
}
