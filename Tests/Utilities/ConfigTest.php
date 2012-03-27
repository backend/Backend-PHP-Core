<?php
/**
 * File defining ConfigTest
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Tests\Utilities;
use \Backend\Core\Utilities\Config;
/**
 * Class to test the \Backend\Core\Utilities\Config class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    /**
     * Check if the correct exception is thrown for unexisting files
     *
     * @expectedException \Backend\Core\Exceptions\BackendException
     */
    public function testInvalidConfigFile()
    {
        $config = new Config('/var/www/some/unexisting/file');
    }

    public function testYamlConfig()
    {
        $config   = new Config(PROJECT_FOLDER . 'configs/default.yaml');
        $expected = include PROJECT_FOLDER . 'configs/default.php';
        foreach ($expected as $section => $values) {
            $this->assertEquals($values, $config->get($section));
        }
    }

    public function testJsonConfig()
    {
        $config   = new Config(PROJECT_FOLDER . 'configs/default.json');
        $expected = include PROJECT_FOLDER . 'configs/default.php';
        foreach ($expected as $section => $values) {
            $this->assertEquals($values, $config->get($section));
        }
    }

    public function testDefaultConfigFile()
    {
        $config   = new Config();
        $expected = include PROJECT_FOLDER . 'configs/default.php';
        foreach ($expected as $section => $values) {
            $this->assertEquals($values, $config->get($section));
        }
    }

    /**
     * Check if the correct exception is thrown for unparsable file
     *
     * @expectedException \Backend\Core\Exceptions\BackendException
     */
    public function testInvalidConfig()
    {
        $config = new Config(__FILE__);
    }

    public function testGet()
    {
        $expected = include PROJECT_FOLDER . 'configs/default.php';
        $config = new Config(PROJECT_FOLDER . 'configs/default.yaml');

        //Get the section using get, and the magic method
        $this->assertEquals($expected['application'], $config->application);
        $this->assertEquals($expected['application'], $config->get('application'));

        //Get a specified setting in a section
        $this->assertEquals($expected['application']['values'], $config->get('application', 'values'));

        //Get the whole config
        $this->assertEquals($expected, $config->get());
    }

    public function testEmptyGet()
    {
        $config = new Config(PROJECT_FOLDER . 'configs/default.yaml');
        $this->assertEquals(null, $config->someWeird);
        $this->assertEquals(null, $config->get('application', 'someWeird'));
    }
}
