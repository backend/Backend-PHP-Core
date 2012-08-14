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
     * Set up the test
     *
     * @return void
     */
    public function setUp()
    {
        $folders = array();
        $folders[] = __DIR__ . '/../auxiliary/';
        Config::setBaseFolders($folders);
    }

    /**
     * Test the base folders getter and settor
     */
    public function testBaseFoldersAccessors()
    {
        $folders = array('one', 'two');;
        Config::setBaseFolders($folders);
        $this->assertEquals($folders, Config::getBaseFolders());
    }

    /**
     * Check for invalid Parser.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\DuckTypeException
     * @expectedExceptionMessage Expected an object with a parse method
     */
    public function testInvalidParser()
    {
        $config = new Config(new \stdClass);
    }

    /**
     * Check for invalid Config values.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage Invalid configuration values
     */
    public function testInvalidConfigValue()
    {
        $parser = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ParserInterface'
        );
        $config = new Config($parser, true);
    }

    /**
     * Test setting the config.
     *
     * @return void
     */
    public function testSet()
    {
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $actual = array('one' => 'value');
        $config = new Config($parser, $actual);
        $this->assertEquals($actual, $config->get());

        $config = new Config($parser, (object) $actual);
        $this->assertEquals($actual, $config->get());
    }

    /**
     * Check for invalid parsers.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\DuckTypeException
     * @expectedExceptionMessage Expected an object with a parse method
     */
    public function testInvalidParserSetter()
    {
        $config = new Config(new \stdClass);
    }

    /**
     * Test the parser getters and setters.
     *
     * @return void
     */
    public function testParserAccessors()
    {
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $config = new Config($parser);
        $config->setParser($parser);
        $this->assertEquals($parser, $config->getParser());
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
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $config = new Config($parser);
        $config->setAll(__FILE__);
    }

    /**
     * Test the fromFile method.
     *
     * @return void
     */
    public function testFromFile()
    {
        $configFile = dirname(__FILE__) . '/../auxiliary/configs/config.json';
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $parser
            ->expects($this->once())
            ->method('parse')
            ->with(file_get_contents($configFile))
            ->will($this->returnValue(array('one' => 'two')));
        $config = new Config($parser);
        $config->setAll($configFile);
        $this->assertEquals(array('one' => 'two'), $config->get());
    }

    /**
     * Test the getNamed method.
     *
     * @return void
     */
    public function testGetNamed()
    {
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $parser
            ->expects($this->once())
            ->method('parse')
            ->will($this->returnValue(array('one' => 'two')));
        $config = Config::getNamed($parser, 'application');
        $this->assertInstanceOf('\Backend\Interfaces\ConfigInterface', $config);
    }
    /**
     * Test an unsuccesful getNamed call.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage Could not find No_such_file Configuration file.
     */
    public function testConfigNotFound()
    {
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $config = Config::getNamed($parser, 'no_such_file');
    }

    /**
     * Test the value getters and setters method.
     *
     * @return void
     */
    public function testValueAccessors()
    {
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $configArray = array('one' => 'value');
        $config = new Config($parser, $configArray);
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
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $config = new Config($parser);
        $this->assertFalse($config->valid());

        $config = new Config(
            $parser,
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
