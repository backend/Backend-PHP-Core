<?php
namespace Backend\Base\Decorators;

/**
 * The Crud Controller is a Decorator that provides basic CRUD functionality to controllers
 *
 * Executing GET requests on the following special resources modifies the default REST behaviour
 * * <controller>/<id>/input Return the inputs required to create or update an entity.
 */
class CrudController extends \Backend\Core\Decorators\ControllerDecorator
{
    /**
     * CRUD Read functionality for controllers.
     */
    public function readAction($identifier, $arguments)
    {
        $model = $this->getModel();
        if (is_null($model)) {
            throw new \Exception('Could not find specified Model');
        }
        $model->read($identifier);
        return $model;
    }

    public function readHtml(
        $identifier, $arguments, \Backend\Core\Interfaces\ModelInterface $model, \Backend\Core\View $view = null
    )
    {
        $view = $view instanceof View ? $view : $view = \Backend\Core\Application::getTool('View');
        if (count($arguments) >= 1 && $arguments[0] == 'input') {
            $template = 'crud/form.tpl';
            $view->bind('title', 'Update ' . $model->getName());
        } else {
            $template = 'crud/display.tpl';
            $view->bind('title', 'Display ' . $model->getName());
        }
        return $view->render($template, array('model' => $model));
    }

    public function createHtml($view, $model)
    {
        //return $view->redirect('display');
    }
}
