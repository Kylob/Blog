<?php

namespace BootPress\Blog;

use BootPress\Page\Component as PageClone;

class Page
{
    public $methods = array();
    private $native = array();

    public function __construct()
    {
        $this->native = array_fill_keys(array('set', 'url', 'get', 'post', 'tag', 'meta', 'link', 'style', 'script', 'jquery', 'id'), true);
    }

    public function __get($name)
    {
        return PageClone::html()->$name;
    }

    public function __isset($name)
    {
        return (isset($this->native[$name]) || isset($this->methods[$name])) ? false : true;
    }

    public function __call($name, $arguments)
    {
        if (isset($this->native[$name])) {
            $result = call_user_func_array(array(PageClone::html(), $name), $arguments);
        } elseif (isset($this->methods[$name]) && is_callable($this->methods[$name])) {
            $result = call_user_func_array($this->methods[$name], $arguments);
        }

        return (isset($result) && !is_object($result)) ? $result : null;
    }
}
