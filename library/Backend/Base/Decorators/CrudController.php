<?php
namespace Backend\Base\Decorators;

class CrudController extends \Backend\Core\Decorators\ControllerDecorator
{
    public function readHtml($id, $arguments, $result, \Backend\Core\View $view = null)
    {
        $view = $view instanceof View ? $view : $view = \Backend\Core\Application::getTool('View');
        return $view->render('crud_display.tpl', array('values' => $result));
    }
}
