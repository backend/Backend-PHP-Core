<?php
/**
 * File defining ViewFactoryTest
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
use \Backend\Core\Application;
use \Backend\Core\Utilities\Config;
use \Backend\Core\Utilities\Logger;
use \Backend\Core\Utilities\ApplicationEvent;
use \Backend\Core\Utilities\Subject;
/**
 * Class to test the \Backend\Core\Utilities\ViewFactory class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ViewFactoryTest extends \PHPUnit_Framework_TestCase
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
     * Test the build function
     * 
     * @return void
     */
    public function testBuild()
    {
        $request = new \Backend\Core\Request('http://www.google.com/', 'GET', array('format' => 'test'));
        $request->setQuery('/');
        $view = \Backend\Core\Utilities\ViewFactory::build($request, array('\Backend\Core\Tests'));
        $this->assertInstanceOf('\Backend\Core\View', $view);
    }

    /**
     * testBuild 
     * 
     * @return void
     * @expectedException \Backend\Core\Exceptions\UnrecognizedRequestException
     */
    public function testBuildException()
    {
        $request = new \Backend\Core\Request('http://www.google.com/', 'GET', array('format' => 'someFormat'));
        $request->setQuery('/');
        $view = \Backend\Core\Utilities\ViewFactory::build(
            $request,
            array(
                '\Backend\Core\Tests'
            )
        );
    }

    /**
     * Test getViews 
     * 
     * @return void
     */
    public function testGetViews()
    {
        $views = \Backend\Core\Utilities\ViewFactory::getViews(
            array(
                '\Backend\Core\Tests'
            )
        );
        $this->assertContains('\Backend\Core\Tests\Views\Test', $views);
    }

    /**
     * Test getFormats 
     * 
     * @return void
     */
    public function testGetFormats()
    {
        $request = new \Backend\Core\Request('http://www.google.com/', 'GET', array('format' => 'someFormat'));
        $request->setQuery('/');
        $formats = \Backend\Core\Utilities\ViewFactory::getFormats($request);
        $this->assertContains('someFormat', $formats);
    }
}
