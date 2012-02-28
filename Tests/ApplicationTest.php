<?php
namespace Backend\Core\Tests;
use \Backend\Core\Application;

//use DataPathway\LedgerBundle\Service\LedgerService;
//use DataPathway\AccountingBundle\Entity\SalesJournal;

class ApplicationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @expectedException \Backend\Core\Exceptions\UndefinedToolException
     */
    public function testAddUndefinedTool()
    {
        Application::addTool('UndefinedTool', 'UndefinedTool');
    }

    /**
     * @expectedException \Backend\Core\Exceptions\InvalidToolException
     */
    public function testAddInvalidTool()
    {
        Application::addTool('InvalidTool', new \StdClass());
    }

    public function testAddGetTool()
    {
        Application::addTool('Logger', '\Backend\Core\Utilities\Logger');
        $this->assertInstanceOf('\Backend\Core\Utilities\Logger', Application::getTool('Logger'));
    }

    /**
     * @expectedException \ErrorException
     */
    public function testError()
    {
        Application::error(1, 'SomeError', __FILE__, __LINE__);
    }

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

    public function testRegisterNamespace()
    {
        $this->assertSame(array(), Application::getNamespaces());
        Application::registerNamespace('Base');
        $this->assertContains('Base', Application::getNamespaces());
    }

    /**
     * @expectedException \ReflectionException
     */
    public function testGetViewMethod()
    {
        $callback = array(
            new \Backend\Base\Controllers\ExamplesController(),
            'homeAction'
        );
        $view = new \Backend\Base\Views\Cli();
        $method = Application::getViewMethod($callback, $view);
        $this->assertInstanceOf('\ReflectionMethod', $method);
        $this->assertSame('homeCli', $method->getName());

        $callback[1] = 'someAction';
        $method = Application::getViewMethod($callback, $view);
    }
}
