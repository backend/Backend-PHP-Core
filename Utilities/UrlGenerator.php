<?php
/**
 * File defining Backend\Core\Utilities\UrlGenerator.
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Utilities;
use Backend\Interfaces\ConfigInterface;
use Backend\Interfaces\UrlGeneratorInterface;
use Backend\Interfaces\RequestContextInterface;
/**
 * Class to generate URL's using routing information.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * The context of the current request.
     *
     * @var \Backend\Interfaces\RequestContextInterface
     */
    protected $context;

    /**
     * A config object containing route definitions.
     *
     * @var \Backend\Interfaces\ConfigInterface
     */
    protected $config;

    /**
     * The class constructor.
     *
     * @param \Backend\Interfaces\RequestContextInterface $context The context of
     * the current request.
     * @param \Backend\Interfaces\ConfigInterface         $config  A config object
     * containing route definitions.
     */
    public function __construct(RequestContextInterface $context, ConfigInterface $config)
    {
        $this->context = $context;
        $this->config  = $config;
    }

    /**
     * Generate a link for the given Route.
     *
     * @param string $routeName The name of the route to generate a link for.
     *
     * @return string
     */
    public function generate($routeName)
    {
        $routes = $this->config->get('routes');
        $controllers = $this->config->get('controllers');
        if ($routes && array_key_exists($routeName, $routes)) {
            $path = $routes[$routeName]['route'];
        } else if ($controllers && array_key_exists($routeName, $controllers)) {
            $path = $routeName;
        } else {
            throw new \RuntimeException('Undefined Route: ' . $routeName);
        }

        $link = $this->context->getLink();
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        $link .= $path;
        return $link;
    }
}