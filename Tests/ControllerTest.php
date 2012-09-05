<?php
/**
 * File defining \Backend\Core\Tests\ControllerTest
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
use \Backend\Core\Controller;
use \Backend\Core\Request;
use \Backend\Core\Response;
/**
 * Class to test the \Backend\Core\Controller class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test setting and getting the Request
     *
     * @return void
     */
    public function testRequestAccessors()
    {
        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $controller = new Controller();
        $controller->setRequest($request);
        $this->assertSame($request, $controller->getRequest());
    }

    /**
     * Test setting and getting the DIC
     *
     * @return void
     */
    public function testContainerAccessors()
    {
        $container = $this->getMockForAbstractClass(
            '\Backend\Interfaces\DependencyInjectionContainerInterface'
        );
        $controller = new Controller();
        $controller->setContainer($container);
        $this->assertSame($container, $controller->getContainer());
    }

    /**
     * Test a Relative Redirect
     *
     * @return void
     */
    public function testRelativeRedirect()
    {
        $controller = new Controller();
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue('http://backend-php.net'));
        $controller->setRequest($request);
        $response = $controller->redirect('/');
        $headers  = $response->getHeaders();
        $this->assertContains('Location: http://backend-php.net/', $headers);
    }

    /**
     * Test an Absolute Redirect
     *
     * @return void
     */
    public function testAbsoluteRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('http://www.google.com');
        $headers  = $response->getHeaders();
        $this->assertContains('Location: http://www.google.com', $headers);
    }

    /**
     * Test a Permanent Redirect
     *
     * @return void
     */
    public function testPermanentRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('http://www.google.com', 302);
        $headers  = $response->getHeaders();
        $this->assertContains('Location: http://www.google.com', $headers);
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Test an Invalid Redirect
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid Redirection Response Code
     * @return void
     */
    public function testInvalidRedirect()
    {
        $controller = new Controller();
        $controller->redirect('/somewhere', 401);
    }
}
