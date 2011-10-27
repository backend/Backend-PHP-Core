<?php
require_once('lib/core/CoreApplication.obj.php');
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
        $this->assertEquals(CoreApplication::translateController('home'), 'HomesController');
        $this->assertEquals(CoreApplication::translateController('homes'), 'HomesController');
        $this->assertEquals(CoreApplication::translateController('home-camps'), 'HomeCampsController');
        $this->assertEquals(CoreApplication::translateController('home_camps'), 'HomeCampsController');

        $this->assertEquals(CoreApplication::translateModel('home'), 'HomesModel');
        $this->assertEquals(CoreApplication::translateModel('homes'), 'HomesModel');
        $this->assertEquals(CoreApplication::translateModel('home-camps'), 'HomeCampsModel');
        $this->assertEquals(CoreApplication::translateModel('home_camps'), 'HomeCampsModel');
    }
}
