<?php
/**
 * File defining \Backend\Core\Utilities\Format
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Utilities;
use \Backend\Core\Application;
use \Backend\Core\Request;
use \Backend\Core\Response;
/**
 * The Base Format class.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
abstract class Format
{
    /**
     * The request that was used to generate the Response.
     *
     * @var \Backend\Core\Request
     */
    protected $request = null;

    /**
     * @var array Define the formats this class can handle
     */
    public static $handledFormats = array();

    /**
     * The constructor for the object
     *
     * @param Request $request The Request to associate with the view
     */
    function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Build a format with the supplied request
     *
     * @param Request $request    The Request to use to determine the format
     * @param array   $namespaces An array of namespaces to check
     *
     * @throws \Backend\Core\Exceptions\UnrecognizedRequestException
     * @return \Backend\Core\Format The format specified in the Request
     */
    public static final function build(Request $request, array $namespaces = null)
    {
        $classes = self::getFormatClasses($namespaces);
        $formats = self::getFormats($request);

        foreach ($formats as $format) {
            foreach ($classes as $className) {
                if (in_array($format, $className::$handledFormats)) {
                    $object = new $className($request);
                    return $object;
                }
            }
        }

        throw new \Backend\Core\Exceptions\UnrecognizedRequestException('Unrecognized Format');
    }

    /**
     * Get the available classes
     * 
     * @param array $namespaces An array of namespaces to check
     *
     * @return array The list of available format classes
     */
    public static final function getFormatClasses(array $namespaces = null)
    {
        //Get the Files
        $namespaces  = $namespaces ? $namespaces : array_reverse(Application::getNamespaces());
        $formatFiles = array();
        foreach ($namespaces as $base) {
            $folder = str_replace('\\', DIRECTORY_SEPARATOR, $base);
            $files  = glob(PROJECT_FOLDER . '*' . $folder . '/Formats/*.php');
            $formatFiles = array_merge($formatFiles, $files);
        }

        $formats = array();
        foreach ($formatFiles as $file) {
            //Check the format class
            $formatName = str_replace(array(SOURCE_FOLDER, VENDOR_FOLDER), '', $file);
            $formatName = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', substr($formatName, 0, strlen($formatName) - 4));
            $formats[] = $formatName;
        }
        //Check the classes
        $formats = array_filter($formats, array('\Backend\Core\Utilities\Format', 'isValidFormat'));

        return $formats;
    }

    /**
     * Check if a class is a valid Format class
     * 
     * @param mixed $className The class to check
     *
     * @return boolean
    */
    public static function isValidFormat($className)
    {
        return class_exists($className) && is_subclass_of($className, '\Backend\Core\Utilities\Format');
    }

    /**
     * Get the possible formats for the Request
     *
     * @param \Backend\Core\Request $request The request to check
     *
     * @return array The formats requested
     */
    public static function getFormats(Request $request)
    {
        $formats = array_filter(
            array(
                $request->getSpecifiedFormat(),
                //We can use the extension, but let's not promote a bad practice
                //$request->getExtension(),
                $request->getMimeType(),
            )
        );
        return $formats;
    }

    /**
     * Transform the result into the Format.
     *
     * @param mixed    $result    The result to transform
     * @param callable $callback  The callback that was executed
     * @param array    $arguments The arguments that were passed
     *
     * @return \Backend\Core\Response The response to transform
     */
    public function transform($result, $callback, array $arguments)
    {
        //Execute the format related method
        $method = $this->getFormatMethod($callback);
        if (!$method) {
            return $result;
        }
        new ApplicationEvent(
            'Executing ' . get_class($callback[0]) . '::' . $method, ApplicationEvent::SEVERITY_DEBUG
        );
        if (method_exists($callback[0], $method)) {
            $result = call_user_func(array($callback[0], $method), $result);
        } else {
            new ApplicationEvent(
                get_class($callback[0]) . '::' . $method . ' does not exist',
                ApplicationEvent::SEVERITY_DEBUG
            );
            unset($e);
        }

        //Wrap the result in a response
        $response = $result instanceof Response ? $result : new Response($result);
        $response->addHeader('X-Backend-View', get_class($this));
        return $response;
    }

    /**
     * Return a forrmat method for the specified action
     *
     * @param callable $callback The callback to check for
     *
     * @return string The name of the format Method
     */
    public function getFormatMethod($callback)
    {
        if (!is_array($callback)) {
            //TODO format methods for function callbacks
            return false;
        }
        //Check for a transform for the current format in the controller
        $methodName = get_class($this);
        $methodName = substr($methodName, strrpos($methodName, '\\') + 1);
        $methodName = preg_replace('/Action$/', $methodName, $callback[1]);
        return $methodName;
    }
}
