<?php
/**
 * File defining Backend\Interfaces\FormatterInterface.
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Interfaces
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core\Utilities;
use Backend\Interfaces\FormatterInterface;
use Backend\Interfaces\DependencyInjectionContainerInterface;
use Backend\Interfaces\RequestInterface;
use Backend\Interfaces\ResponseInterface;
use Backend\Core\Response;
/**
 * Transform results into the specified format.
 *
 * @category Backend
 * @package  Interfaces
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Formatter implements FormatterInterface
{
    /**
     * The request resulting in the response to be formatted.
     *
     * @var \Backend\Interfaces\RequestInterface
     */
    protected $request;

    /**
     * Formats to check
     *
     * @var array
     */
    public static $formats = null;

    /**
     * The folders in which to check for Formatters.
     *
     * @var array
     */
    protected static $baseFolders = null;

    /**
     * The constructor for the object
     *
     * @param \Backend\Interfaces\RequestInterface $request The request used to
     * determine what formatter to return.
     */
    public function __construct(RequestInterface $request = null)
    {
        if ($request !== null) {
            $this->setRequest($request);
        }
    }

    /**
     * Output the response to the client.
     *
     * @param mixed $result The result to transform.
     *
     * @return \Backend\Interfaces\ResponseInterface;
     */
    public function transform($result)
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return new Response($result);
    }

    /**
     * Factory function to generate a formatter object.
     *
     * @param \Backend\Interfaces\DependencyInjectionContainerInterface $container
     * The DI Container used to fetch the object.
     *
     * @return \Backend\Interfaces\FormatterInterface
     */
    public static function factory(DependencyInjectionContainerInterface $container)
    {
        $request = $container->get('request');
        $requested = self::getRequestFormats($request);
        $formats   = self::getFormats();
        foreach ($requested as $reqFormat) {
            foreach ($formats as $formatName) {
                if (in_array($reqFormat, $formatName::$handledFormats)) {
                    $name = strtolower(str_replace('\\', '.', $formatName));
                    if (substr($name, 0, 1) === '.') {
                        $name = substr($name, 1);
                    }
                    $view = $container->get($name);

                    return $view;
                }
            }
        }

        return null;
    }

    /**
     * Get the available Format classes.
     *
     * @return array The list of available Formatter classes.
     */
    public static function getFormats()
    {
        if (self::$formats !== null) {
            return self::$formats;
        }

        $formats = array();
        foreach (self::getBaseFolders() as $base) {
            $folder = new \RecursiveDirectoryIterator($base);
            $iter   = new \RecursiveIteratorIterator($folder);
            $regex = implode(DIRECTORY_SEPARATOR, array('', '.*', '.*', 'Formats', '.+'));
            $regex = '|.*(' . $regex . ')\.php$|i';
            $regex  = new \RegexIterator($iter, $regex, \RecursiveRegexIterator::GET_MATCH);
            foreach ($regex as $file) {
                $formatName = str_replace(
                    array('/', '\\', DIRECTORY_SEPARATOR), '\\',
                    $file[1]
                );
                $formats[] = $formatName;
            }
        }
        $formats = array_unique($formats);
        static::setFormats($formats);

        return self::$formats;
    }

    /**
     * Set the available Format classes.
     *
     * @param array $formats An array of Format classes.
     *
     * @return void
     */
    public static function setFormats(array $formats = null)
    {
        self::$formats = $formats === null ? $formats : array_filter(
            $formats,
            array('\Backend\Core\Utilities\Formatter', 'isValidFormat')
        );
    }

    /**
     * Get the possible formats for the Request.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request to check.
     *
     * @return array
     */
    public static function getRequestFormats(RequestInterface $request)
    {
        $formats = array_unique(
            array_filter(
                array(
                    $request->getSpecifiedFormat(),
                    $request->getExtension(),
                    $request->getMimeType(),
                )
            )
        );

        return $formats;
    }

    /**
     * Check if a class is a valid Format
     *
     * @param mixed $className The class to check
     *
     * @return boolean
     */
    public static function isValidFormat($className)
    {
        $className = is_object($className) ? get_class($className) : $className;
        if (class_exists($className, true) === false) {
            return false;
        }
        //Just use Reflection, unless it's proven to have a performance penalty.
        //if (PHP_VERSION_ID < 50307) {
            $ref = new \ReflectionClass($className);

            return in_array('Backend\Interfaces\FormatterInterface', $ref->getInterfaceNames());
        /*} else {
            return is_subclass_of($className, '\Backend\Interfaces\FormatterInterface');
        }*/
    }

    /**
     * Return the request used to generate the response.
     *
     * @return Backend\Interfaces\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the request used to generate the response.
     *
     * @param Backend\Interfaces\RequestInterface $request The request to set.
     *
     * @return Backend\Core\Utilities\Formatter
     */
    public function setRequest(\Backend\Interfaces\RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the Base folders
     *
     * @return array
     */
    public static function getBaseFolders()
    {
        if (self::$baseFolders === null) {
            self::$baseFolders = array();
            defined('PROJECT_FOLDER') && self::$baseFolders[] = VENDOR_FOLDER;
            defined('PROJECT_FOLDER') && self::$baseFolders[] = SOURCE_FOLDER;
            self::$baseFolders = array_filter(self::$baseFolders, 'file_exists');
        }

        return self::$baseFolders;
    }

    /**
     * Set the Base folders.
     *
     * @param array $folders The base folders
     *
     * @return void
     */
    public static function setBaseFolders(array $folders)
    {
        self::$baseFolders = $folders;
    }
}
