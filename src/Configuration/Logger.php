<?php
namespace phpbu\App\Configuration;

class Logger extends Optionized
{
    public $type;

    public function __construct($type, $options = array())
    {
        $this->type = $type;
        $this->setOptions($options);
    }
}
