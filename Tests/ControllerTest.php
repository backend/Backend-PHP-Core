<?php
namespace Backend\Core\Tests;
use \Backend\Core\Controller;
use \Backend\Core\Request;
use \Backend\Core\Response;

class ControllerTest extends \PHPUnit_Framework_TestCase {

    public function testRelativeRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('/');
        var_dump($response); die;
    }

    public function testAbsoluteRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('http://www.google.com');
        $this->assertContains('Location: http://www.google.com', $response->getHeaders());
    }

    public function testPermanentRedirect()
    {
        $controller = new Controller();
        $response = $controller->redirect('http://www.google.com', 302);
        var_dump($response); die;
    }

    public function testInvalidRedirect()
    {
        $controller = new Controller();
    }
}
