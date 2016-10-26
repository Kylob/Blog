<?php

namespace BootPress\Blog\Twig;

use BootPress\Page\Component as PageClone;

class Page
{
    public $methods = array();

    public function __construct()
    {
        $page = PageClone::html();
        foreach (array('set', 'url', 'get', 'post', 'tag', 'meta', 'link', 'style', 'script', 'jquery', 'id') as $name) {
            $this->methods[$name] = array($page, $name);
        }
    }

    public function __get($name)
    {
        return PageClone::html()->$name;
    }

    public function __isset($name)
    {
        return (isset($this->methods[$name])) ? false : true;
    }

    public function __call($name, $arguments)
    {
        if (isset($this->methods[$name]) && is_callable($this->methods[$name])) {
            return call_user_func_array($this->methods[$name], $arguments);
        }
    }
}
