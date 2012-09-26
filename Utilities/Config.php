<?php
/**
 * File defining Backend\Core\Utilities\Config .
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
use Backend\Interfaces\ConfigInterface;
use Backend\Core\Exceptions\ConfigException;
use Backend\Core\Exceptions\DuckTypeException;
/**
 * Class to handle application configs.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Config implements ConfigInterface
{
    /**
     * The folders in which to check for configs.
     *
     * @var array
     */
    protected static $baseFolders = null;

    /**
     * @var object Store for all the config values.
     */
    protected $values = array();

    /**
     * Parser to parse the config file.
     *
     * @var callable
     */
    protected $parser = null;

    /**
     * Construct the config class.
     *
     * @param object $parser The parser to use when parsing a file.
     * @param mixed  $config The configuration, either as an array of values
     * or the name of the config file.
     *
     * @return null
     */
    public function __construct($parser, $config = null)
    {
        $this->setParser($parser);
        if (empty($config) === false) {
            $this->setAll($config);
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
        if ($this->has($propertyName)) {
            return $this->values[$propertyName];
        }

        return null;
    }

    /**
    * Get the named config value.
    *
    * @param string $name    The name of the config value. Omit to get the whole
    * config.
    * @param mixed  $default The default value to return should the value not
    * be found.
    *
    * @return mixed The config setting
    */
    public function get($name = false, $default = null)
    {
        if ($name) {
            $value = $this->__get($name);

            return $value === null ? $default : $value;
        } else {
            return $this->values;
        }
    }

    /**
     * Magic function that set the config values on request
     *
     * @param string $propertyName The name of the property being set
     * @param mixed  $value        The value of the property
     *
     * @return void
     */
    public function __set($propertyName, $value)
    {
        $this->values[$propertyName] = $value;
    }

    /**
     * Set a named config value.
     *
     * @param string $name  The name of the config value.
     * @param mixed  $value The value of the setting.
     *
     * @return \Backend\Interfaces\ConfigInterface The current config.
     */
    public function set($name, $value)
    {
        $this->__set($name, $value);

        return $this;
    }

    /**
     * Set the configuration values.
     *
     * @param mixed $config The configuration, either as an array of values
     * or the name of the config file.
     *
     * @return \Backend\Interfaces\ConfigInterface The current config.
     */
    public function setAll($config)
    {
        switch (true) {
            case is_string($config) && file_exists($config):
                $this->values = $this->fromFile($config);
                break;
            case is_array($config):
                $this->values = $config;
                break;
            case is_object($config):
                $this->values = (array) $config;
                break;
            default:
                throw new ConfigException('Invalid configuration values: ' . $config);
                break;
        }
        $this->rewind();

        return $this;
    }

    /**
     * Check if the config has the specified value.
     *
     * @param mixed $name The name of the config value to check.
     *
     * @return boolean If the config has the specified value.
     */
    public function has($name)
    {
        return array_key_exists($name, $this->values);
    }

    /**
     * Get the parser to parse a config file.
     *
     *  @return object
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Set the parser to use when parsing a config file.
     *
     * @param object $parser The parser.
     *
     * @return \Backend\Core\Utilities\Config
     */
    public function setParser($parser)
    {
        if (is_object($parser) === false || method_exists($parser, 'parse') === false) {
            throw new DuckTypeException('Expected an object with a parse method.');
        }
        $this->parser = $parser;

        return $this;
    }

    /**
     * Instansiate the configuration from the specified file.
     *
     * @param string $filename The name of the file to parse.
     *
     * @return array
     */
    protected function fromFile($filename)
    {
        $parser = $this->getParser();
        $result = $parser->parse(file_get_contents($filename));
        if (empty($result)) {
            throw new ConfigException('Invalid Configuration File');
        }

        return is_object($result) ? (array) $result : $result;
    }

    /**
     * Get the named configuration file from the default config locations.
     *
     * The environment specific file is tried first, otherwise the global one is used.
     *
     * @param object $parser The parser to use when parsing a file.
     * @param string $name   The name of the configuration to get.
     *
     * @return Backend\Interfaces\ConfigInterface
         * @throws Backend\Core\Exceptions\ConfigException If the config file can't be
     * found.
     */
    public static function getNamed($parser, $name)
    {
        $files = array(
            'configs/' . $name . '.' . BACKEND_SITE_STATE . '.yml',
            'configs/' . $name . '.yml',
        );
        $folders = self::getBaseFolders();
        foreach ($folders as $folder) {
            foreach ($files as $file) {
                if (file_exists($folder . $file) === false) {
                    continue;
                }

                return new static($parser, $folder . $file);
            }
        }

        throw new ConfigException(
            'Could not find ' . ucwords($name) . ' Configuration file. Add one to '
            . reset($folders) . 'configs'
        );
    }

    /**
     * Iterator function to get the current element.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->values);
    }

    /**
     * Iterator function to get the current key.
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->values);
    }

    /**
     * Iterator function to proceed to the next value.
     *
     * @return void
     */
    public function next()
    {
        next($this->values);
    }

    /**
     * Iterator function to reset the collection.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->values);
    }

    /**
     * Iterator function to check if there are more element in the collection.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->values
            && in_array(key($this->values), array(false, null)) === false;
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
            defined('PROJECT_FOLDER') && self::$baseFolders[] = PROJECT_FOLDER;
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
