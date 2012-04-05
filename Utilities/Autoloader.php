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
namespace Backend\Core\Utilities;
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
        //Prepend the master autoload function to the beginning of the stack
        spl_autoload_register(array('\Backend\Core\Utilities\Autoloader', 'autoload'), true, true);
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
        $parts  = explode('\\', $className);
        $vendor = false;
        $base   = false;
        if (count($parts) > 1) {
            $vendor = $parts[0];
            if (count($parts) > 2) {
                $base = $parts[1];
            }
        }
        //This allows us to define a class as \Backend\Something, which can be loaded from
        // \Backend\Base\Something, or if that doesn't exist, \Backend\Core\Something
        if ($vendor && $vendor == 'Backend' && self::_loadBackendClass($base, $className)) {
            return true;
        }

        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (file_exists(VENDOR_FOLDER . $fileName)) {
            include_once VENDOR_FOLDER . $fileName;
            return true;
        }
        if (file_exists(SOURCE_FOLDER . $fileName)) {
            include_once SOURCE_FOLDER . $fileName;
            return true;
        }
        return false;
    }

    /**
     * Load backend specific classes
     *
     * @param string $base      The code base to check
     * @param string $className The class name to be loaded
     *
     * @return boolean If a class was loaded or not
     */
    private static function _loadBackendClass($base, $className)
    {
        if (!class_exists('\Backend\Core\Application', false)) {
            return false;
        }
        return false;
        var_dump($base, $className);
        throw new \Exception('TODO: This has been broken by the new namespace implementation');
        $bases = Application::getNamespaces();
        if (!$base || in_array($base, $bases)) {
            return false;
        }

        //Not in a defined Base, check all
        $parts     = explode('\\', $className);
        $bases     = array_reverse($bases);
        $className = end($parts);
        foreach ($bases as $base) {
            $namespace = implode('/', array_slice($parts, 1, count($parts) - 2));
            $fileName = BACKEND_FOLDER . DIRECTORY_SEPARATOR
                . $base . DIRECTORY_SEPARATOR
                . $namespace . DIRECTORY_SEPARATOR
                . $className . '.php';
            if (file_exists($fileName)) {
                include_once $fileName;
                return true;
            }
        }
        return false;
    }
}
