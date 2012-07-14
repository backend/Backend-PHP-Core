<?php
/**
 * File defining \Backend\Core\Tests\Utilities\ConfigTest
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   CoreTests
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core\Tests\Utilities;
use \Backend\Core\Utilities\Config;
use \Backend\Interfaces\ConfigInterface;
/**
 * Class to test the \Backend\Core\Utilities\Config class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check for invalid Config values.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage Invalid configuration values
     */
    public function testInvalidConfigValue()
    {
        $config = new Config(true);
    }

    /**
     * Test setting the config.
     *
     * @return void
     */
    public function testSet()
    {
        $actual = array('one' => 'value');
        $config = new Config($actual);
        $this->assertEquals($actual, $config->get());

        $config = new Config((object)$actual);
        $this->assertEquals($actual, $config->get());
    }

    /**
     * Check for invalid parsers.
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Trying to set Uncallable Config Parser
     */
    public function testInvalidParser()
    {
        $config = new Config;
        $config->setParser('something');
    }
    /**
     * Test the parser getters and setters.
     *
     * @return void
     */
    public function testParserAccessors()
    {
        $config = new Config;
        $config->setParser('json_decode');
        $this->assertEquals('json_decode', $config->getParser());

        $config = new Config;
        $parser = $config->getParser();
        $this->assertTrue(is_callable($parser));
    }

    /**
     * Test checking for an invalid config file.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage Invalid Configuration File
     */
    public function testInvalidFile()
    {
        $config = new Config;
        $config->setParser('json_decode');
        $config->setAll(__FILE__);
    }

    /**
     * Test the fromFile method.
     *
     * @return void
     */
    public function testFromFile()
    {
        $configFile = dirname(__FILE__) . '/../auxiliary/config.json';
        $config = new Config;
        $config->setParser('json_decode');
        $config->setAll($configFile);
        $this->assertEquals(
            (array)json_decode(file_get_contents($configFile)), $config->get()
        );
    }
    /**
     * Test the value getters and setters method.
     *
     * @return void
     */
    public function testValueAccessors()
    {
        $configArray = array('one' => 'value');
        $config = new Config($configArray);
        $this->assertEquals($configArray, $config->get());
        $this->assertEquals($configArray['one'], $config->get('one'));
        $this->assertEquals($configArray['one'], $config->one);
        $this->assertEquals('default', $config->get('other', 'default'));

        $this->assertInstanceOf(
            '\Backend\Interfaces\ConfigInterface', $config->set('test', 'set')
        );
        $this->assertEquals('set', $config->test);

        $config->something = 'value';
        $this->assertEquals('value', $config->something);
    }
    /**
     * Test the iterator methods.
     *
     * @return void
     */
    public function testIteratorMethods()
    {
        $config = new Config;
        $this->assertFalse($config->valid());

        $config = new Config(
            array('one' => 'value', 'two' => 'again')
        );
        $this->assertEquals('one', $config->key());
        $this->assertEquals('value', $config->current());
        $this->assertTrue($config->valid());
        $config->next();
        $this->assertEquals('two', $config->key());
        $this->assertEquals('again', $config->current());
        $config->next();
        $this->assertFalse($config->valid());
        $config->rewind();
        $this->assertTrue($config->valid());
        $this->assertEquals('one', $config->key());
    }
}