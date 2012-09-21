<?php
/**
 * File defining \Backend\Core\Tests\Utilities\CallbackTest
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
namespace Backend\Core\Tests\Utilities;
use Backend\Core\Utilities\Callback;
/**
 * Class to test the \Backend\Core\Utilities\Callback class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class CallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @return void
     */
    public function testConstructor()
    {
        $callback = new Callback(__CLASS__, __METHOD__);
        $this->assertEquals('\\' . __CLASS__, $callback->getClass());
        $this->assertEquals(__METHOD__, $callback->getMethod());

        $callback = new Callback($this, __METHOD__);
        $this->assertSame($this, $callback->getObject());
        $this->assertEquals(__METHOD__, $callback->getMethod());

        $callback = new Callback('preg_match', null, array('one' => 'two'));
        $this->assertEquals('preg_match', $callback->getFunction());
        $this->assertEquals(array('one' => 'two'), $callback->getArguments());
    }

    /**
     * Test the Class setter and getter.
     *
     * @return void
     */
    public function testClassAccessors()
    {
        $class = '\\' . __CLASS__;
        $callback = new Callback();
        $callback->setClass($class);
        $this->assertEquals($class, $callback->getClass());
        $this->assertFalse($callback->isValid());
    }

    /**
     * Test checking for invalid class type.
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid type for class name, string expected, got
     * object
     */
    public function testInvalidClassType()
    {
        $callback = new Callback();
        $callback->setClass($this);
    }

    /**
     * Test checking for a non existing class.
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Trying to set non-existant class in
     * Callback: \SomeClass
     * @return void
     */
    public function testNonExistantClass()
    {
        $callback = new Callback();
        $callback->setClass('\SomeClass');
    }

    /**
     * Test the Object setter and getter.
     *
     * @return void
     */
    public function testObjectAccessors()
    {
        $callback = new Callback();
        $callback->setObject($this);
        $this->assertSame($this, $callback->getObject());
        $this->assertFalse($callback->isValid());
    }

    /**
     * Test checking for an invalid object.
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid type for object, object expected
     */
    public function testInvalidObject()
    {
        $callback = new Callback();
        $callback->setObject('Class');
    }

    /**
     * Test the Method setter and getter.
     *
     * @return void
     */
    public function testMethodAccessors()
    {
        $method = 'someMethod';
        $callback = new Callback();
        $callback->setMethod($method);
        $this->assertEquals($method, $callback->getMethod());
        $this->assertFalse($callback->isValid());
    }

    /**
     * Test checking for an invalid method type.
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid type for method, string expected
     * @return void
     */
    public function testInvalidMethodType()
    {
        $callback = new Callback();
        $callback->setMethod($this);
    }

    /**
     * Test the Function setter and getter.
     *
     * @return void
     */
    public function testFunctionAccessors()
    {
        $function = 'preg_match';
        $callback = new Callback();
        $callback->setFunction($function);
        $this->assertEquals($function, $callback->getFunction());
        $this->assertEquals('preg_match', $callback->isValid());
    }

    /**
     * Test checking for an invalid function type.
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid type for function, string expected
     * @return void
     */
    public function testInvalidFunctionType()
    {
        $callback = new Callback();
        $callback->setFunction($this);
    }

    /**
     * Test the Arguments setter and getter.
     *
     * @return void
     */
    public function testArgumentsAccessors()
    {
        $arguments = array('one' => 'two');
        $callback = new Callback();
        $callback->setArguments($arguments);
        $this->assertEquals($arguments, $callback->getArguments());
    }

    /**
     * Data provider for testExecute
     *
     * @return array
     */
    public function dataExecute()
    {
        $result = array();
        //Functions
        $result[] = array(new Callback('getcwd'), getcwd());
        $result[] = array(new Callback('max', null, array(array(1, 0))), 1);
        $result[] = array(new Callback('max', null, array(1, 2)), 2);
        $result[] = array(new Callback('max', null, array(3, 2, 1)), 3);
        $result[] = array(new Callback('max', null, array(3, 4, 2, 1)), 4);

        //Objects
        $node = $this->getMock('DOMNode');
        $node
            ->expects($this->any())
            ->method('hasChildNodes')
            ->will($this->returnValue(false));
        $result[] = array(new Callback($node, 'hasChildNodes'), false);
        $result[] = array(new Callback($node, 'hasChildNodes', array(1)), false);
        $result[] = array(new Callback($node, 'hasChildNodes', array(1, 2)), false);
        $callback = new Callback($node, 'hasChildNodes', array(1, 2, 3));
        $result[] = array($callback, false);
        $callback = new Callback($node, 'hasChildNodes', array(1, 2, 3, 4));
        $result[] = array($callback, false);

        //Class
        $callback = new Callback(
            'DateTime',
            'createFromFormat',
            array('Y-m-d H:i:s', '2012-07-11 12:34:56')
        );
        $expected = \DateTime::createFromFormat('Y-m-d H:i:s', '2012-07-11 12:34:56');
        $result[] = array($callback, $expected);

        return $result;
    }

    /**
     * Test the different execute paths
     *
     * @param \Backend\Core\Utilities\Callback $callback The callback to execute.
     * @param mixed                            $result   The desired result.
     *
     * @dataProvider dataExecute
     * @return void
     */
    public function testExecute(Callback $callback, $result)
    {
        $this->assertEquals($result, $callback->execute());
    }

    /**
     * Check for invalid callback when executing
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unexecutable Callback
     */
    public function testInvalidExecute()
    {
        $callback = new Callback;
        $callback->execute();
    }

    /**
     * Data provider for testInvalidCallback
     *
     * @return array
     */
    public function dataIncompleteCallback()
    {
        $result = array();
        // Empty Everything
        $result[] = array(new Callback());
        // Empty Class and Object, Set Method
        $callback = new Callback();
        $callback->setMethod(__METHOD__);
        $result[] = array($callback);
        // Set Class, Empty Method
        $callback = new Callback();
        $callback->setClass(__CLASS__);
        $result[] = array($callback);
        // Set Object, Empty Method
        $callback = new Callback();
        $callback->setObject($this);
        $result[] = array($callback);

        return $result;
    }

    /**
     * Test checking for an incomplete callback.
     *
     * @param \Backend\Core\Utilities\Callback $callback The callback to
     * check.
     *
     * @dataProvider dataIncompleteCallback
     * @return void
     */
    public function testIncompleteCallback(Callback $callback)
    {
        $this->assertFalse($callback->isValid());
    }

    /**
     * Data provider for testInvalidCallback.
     *
     * @return array
     */
    public function dataInvalidCallback()
    {
        $result = array();
        $callback = new Callback();
        $callback->setFunction('someFunc');
        $result[] = array($callback);
        $callback = new Callback();
        $callback->setClass(__CLASS__);
        $callback->setMethod('someMethod');
        $result[] = array($callback);
        $callback = new Callback();
        $callback->setObject($this);
        $callback->setMethod('someMethod');
        $result[] = array($callback);
        $callback = new Callback('!');
        $result[] = array($callback);

        return $result;
    }

    /**
     * Test checking for an invalid callback.
     *
     * @param \Backend\Core\Utilities\Callback $callback The callback to check.
     *
     * @dataProvider dataInvalidCallback
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unexecutable Callback
     */
    public function testInvalidCallback($callback)
    {
        $callback->isValid();
    }

    /**
     * Data provider for testValidCallback
     *
     * @return array
     */
    public function dataValidCallback()
    {
        $result = array();
        // Function
        $callback = new Callback();
        $callback->setFunction('preg_match');
        $result[] = array($callback, 'preg_match');
        // Object Method
        $callback = new Callback();
        $callback->setObject($this);
        $callback->setMethod('dataValidCallback');
        $result[] = array($callback, get_class($this) . '::dataValidCallback');
        // Class Method
        $callback = new Callback();
        $callback->setClass('\Backend\Core\Autoloader');
        $callback->setMethod('register');
        $result[] = array($callback, '\Backend\Core\Autoloader::register');

        return $result;
    }

    /**
     * Test checking for a Valid callback.
     *
     * This also tests the toString method.
     *
     * @param \Backend\Core\Utilities\Callback $callback The callback to check.
     * @param string                           $result   The string representation
     * of the callback.
     *
     * @dataProvider dataValidCallback
     * @return void
     */
    public function testValidCallback(Callback $callback, $result)
    {
        $this->assertEquals($result, $callback->isValid());
    }

    /**
     * Data provider for testToString
     *
     * @return array
     */
    public function dataForToString()
    {
        $result = array();
        $result[] = array(new Callback, '(Invalid Callback)');
        $result[] = array(new Callback('DateTime'), 'DateTime');
        $result[] = array(
            new Callback('DateTime', 'createFromFormat'),
            '\DateTime::createFromFormat'
        );
        $result[] = array(
            new Callback('DateTime', 'unknownMethod'),
            '\DateTime::unknownMethod'
        );
        $result[] = array(
            new Callback(new \DateTime(), 'unknownMethod'),
            '\DateTime::unknownMethod'
        );
        $callback = new Callback;
        $callback->setMethod('someMethod');
        $result[] = array($callback, '(null)::someMethod');

        return $result;
    }

    /**
     * Test the __toString method
     *
     * @dataProvider dataForToString
     * @return void
     */
    public function testToString(Callback $callback, $result)
    {
        $this->assertEquals($result, (string) $callback);
    }
}
