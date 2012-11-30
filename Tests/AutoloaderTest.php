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
namespace Backend\Core\Tests;

use \Backend\Core\Autoloader;

/**
 * Class to test the \Backend\Core\Utilities\Autoloader class
 *
 * Notice the use of class_exists($className, FALSE) and an explicit call to the
 * autokload function to prevent the already registered autoloaders from kicking in.
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
     * Original include path
     *
     * @var string
     */
    protected $originalPath = null;

    /**
     * Set up the test
     *
     * @return void
     */
    public function setUp()
    {
        $folders = array();
        if (file_exists($loader = __DIR__ . '/../libraries/')) {
            $folders[] = $loader;
        }
        if (file_exists($loader = __DIR__ . '/../../../../../../libraries/')) {
            $folders[] = $loader;
        }
        $folders[] = __DIR__ . '/../';
        Autoloader::setBaseFolders($folders);
    }

    /**
     * Tear down the test
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->originalPath) {
            set_include_path($this->originalPath);
            $this->originalPath = null;
        }
    }

    /**
     * Test the base folders getter and settor
     */
    public function testBaseFoldersAccessors()
    {
        $folders = array('one', 'two');
        Autoloader::setBaseFolders($folders);
        $this->assertEquals($folders, Autoloader::getBaseFolders());
    }

    /**
     * Test if the register function works correctly
     *
     * @return void
     */
    public function testRegister()
    {
        Autoloader::register();
        $functions = spl_autoload_functions();
        $function  = end($functions);
        $this->assertEquals(array('Backend\Core\Autoloader', 'autoload'), $function);
    }

    /**
     * Test if the autoload function works correctly
     *
     * @return void
     */
    public function testAutoload()
    {
        $this->assertFalse(
            class_exists('Tests\AutoloaderTestClass', false)
        );
        $result = Autoloader::autoload('Tests\AutoloaderTestClass');
        $this->assertTrue($result);
        $this->assertTrue(
            class_exists('Tests\AutoloaderTestClass', false)
        );
    }

    /**
     * Test the last gasp attempt to load a class
     *
     * @return void
     */
    public function testLastGasp()
    {
        $this->originalPath = get_include_path();
        set_include_path(
            implode(
                PATH_SEPARATOR,
                array(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'auxiliary',
                    $this->originalPath,
                )
            )
        );
        $this->assertFalse(
            class_exists('\TestController', false)
        );
        $result = Autoloader::autoload('\TestController');
        $this->assertTrue($result);
        $this->assertTrue(
            class_exists('\TestController', false)
        );
    }

    /**
     * Test if the autoloader doesn't find a class
     *
     * @return void
     */
    public function testNonExistantClass()
    {
        $this->assertFalse(Autoloader::autoload('Backend\Core\NoClass'));
        $this->assertFalse(
            class_exists('Backend\Core\NoClass', false)
        );
    }
}
