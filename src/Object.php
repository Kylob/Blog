<?php

namespace BootPress\Blog;

class Object
{
    public $properties = array();
    public $methods = array();

    public function __construct(array $properties = array(), array $methods = array())
    {
        $this->properties = $properties;
        $this->methods = $methods;
    }

    public function __get($name)
    {
        return (isset($this->properties[$name])) ? $this->properties[$name] : null;
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
