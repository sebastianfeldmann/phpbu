<?php
namespace phpbu\App;

interface Listener
{
    /**
     * phpbu start event.
     * 
     * @param array $settings
     */
    public function phpbuStart($settings);

    /**
     * phpbu end event.
     * 
     * @param \phpbu\App\Result $result
     */
    public function phpbuEnd(Result $result);

    /**
     * Backup start event.
     * 
     * @param array $backup
     */
    public function backupStart($backup);

    /**
     * Backup failed event.
     * 
     * @param array $backup
     */
    public function backupFailed($backup);

    /**
     * Backup failed event.
     * 
     * @param array $backup
     */
    public function backupEnd($backup);

    /**
     * Check start event.
     * 
     * @param array $check
     */
    public function checkStart($check);

    /**
     * Check failed event.
     * 
     * @param array $check
     */
    public function checkFailed($check);

    /**
     * Check end event.
     * 
     * @param array $check
     */
    public function checkEnd($check);

    /**
     * Sync start event.
     * 
     * @param array $sync
     */
    public function syncStart($sync);

    /**
     * Sync skipped event.
     * 
     * @param array $sync
     */
    public function syncSkipped($sync);

    /**
     * Sync failed event.
     * 
     * @param array $sync
     */
    public function syncFailed($sync);

    /**
     * Sync end event.
     * 
     * @param array $sync
     */
    public function syncEnd($sync);

    /**
     * Cleanup start event.
     * 
     * @param array $cleanup
     */
    public function cleanupStart($cleanup);

    /**
     * Cleanup skipped event.
     * 
     * @param array $cleanup
     */
    public function cleanupSkipped($cleanup);

    /**
     * Cleanup failed event.
     * 
     * @param array $cleanup
     */
    public function cleanupFailed($cleanup);

    /**
     * Cleanup end event.
     * 
     * @param array $cleanup
     */
    public function cleanupEnd($cleanup);

    /**
     * Store / output some debug information.
     * 
     * @param string $msg
     */
    public function debug($msg);
}
