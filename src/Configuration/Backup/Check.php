<?php
namespace phpbu\App\Configuration\Backup;

class Check
{
    public $type;

    public $value;

    public function __construct($type, $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }
}
