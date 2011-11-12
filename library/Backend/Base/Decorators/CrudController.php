<?php
namespace Backend\Base\Decorators;

class CrudController extends \Backend\Core\Decorators\ControllerDecorator
{
    public function readAction($id, $arguments)
    {
        $result = $this->_model->readAction($id, $arguments);
        $view = \Backend\Core\Application::getTool('View');
        if ($view) {
            $viewMethod = strtolower(get_class($view));
            $viewMethod = substr($viewMethod, strrpos($viewMethod, '\\') + 1);
            if (method_exists($this, $viewMethod)) {
                $result = $this->$viewMethod($view, $result);
            }
        }
        return $result;
    }

    public function html($view, $result)
    {
        return $view->render('crud_display.tpl', array('values' => $result));
    }
}
