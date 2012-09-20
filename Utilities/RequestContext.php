<?php
namespace Backend\Core;
use Backend\Interfaces\RequestInterface;
class RequestContext
{
    protected $siteState;

    protected $scheme;

    protected $host;

    protected $path;

    protected $link;

    public function __construct(RequestInterface $request, $siteState = 'dev')
    {
        $this->siteState = $siteState;

        $defaults = array(
            'scheme' => 'http',
            'host'   => gethostname(),
            'path'   => '/',
        );
        $urlParts = parse_url($request->getUrl());

        if (empty($urlParts)) {
            throw new \RuntimeException('Unparsable URL Requested');
        }
        $urlParts = $urlParts + $defaults;
        $this->scheme = $urlParts['scheme'];
        $this->host   = $urlParts['host'];
        $this->path   = $urlParts['path'];

        //Check if the last part is a file
        if (substr($this->path, -1) !== '/' && strpos(basename($this->path), '.') !== false) {
            $this->path = dirname($this->path);
        }

        $this->link = $this->scheme . '://' . $this->host . $this->path;
        $this->link = substr($this->link, -1) === '/' ?
            substr($this->link, 0, strlen($this->link) -1) : $this->link;
    }

    public function __get($parameter)
    {
        if (property_exists($this, $parameter)) {
            return $this->$parameter;
        } else {
            throw new \ErrorException('Undefined property: ' . get_class($this) . '::$' . $name);
        }
    }
}