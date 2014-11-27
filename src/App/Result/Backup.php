<?php
namespace phpbu\App\Result;

class Backup
{
    protected $type;

    protected $wasSuccessful = true;

    protected $checks = array();

    protected $checksFailed = array();

    protected $syncs = array();

    protected $syncsSkipped = array();

    protected $syncsFailed = array();

    protected $cleanups = array();

    protected $cleanupsSkipped = array();

    protected $cleanupsFailed = array();

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function wasSuccessful()
    {
        return $this->wasSuccessful;
    }

    public function fail()
    {
        $this->wasSuccessful = false;
    }

    public function checkAdd($check)
    {
        $this->checks[] = $check;
    }

    public function checkCount()
    {
        return count($this->checks);
    }

    public function checkFailed($check)
    {
        $this->checksFailed[] = $check;
    }

    public function checkFailedCount()
    {
        return count($this->checksFailed);
    }

    public function syncAdd($sync)
    {
        $this->syncs[] = $sync;
    }

    public function syncCount()
    {
        return count($this->syncs);
    }

    public function syncSkipped($sync)
    {
        $this->syncsSkipped[] = $sync;
    }

    public function syncSkippedCount()
    {
        return count($this->syncsSkipped);
    }

    public function syncFailed($sync)
    {
        $this->syncsFailed[] = $sync;
    }

    public function syncFailedCount()
    {
        return count($this->syncsFailed);
    }

    public function cleanupAdd($cleanup)
    {
        $this->cleanups[] = $cleanup;
    }

    public function cleanupCount()
    {
        return count($this->cleanups);
    }

    public function cleanupSkipped($cleanup)
    {
        $this->cleanupsSkipped[] = $cleanup;
    }

    public function cleanupSkippedCount()
    {
        return count($this->cleanupsSkipped);
    }

    public function cleanupFailed($cleanup)
    {
        $this->cleanupsFailed[] = $cleanup;
    }

    public function cleanupFailedCount()
    {
        return count($this->cleanupsFailed);
    }
}
