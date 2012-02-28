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
     * @param Request $request The Request to use to determine the view
     *
     * @return View The view that can handle the Request
     */
    public static function build(Request $request)
    {
        //Check the View Folder
        $views = array();
        $namespaces = array_reverse(Application::getNamespaces());
        $viewFiles = array();
        foreach ($namespaces as $base) {
            $folder = str_replace('\\', DIRECTORY_SEPARATOR, $base);
            $files  = glob(PROJECT_FOLDER . '*' . $folder . '/Views/*.php');
            $viewFiles = array_merge($viewFiles, $files);
        }
        foreach ($viewFiles as $file) {
            //Check the view class
            $viewName = str_replace(array(SOURCE_FOLDER, VENDOR_FOLDER), '', $file);
            $viewName = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', substr($viewName, 0, strlen($viewName) - 4));
            if (!class_exists($viewName, true)) {
                continue;
            }
            $views[] = $viewName;
        }

        $formats = array_filter(
            array(
                $request->getSpecifiedFormat(),
                $request->getExtension(),
                $request->getMimeType(),
            )
        );

        foreach ($formats as $format) {
            foreach ($views as $viewName) {
                if (in_array($format, $viewName::$handledFormats)) {
                    $view = new $viewName($request);
                    if (!($view instanceof View)) {
                        throw new \Backend\Core\Exceptions\UnknownViewException('Invalid View: ' . get_class($view));
                    }
                    return $view;
                }
            }
        }

        throw new \Backend\Core\Exceptions\UnrecognizedRequestException('Unrecognized Format');
        return false;
    }
}
