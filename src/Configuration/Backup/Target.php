<?php
namespace phpbu\App\Configuration\Backup;

class Target
{
    public $dirname;

    public $filename;

    public $compression;

    public function __construct($dir, $file, $compression = null)
    {
        $this->dirname  = $dir;
        $this->filename = $file;

        if (!empty($compression)) {
            $this->compression = $compression;
        }
    }
}
