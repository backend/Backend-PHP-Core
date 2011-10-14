<?php
require_once('lib/core/BEApplication.obj.php');
require_once('lib/modifiers.inc.php');
class BEApplicationTest extends PHPUnit_Framework_TestCase
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
        $this->assertEquals(BEApplication::translateController('home'), 'HomesController');
        $this->assertEquals(BEApplication::translateController('homes'), 'HomesController');
        $this->assertEquals(BEApplication::translateController('home-camps'), 'HomeCampsController');
        $this->assertEquals(BEApplication::translateController('home_camps'), 'HomeCampsController');

        $this->assertEquals(BEApplication::translateModel('home'), 'HomesModel');
        $this->assertEquals(BEApplication::translateModel('homes'), 'HomesModel');
        $this->assertEquals(BEApplication::translateModel('home-camps'), 'HomeCampsModel');
        $this->assertEquals(BEApplication::translateModel('home_camps'), 'HomeCampsModel');

        $this->assertEquals(BEApplication::translateView('json'), 'JsonView');
        $this->assertEquals(BEApplication::translateView('text'), 'TextView');
        $this->assertEquals(BEApplication::translateView('html'), 'HtmlView');
        $this->assertEquals(BEApplication::translateView('css'), 'CssView');
        $this->assertEquals(BEApplication::translateView('some_other'), 'SomeOtherView');
    }
}
