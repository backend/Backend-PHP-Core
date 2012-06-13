<?php
/**
 * File defining Application
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
use Backend\Interfaces\ApplicationInterface;
use Backend\Interfaces\RequestInterface;
use Backend\Interfaces\RouterInterface;
use Backend\Core\Utilities\Router;
/**
 * The main application class.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Application implements ApplicationInterface
{
    /**
     * Main function for the application
     *
     * @param \Backend\Interfaces\RequestInterface $request The request the
     * application should handle
     *
     * @return mixed The result of the call
     */
    public function main(\Backend\Interfaces\RequestInterface $request = null,
        \Backend\Interfaces\RouterInterface $router = null)
    {
        $toInspect = $request ?: Request::fromState();
        $router    = $router  ?: new Router();
        do {
            $callback  = $toInspect instanceof RequestInterface
                ? $router->inspect($toInspect)
                : $toInspect;
            $toInspect = $callback->execute();
        } while ($toInspect instanceof RequestInterface
            || $toInspect instanceof CallbackInterface);

    }
}
