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
            class_exists('Backend\Core\Tests\AutoloaderTestClass', false)
        );
        $result = Autoloader::autoload('Backend\Core\Tests\AutoloaderTestClass');
        $this->assertTrue($result);
        $this->assertTrue(
            class_exists('Backend\Core\Tests\AutoloaderTestClass', false)
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
            class_exists('\AutoloadTestIncludeClass', false)
        );
        $result = Autoloader::autoload('AutoloadTestIncludeClass');
        $this->assertTrue($result);
        $this->assertTrue(
            class_exists('AutoloadTestIncludeClass', false)
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
