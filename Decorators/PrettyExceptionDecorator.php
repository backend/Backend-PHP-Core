<?php
/**
 * File defining Core\Decorators\PrettyExceptionDecorator
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Decorators
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Decorators;
/**
 * Abstract base class for Model decorators
 *
 * @category   Backend
 * @package    Core
 * @subpackage Decorators
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class PrettyExceptionDecorator extends \Exception
{
    protected $exception;

    /**
     * The constructor for the class
     *
     * @param Exception $exception The exception to decorate
     * @param string    $message   The exception message
     * @param integer   $code      The exception code
     */
    function __construct(\Exception $exception, $message = null, $code = 0)
    {
        $this->exception = $exception;
        parent::__construct($message, $code);
    }

    /**
     * Return the exception as a string
     *
     * @return string The exception as a string
     * @todo Use the kohana code to format the exception properly.
     * * /kohana/system/classes/kohana/kohana/exception.php
     * * /kohana/system/views/kohana/error.php
     */
    public function __toString()
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            get_class($this->_exception),
            $this->exception->getCode(),
            strip_tags($this->_exception->getMessage()),
            $this->exception->getFile(),
            $this->exception->getLine()
        );
    }
}
