<?php

namespace BootPress\Blog\Twig;

class Parser extends \Twig_Parser
{
    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
        $this->expressionParser = new ExpressionParser($this, $this->env);
    }
}
