<?php
/**
 * File defining \Core\View
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Core
 * @author    J Jurgens du Toit <jrgns@jrgns.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core;
use \Backend\Core\Request;
use \Backend\Core\Response;
/**
 * The Base View class.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@jrgns.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class View
{
    /**
     * @var array Define the formats this view can handle
     */
    public static $handledFormats = array();

    /**
     * @var \Backend\Core\Request The request that was used to generate the View
     */
    protected $request = null;

    /**
     * The View constructor
     *
     * @param Request $request The Request to associate with the view
     */
    function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Transform the result into a Response Object.
     *
     * This function should be overwritten by other views to change the output
     *
     * @param mixed $result The result to transform
     *
     * @return Response The result transformed into a Response
     */
    function transform($result)
    {
        if ($result instanceof Response) {
            return $result;
        }
        $response = new Response();
        $response->addHeader('X-Backend-View', get_class($this));
        $body  = '';
        switch (gettype($result)) {
        case 'object':
            if ($result instanceof \Exception) {
                $result = new Decorators\PrettyExceptionDecorator($result);
                $result = (string)$result;
            }
            //NO break;
        case 'array':
            $result = var_export($result, true);
            //NO break;
        case 'string':
        default:
            $body = 'Result: ' . $result;
            break;
        }

        //Add some default formatting
        if (!Request::fromCli()) {
            $header = <<< END
<!DOCTYPE HTML>
<html>
    <head>
        <title>Backend-Core</title>
    </head>
    <body>
        <pre>
END;
            $footer = <<< END
        </pre>
    </body>
</html>
END;
            $body = $header . PHP_EOL . $body . PHP_EOL . $footer;
        } else {
            $body .= PHP_EOL;
        }
        $response->setBody($body);
        return $response;
    }

    /**
     * Get the request associated with the View
     *
     * @return \Backend\Core\Request The Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
