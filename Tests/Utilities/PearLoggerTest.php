<?php
/**
 * File defining PearLoggerTest
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
use \Backend\Core\Utilities\PearLogger;
/**
 * Class to test the \Backend\Core\Utilities\PearLogger class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class PearLoggerTest extends \PHPUnit_Framework_TestCase
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
     * Check if the correct message is generated
     *
     * @return void
     */
    public function testMessages()
    {
        $logger = new PearLogger('/tmp/test-backend.log');
        var_dump($logger); die;
        //$this->assert
        ob_start();
        $logger->update(new LogMessage('Some Message', $level));
        $result = ob_get_clean();
        $this->assertContains($message, $result);
    }
}
