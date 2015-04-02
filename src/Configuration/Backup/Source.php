<?php
namespace phpbu\App\Configuration\Backup;

use phpbu\App\Configuration\Optionized;

class Source extends Optionized
{
    public $type;

    public function __construct($type, $options = array())
    {
        $this->type    = $type;
        $this->setOptions($options);
    }
}
