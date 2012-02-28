<?php
/**
 * File defining ControllerTest
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
     * Test a Relative Redirect
     *
     * @return null
     */
    public function testRelativeRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('/');
        var_dump($response); die;
    }

    /**
     * Test an Absolute Redirect
     *
     * @return null
     */
    public function testAbsoluteRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('http://www.google.com');
        $this->assertContains('Location: http://www.google.com', $response->getHeaders());
    }

    /**
     * Test a Permanent Redirect
     *
     * @return null
     */
    public function testPermanentRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('http://www.google.com', 302);
        var_dump($response); die;
    }

    /**
     * Test an Invalid Redirect
     *
     * @return null
     */
    public function testInvalidRedirect()
    {
        $controller = new Controller();
    }
}