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
     * The folders in which to check for classes.
     *
     * @var array
     */
    protected static $baseFolders = null;

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
            $baseFolders = self::getBaseFolders();
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
        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path) {
            if (file_exists($path . '/' . $fileName)) {
                include_once $fileName;

                return true;
            }
        }

        return false;
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
            defined('VENDOR_FOLDER') && self::$baseFolders[] = VENDOR_FOLDER;
            defined('SOURCE_FOLDER') && self::$baseFolders[] = SOURCE_FOLDER;
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
