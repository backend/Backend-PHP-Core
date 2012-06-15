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
use Backend\Interfaces\RouterInterface;
use Backend\Interfaces\FormatterInterface;
use Backend\Interfaces\RequestInterface;
use Backend\Interfaces\CallbackInterface;
use Backend\Core\Utilities\Router;
use Backend\Core\Utilities\Formatter;
use Backend\Modules\Callback;
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
     * Router to map callbacks to requests, and vice versa.
     *
     * @var Backend\Interfaces\RouterInterface
     */
    protected $router = null;

    /**
     * Formatter to convert results into responses.
     *
     * @var Backend\Interfaces\FormatterInterface
     */
    protected $formatter = null;

    /**
     * The constructor for the object.
     *
     * @param \Backend\Interfaces\RouterInterface    $router    The router to use.
     * @param \Backend\Interfaces\FormatterInterface $formatter The formatter to use.
     */
    public function __construct(
        RouterInterface $router = null,
        FormatterInterface $formatter = null
    ) {
        $this->router    = $router  ?: new Router();
        $this->formatter = $formatter ?: new Formatter();
    }

    /**
     * Main function for the application
     *
     * @param \Backend\Interfaces\RequestInterface $request The request the
     * application should handle
     *
     * @return mixed The result of the call
     */
    public function main(RequestInterface $request = null)
    {
        //Inspect the request and subsequent results, chain if necessary
        $toInspect = $request ?: Request::fromState();
        do {
            $callback  = $toInspect instanceof RequestInterface
                ? $this->router->inspect($toInspect)
                : $toInspect;
            if ($callback instanceof RequestInterface) {
                continue;
            } else if ($callback) {
                if ($callback instanceof CallbackInterface) {
                } else if (is_array($callback) && count($callback) == 2) {
                    $callback = Callback::fromString($callback[0], $callback[1]);
                    if (is_subclass_of($callback->getClass(), '\Backend\Interfaces\ControllerInterface')) {
                        $callback->setObject(new $callback->getClass());
                    }
                }
                $toInspect = $callback->execute();
            } else {
                //TODO 404 or something
                $toInspect = null;
            }
        } while ($toInspect instanceof RequestInterface
            || $toInspect instanceof CallbackInterface);

        //Transform the Result
        $response = $this->formatter->transform($toInspect);
        $response->output();
    }
}
