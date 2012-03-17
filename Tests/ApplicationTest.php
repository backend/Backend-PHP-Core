<?php
/**
 * File defining ApplicationTest
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
namespace Backend\Core\Tests;
use \Backend\Core\Application;
/**
 * Class to test the \Backend\Core\Application class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test adding an undefined Tool
     *
     * @return null
     * @expectedException \Backend\Core\Exceptions\UndefinedToolException
     */
    public function testAddUndefinedTool()
    {
        Application::addTool('UndefinedTool', 'UndefinedTool');
    }

    /**
     * Test adding an invalid Tool
     *
     * @return null
     * @expectedException \Backend\Core\Exceptions\InvalidToolException
     */
    public function testAddInvalidTool()
    {
        Application::addTool('InvalidTool', new \StdClass());
    }

    /**
     * Test adding and retrieving a Tool
     *
     * @return null
     */
    public function testAddGetTool()
    {
        Application::addTool('Logger', '\Backend\Core\Utilities\Logger');
        $this->assertInstanceOf('\Backend\Core\Utilities\Logger', Application::getTool('Logger'));
    }

    /**
     * Test the Application error handling
     *
     * @return null
     * @expectedException \ErrorException
     */
    public function testError()
    {
        Application::error(1, 'SomeError', __FILE__, __LINE__);
    }

    /**
     * Test getting and setting the Debug Level
     *
     * @return null
     */
    public function testDebugLevel()
    {
        $this->assertSame(3, Application::getDebugLevel());
        Application::setDebugLevel(1);
        $this->assertSame(1, Application::getDebugLevel());
        Application::setDebugLevel('4');
        $this->assertSame(4, Application::getDebugLevel());
        Application::setDebugLevel(5);
        $this->assertSame(5, Application::getDebugLevel());
    }

    /**
     * Test adding and getting Namespaces
     *
     * @return null
     */
    public function testRegisterNamespace()
    {
        $this->assertSame(array(), Application::getNamespaces());
        Application::registerNamespace('Base');
        $this->assertContains('Base', Application::getNamespaces());
    }

    /**
     * Test Application::getViewMethod
     *
     * @return null
     * @expectedException \ReflectionException
     */
    public function testGetViewMethod()
    {
        $callback = array(
            new \Backend\Base\Controllers\ExamplesController(),
            'homeAction'
        );
        $request = new \Backend\Core\Request('http://www.google.com', 'GET');
        $view    = new \Backend\Base\Views\Cli($request);
        $method  = Application::getViewMethod($callback, $view);
        $this->assertInstanceOf('\ReflectionMethod', $method);
        $this->assertSame('homeCli', $method->getName());

        $callback[1] = 'someAction';
        $method = Application::getViewMethod($callback, $view);
    }
}
