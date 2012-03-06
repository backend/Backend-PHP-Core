<?php
/**
 * File defining Controller
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Core
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core;
/**
 * The main controller class.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Controller extends Decorable implements Interfaces\ControllerInterface
{
    /**
     * @var Route This contains the route object that will help decide what controller
     * and action to execute
     */
    protected $route = null;

    /**
     * @var \Backend\Core\Request This contains the Request that's being actioned
     */
    protected $request = null;

    /**
     * The constructor for the object
     *
     * @param \Backend\Core\Request $request The request object for the execution of the action
     */
    function __construct(Request $request = null)
    {
        //Setup the request
        $this->request = $request;
    }

    /**
     * Set the Request for the Controller
     *
     * @param \Backend\Core\Request $request The request for the Controller
     *
     * @return \Backend\Core\Controller The current object
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Create a redirection Response
     *
     * @param string $location     The location to redirect to
     * @param int    $responseCode The HTTP status code to use
     *
     * @return \Backend\Core\Response The Response object
     */
    public function redirect($location, $responseCode = 302)
    {
        if (substr($responseCode, 0, 1) !== '3') {
            throw new \Exception('Invalid Redirection Response Code');
        }
        $response = new Response('Redirecting to ' . $location, $responseCode);
        $response->addHeader('Location', $location);
        return $response;
    }

    /**
     * Use the current Route to generate the Model name and return it
     *
     * @return ModelInterface The model associated with this controller
     */
    public function getModel($id = null)
    {
        $reflector = new \ReflectionClass($this);
        $namespace = preg_replace('/\\\\Controllers$/', '\\Models', $reflector->getNamespaceName());
        $modelName = basename(str_replace('\\', DIRECTORY_SEPARATOR, get_class($this)));
        $modelName = Utilities\Strings::singularize(preg_replace('/Controller$/', '', $modelName));
        $modelName = Utilities\Strings::className($modelName);
        $modelName = $namespace . '\\' . $modelName;
        if (!class_exists($modelName, true)) {
            return null;
        }
        $model = new $modelName($id);
        if ($model instanceof Interfaces\Decorable) {
            foreach ($model->getDecorators() as $decorator) {
                $model = new $decorator($model);
                if (!($model instanceof \Backend\Core\Decorators\ModelDecorator)) {
                    //TODO Use a specific Exception
                    throw new \Exception(
                        'Class ' . $decorator . ' is not an instance of \Backend\Core\Decorators\ModelDecorator'
                    );
                }
            }
        }
        return $model;
    }
}
