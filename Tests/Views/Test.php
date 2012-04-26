<?php
/**
 * File defining \Core\Tests\Views\Test
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Tests
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Tests\Views;
/**
 * Output a request in JavaScript Object Notation
 *
 * @category   Backend
 * @package    Base
 * @subpackage Views
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Test extends \Backend\Core\View
{
    /**
     * @var array Handle HTML requests
     */
    public static $handledFormats = array(
        'test'
    );

    /**
     * @var mixed The response to give
     */
    protected $response;

    /**
     * Transform the result into a Response Object containing the JSON encoded result
     *
     * @param mixed $result The result to transform
     *
     * @return Response The result transformed into a JSON encoded Response
     */
    public function transform($result)
    {
        return $this->response;
    }

    /**
     * Set the Response to give back
     * 
     * @param mixed $response The response
     *
     * @return void
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}
