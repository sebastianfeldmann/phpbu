<?php
namespace phpbu\App\Configuration\Backup;

use phpbu\App\Configuration\Optionized;

class Sync extends Optionized
{
    public $type;

    public $skipOnFailure;

    public function __construct($type, $skipOnFailure, $options = array())
    {
        $this->type          = $type;
        $this->skipOnFailure = $skipOnFailure;
        $this->setOptions($options);
    }
}
