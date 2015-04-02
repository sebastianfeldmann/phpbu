<?php
namespace phpbu\App\Configuration;

class Options
{
    protected $options = array();

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function __isset($name)
    {
        return isset($this->options[$name]);
    }

    public function __get($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    public function __set($name, $value)
    {
        return $this->options[$name] = $value;
    }

    public function toArray()
    {
        return $this->options;
    }
}

