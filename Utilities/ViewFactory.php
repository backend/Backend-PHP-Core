<?php
/**
 * File defining ViewFactory
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
use Backend\Core\Application;
use Backend\Core\Request;
use Backend\Core\View;
/**
 * Factory class to create Views
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ViewFactory
{
    /**
     * Build a view with the supplied (or current) request
     *
     * @param Request $request    The Request to use to determine the view
     * @param array   $namespaces An array of namespaces to check
     *
     * @throws \Backend\Core\Exceptions\UnrecognizedRequestException
     * @return View The view that can handle the Request
     */
    public static function build(Request $request, array $namespaces = null)
    {
        $views = self::getViews($namespaces);

        $formats = self::getFormats($request);

        foreach ($formats as $format) {
            foreach ($views as $viewName) {
                if (in_array($format, $viewName::$handledFormats)) {
                    $view = new $viewName($request);
                    return $view;
                }
            }
        }

        throw new \Backend\Core\Exceptions\UnrecognizedRequestException('Unrecognized Format');
    }

    /**
     * Get the available views
     * 
     * @param array $namespaces An array of namespaces to check
     *
     * @return array The list of available view classes
     */
    public static function getViews(array $namespaces = null)
    {
        //Get the Files
        $namespaces = $namespaces ? $namespaces : array_reverse(Application::getNamespaces());
        $viewFiles  = array();
        foreach ($namespaces as $base) {
            $folder = str_replace('\\', DIRECTORY_SEPARATOR, $base);
            $files  = glob(PROJECT_FOLDER . '*' . $folder . '/Views/*.php');
            $viewFiles = array_merge($viewFiles, $files);
        }

        $views = array();
        foreach ($viewFiles as $file) {
            //Check the view class
            $viewName = str_replace(array(SOURCE_FOLDER, VENDOR_FOLDER), '', $file);
            $viewName = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', substr($viewName, 0, strlen($viewName) - 4));
            $views[] = $viewName;
        }
        //Check the classes
        $views = array_filter($views, array('\Backend\Core\Utilities\ViewFactory', 'isValidView'));

        return $views;
    }

    /**
     * Check if a class is a valid View 
     * 
     * @param mixed $className The class to check
     *
     * @return boolean
     */
    public static function isValidView($className)
    {
        return class_exists($className) && is_subclass_of($className, '\Backend\Core\View');
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
                $request->getExtension(),
                $request->getMimeType(),
            )
        );
        return $formats;
    }
}
