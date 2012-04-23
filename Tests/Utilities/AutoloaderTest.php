<?php
/**
 * File defining AutoloaderTest
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
use \Backend\Core\Utilities\Autoloader;
/**
 * Class to test the \Backend\Core\Utilities\Autoloader class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up the test
     *
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * Tear down the test
     *
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * Test if the register function works correctly
     *
     * @return void
     */
    public function testRegister()
    {
        Autoloader::register();
        $function = array_shift(spl_autoload_functions());
        $this->assertEquals(array('Backend\Core\Utilities\Autoloader', 'autoload'), $function);
    }

    /**
     * Test if the autoload function works correctly
     *
     * @return void
     */
    public function testAutoload()
    {
        $this->assertFalse(class_exists('Backend\Core\Tests\Utilities\AutoloaderTestClass', false));
        Autoloader::autoload('Backend\Core\Tests\Utilities\AutoloaderTestClass');
        $this->assertTrue(class_exists('Backend\Core\Tests\Utilities\AutoloaderTestClass', false));
    }

    /**
     * Test if the autoloader ignores non namespaced classes
     *
     * @return void
     */
    public function testNonNSClass()
    {
        $this->assertFalse(Autoloader::autoload('SomeBaseClass'));
    }

    /**
     * Test if the autoloader doesn't find a class
     *
     * @return void
     */
    public function testNonExistantClass()
    {
        $this->assertFalse(Autoloader::autoload('Backend\Core\NoClass'));
    }
}
