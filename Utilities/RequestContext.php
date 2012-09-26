<?php
/**
 * File defining Backend\Core\Utilities\RequestContext.
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
use Backend\Interfaces\RequestInterface;
use Backend\Interfaces\RequestContextInterface;
/**
 * Class to define the context of a Request.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class RequestContext implements RequestContextInterface
{
    /**
     * The scheme of the request.
     *
     * @var string
     */
    protected $scheme;

    /**
     * The host of the request.
     *
     * @var string
     */
    protected $host;

    /**
     * The folder of the request.
     *
     * @var string
     */
    protected $folder;

    /**
     * The site link of the request.
     *
     * @var string
     */
    protected $link;

    /**
     * The class constructor.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request from which
     * the context is derived.
     */
    public function __construct(RequestInterface $request)
    {
        $defaults = array(
            'scheme' => 'http',
            'host'   => gethostname(),
            'path'   => '/',
        );
        $urlParts = parse_url($request->getUrl());

        if (empty($urlParts)) {
            throw new \RuntimeException('Unparsable URL Requested');
        }

        $urlParts = $urlParts + $defaults;
        $urlParts['path'] = empty($urlParts['path']) ? '/' : $urlParts['path'];

        $this->scheme = $urlParts['scheme'];
        $this->host   = $urlParts['host'];

        $this->folder = preg_replace('|' . $request->getPath() . '$|', '', $urlParts['path']);

        //Check if the last part is a file
        if (substr($this->folder, -1) !== '/' && strpos(basename($this->folder), '.') !== false) {
            $this->folder = dirname($this->folder);
        }
        if (substr($this->folder, -1) === '/') {
            $this->folder = substr($this->folder, 0, strlen($this->folder) -1);
        }

        $this->link = $this->scheme . '://' . $this->host . $this->folder;
    }

    /**
     * Get the Request scheme.
     *
     * If the Request URL was, http://backend-php.net/test, this will return
     * http
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the hostname of the Request.
     *
     * If the Request URL was, http://backend-php.net/test, this will return
     * backend-php.net
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the path of the Request.
     *
     * If the Request URL was, http://backend-php.net/test, this will return
     * /test
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Get a link to the base site of the Request.
     *
     * If the Request URL was, http://backend-php.net/test, this will return
     * http://backend-php.net/
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
}
