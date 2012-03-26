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
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testRegister()
    {
        Autoloader::register();
        $function = array_shift(spl_autoload_functions());
        $this->assertEquals(array('Backend\Core\Utilities\Autoloader', 'autoload'), $function);
    }

    public function testAutoload()
    {
        $this->assertFalse(class_exists('Backend\Core\Tests\Utilities\AutoloaderTestClass', false));
        Autoloader::autoload('Backend\Core\Tests\Utilities\AutoloaderTestClass');
        $this->assertTrue(class_exists('Backend\Core\Tests\Utilities\AutoloaderTestClass', false));
    }
}
