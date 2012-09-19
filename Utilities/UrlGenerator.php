<?php
namespace Backend\Core\Utilities;
use Backend\Interfaces\ConfigInterface;
class UrlGenerator
{
    protected $context;

    protected $config;

    public function __construct($context, ConfigInterface $config)
    {
        $this->context = $context;
        $this->config  = $config;
    }

    public function generate($routeName)
    {
        $routes = $this->config->get('routes');
        if ($routes && array_key_exists($routeName, $routes)) {
            $route = $routes[$routeName];
            $path = $route['route'];
        } else {
            throw new \RuntimeException('Undefined Route: ' . $routeName);
        }

        $link = $this->context->link;
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        $link .= $path;
        return $link;
    }
}