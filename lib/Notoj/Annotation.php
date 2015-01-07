<?php

namespace Notoj;

class Annotation
{
    public function __construct($name, Array $args = array())
    {
        $this->name = $name;
        $this->args = $args;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getName()
    {
        return $this->name;
    }
}
