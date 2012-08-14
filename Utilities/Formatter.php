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
use Backend\Interfaces\ConfigInterface;
use Backend\Interfaces\RequestInterface;
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
     * Relavant configuration options.
     *
     * @var \Backend\Interfaces\ConfigInterfaces
     */
    protected $config;

    /**
     * Formats to check
     *
     * @var array
     */
    public static $formats = null;

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
        if ($result instanceof Response) {
            return $result;
        }
        $response = new Response();
        $response->setBody($result);

        return $response;
    }

    /**
     * Factory function to generate a formatter object.
     *
     * @param \Backend\Interfaces\DependencyInjectionContainerInterface $container
     * The DI Container used to fetch the object.
     *
     * @return \Backend\Interfaces\FormatterInterface
     */
    public static function factory(
        DependencyInjectionContainerInterface $container
    ) {
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

        $formatFiles = array();
        foreach (array(VENDOR_FOLDER, SOURCE_FOLDER) as $base) {
            $folder = str_replace('\\', DIRECTORY_SEPARATOR, $base);
            $glob   = str_replace('/', DIRECTORY_SEPARATOR, '*/*/Formats/*.php');
            $files  = glob($folder . $glob);
            $formatFiles = array_merge($formatFiles, $files);
        }
        $formats= array_map(
            array('\Backend\Core\Utilities\Formatter', 'formatClass'), $formatFiles
        );
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
     * Get the Format class of the specified file.
     *
     * @param string $file The filename
     *
     * @return string
     */
    public static function formatClass($file)
    {
         //Check the format class
         $formatName = str_replace(array(SOURCE_FOLDER, VENDOR_FOLDER), '', $file);
         $formatName = '\\' . str_replace(
             array('/', '\\', DIRECTORY_SEPARATOR), '\\',
             substr($formatName, 0, strlen($formatName) - 4)
         );

         return $formatName;
    }

    /**
     * Return the current configuration.
     *
     * @return Backend\Interfaces\ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the configuration to use.
     *
     * @param Backend\Interfaces\ConfigInterface $config The config to set.
     *
     * @return Backend\Core\Utilities\Formatter
     */
    public function setConfig(\Backend\Interfaces\ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
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
}
