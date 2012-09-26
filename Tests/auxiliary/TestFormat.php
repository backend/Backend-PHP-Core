<?php
/**
 * File defining TestFormat.
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Auxiliary
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */

/**
 * Class to test the checking of Formats.
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class TestFormat implements \Backend\Interfaces\FormatterInterface
{
    /**
     * @var array Handle Test requests
     */
    public static $handledFormats = array('test');

    /**
     * Output the response to the client.
     *
     * @param mixed $result The result to transform.
     *
     * @return \Backend\Interfaces\ResponseInterface;
     */
    public function transform($result)
    {
        return $result;
    }
 }
