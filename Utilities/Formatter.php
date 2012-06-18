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
use Backend\Interfaces\RequestInterface;
use Backend\Interfaces\ConfigInterface;
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
     * The request being formatted.
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
     * The constructor for the object
     *
     * @param \Backend\Interfaces\RequestInterface $request The request used to
     * determine what formatter to return.
     * @param \Backend\Interfaces\ConfigInterface  $config  The current Application
     * configuration.
     */
    function __construct(
        RequestInterface $request = null, ConfigInterface $config = null
    ) {
        $this->request = $request;
        $this->config  = $config;
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
        $response = new Response();
        $response->setBody($result);
        return $response;
    }

    /**
     * Factory function to generate a formatter object.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request used to
     * determine what formatter to return.
     * @param \Backend\Interfaces\ConfigInterface  $config  The current Application
     * configuration.
     *
     * @return \Backend\Interfaces\FormatterInterface
     */
    public static function factory(
        RequestInterface $request = null, ConfigInterface $config = null
    ) {
        $requested = self::getRequestFormats($request);
        $formats = self::getFormats();
        foreach ($requested as $reqFormat) {
            foreach ($formats as $viewName) {
                if (in_array($reqFormat, $viewName::$handledFormats)) {
                    $view = new $viewName($request, $config);
                    return $view;
                }
            }
        }
    }

    /**
     * Get the available Format classes.
     * 
     * @return array The list of available Formatter classes.
     */
    public static function getFormats()
    {
        $formatFiles = array();
        foreach (array(VENDOR_FOLDER, SOURCE_FOLDER) as $base) {
            $folder = str_replace('\\', DIRECTORY_SEPARATOR, $base);
            $files  = glob($folder . '*/*/Formats/*.php');
            $formatFiles = array_merge($formatFiles, $files);
        }
        $formatFiles = array_map(
            array('\Backend\Core\Utilities\Formatter', 'formatClass'), $formatFiles
        );
        return array_filter(
            $formatFiles,
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
        $formats = array_filter(
            array(
                $request->getSpecifiedFormat(),
                $request->getExtension(),
                $request->getMimeType(),
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
        return class_exists($className)
            && is_subclass_of(
                $className, '\Backend\Interfaces\FormatterInterface'
            );
    }

    /**
     * get the Format class of the specified file
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
             DIRECTORY_SEPARATOR, '\\',
             substr($formatName, 0, strlen($formatName) - 4)
         );
         return $formatName;
    }
}
