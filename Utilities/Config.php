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
use \Backend\Interfaces\ConfigInterface;
use \Backend\Core\Exception as CoreException;
use \Backend\Core\Exceptions\ConfigException;
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
     * @var object Store for all the config values.
     */
    protected $values = null;

    /**
     * Parser to parse the config file.
     *
     * @var callable
     */
    protected $parser = null;

    /**
     * Construct the config class.
     *
     * @param mixed $config The configuration, either as an array of values
     * or the name of the config file.
     *
     * @return null
     */
    public function __construct($config)
    {
        switch (true) {
        case is_string($config):
            $this->_values = $this->fromFile($config);
            break;
        case is_array($config):
            $this->_values = $config;
            break;
        default:
            throw new ConfigException('Invalid configuration values passed');
            break;
        }
        $this->rewind();
    }

    /**
     * Get the parser to parse a config file.
     *
     *  If none is set, it tries to use the pecl yaml parser, or the Symfony
     *  Components YAML parser.
     *
     *  @return object
     */
    public function getParser()
    {
        if (empty($this->parser)) {
            if (function_exists('yaml_parse')) {
                $this->parser = function($yamlString)
                {
                    return \yaml_parse($yamlString);
                };
            } else if (class_exists('\sfYamlParser')) {
                $this->parser = array(new \sfYamlParser(), 'parse');
            } else if (class_exists('\Symfony\Component\Yaml\Parser')) {
                $this->parser = array(new \Symfony\Component\Yaml\Parser(), 'parse');
            } else if (class_exists('\sfYamlParser')) {
                $this->parser = array(new \sfYamlParser(), 'parse');
            }
        }
        if (!is_callable($this->parser)) {
            throw new CoreException('Could not find Config Parser');
        }
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
        if (!is_callable($parser)) {
            throw new CoreException('Trying to set Uncallable Config Parser');
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
        $result = call_user_func($parser, file_get_contents($filename));
        return is_object($result) ? (array)$result : $result;
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
            return $this->_values;
        }
    }

    /**
     * Iterator function to get the current element.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->_values);
    }

    /**
     * Iterator function to get the current key.
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->_values);
    }

    /**
     * Iterator function to proceed to the next value.
     *
     * @return void
     */
    public function next()
    {
        next($this->_values);
    }

    /**
     * Iterator function to reset the collection.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->_values);
    }

    /**
     * Iterator function to check if there are more element in the collection.
     *
     * @return boolean
     */
    public function valid()
    {
        return key($this->_values) !== false;
    }
}
