<?php
/**
 * File defining Autoloader
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
namespace Backend\Core;
/**
 * The main autoloader.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Autoloader
{
    /**
     * Register the autload function
     *
     * @return null
     */
    public static function register()
    {
        //Prepend the master autoload function to the stack
        spl_autoload_register(array('\Backend\Core\Autoloader', 'autoload'), true);
    }

    /**
     * Function to autoload Backend-CoreFiles classes
     *
     * Register this function for use by calling \Backend\Core\Autoloader::register()
     *
     * @param string $className The class name to auto load
     *
     * @return boolean If the class file was found and included
     */
    public static function autoload($className)
    {
        $className = ltrim($className, '\\');

        $lastNsPos = strripos($className, '\\');
        if ($lastNsPos) {
            //Check namespaced classes
            $namespace = substr($className, 0, $lastNsPos);
            $namespace = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $className = substr($className, $lastNsPos + 1);
            $className = str_replace('_', DIRECTORY_SEPARATOR, $className);
            $fileName  = $namespace  . DIRECTORY_SEPARATOR . $className . '.php';

            //Check the bases for the file
            $baseFolders = array(VENDOR_FOLDER, SOURCE_FOLDER);
            foreach ($baseFolders as $folder) {
                if (file_exists($folder . $fileName)) {
                    include_once $folder . $fileName;
                    return true;
                }
            }
        } else {
            $fileName = $className . '.php';
        }
        //Last gasp attempt, should catch most PSR-0 compliant classes and non
        //namespaced classes
        if (@include_once $fileName) {
            return true;
        }
        return false;
    }
}
