<?php
namespace Backend\Core\Utilities;
use Backend\Interfaces\FormatterInterface;
use Backend\Core\Response;
class Formatter implements FormatterInterface
{
    public function transform($result)
    {
        $response = new Response();
        $response->setBody($result);
        return $response;
    }
}