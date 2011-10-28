<?php
require_once('lib/Core/Application.obj.php');
require_once('lib/modifiers.inc.php');
class CoreApplicationTest extends PHPUnit_Framework_TestCase
{
    private $_request;
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    /**
     */
    public function testTranslations()
    {
        $this->assertEquals(\Core\Application::translateController('home'), 'HomesController');
        $this->assertEquals(\Core\Application::translateController('homes'), 'HomesController');
        $this->assertEquals(\Core\Application::translateController('home-camps'), 'HomeCampsController');
        $this->assertEquals(\Core\Application::translateController('home_camps'), 'HomeCampsController');

        $this->assertEquals(\Core\Application::translateModel('home'), 'HomesModel');
        $this->assertEquals(\Core\Application::translateModel('homes'), 'HomesModel');
        $this->assertEquals(\Core\Application::translateModel('home-camps'), 'HomeCampsModel');
        $this->assertEquals(\Core\Application::translateModel('home_camps'), 'HomeCampsModel');
    }
}
