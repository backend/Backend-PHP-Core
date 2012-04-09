<?php
/**
 * File defining Core\Utilities\Config
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
use Backend\Core\Exceptions\BackendException;
/**
 * Class to handle application configs
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Config
{
    /**
     * @var object Store for all the config values
     */
    private $_values = null;

    /**
     * Construct the config class.
     *
     * @param string $filename The name of the config file to use. Defaults to
     * PROJECT_FOLDER . 'config/default.yaml'
     *
     * @return null
     * @todo Allow passing an array of filesnames to parse. This will let you parse
     * default as well as environment
     */
    public function __construct($filename = false)
    {
        $filename = $filename ? $filename : PROJECT_FOLDER . 'configs/default.yaml';
        if (!is_readable($filename)) {
            throw new BackendException('Invalid Config File: ' . $filename);
        }

        $ext  = pathinfo($filename, PATHINFO_EXTENSION);
        switch ($ext) {
        case 'json':
            $this->_values = json_decode(file_get_contents($filename), true);
            break;
        case 'yaml':
            if (function_exists('yaml_parse_file')) {
                $this->_values = \yaml_parse_file($filename);
            } else if (fopen('SymfonyComponents/YAML/sfYamlParser.php', 'r', true)) {
                if (!class_exists('\sfYamlParser')) {
                    include_once 'SymfonyComponents/YAML/sfYamlParser.php';
                }
                $yaml = new \sfYamlParser();
                //try {
                    $this->_values = $yaml->parse(file_get_contents($filename));
                //} catch (\InvalidArgumentException $e) {
                    //TODO Translate this to a Backend Specific error message
                    //Just keep the config null
                //}
            }
        }
        if (is_null($this->_values)) {
            throw new BackendException('Could not parse Config File using extension ' . $ext);
        }
    }

    /**
     * Magic function that returns the config values on request
     *
     * @param string $propertyName The name of the property being accessed
     *
     * @return mixed The value of the property
     */
    public function __get($propertyName)
    {
        if (array_key_exists($propertyName, $this->_values)) {
            return $this->_values[$propertyName];
        }
        return null;
    }

    /**
    * Get the named config value from the specified section.
    *
    * @param string $section The name of the config section
    * @param string $name    The name of the config value
    *
    * @return mixed The config setting
    */
    public function get($section = false, $name = false)
    {
        if ($section) {
            $section = $this->__get($section);
            if ($name && !is_null($section)) {
                if (array_key_exists($name, $section)) {
                    return $section[$name];
                } else {
                    return null;
                }
            }
            return $section;
        } else {
            return $this->_values;
        }
        return null;
    }
}
