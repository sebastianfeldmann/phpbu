<?php
namespace phpbu\App;

interface Listener
{
    /**
     */
    public function phpbuStart();

    /**
     */
    public function phpbuEnd();

    /**
     * @param Backup $backup
     */
    public function backupStart($backup);

    /**
     * @param Backup $backup
     */
    public function backupFailed($backup);

    /**
     * @param Backup $backup
     */
    public function backupEnd($backup);

    /**
     * @param Check $check
     */
    public function checkStart($check);

    /**
     * @param Check $check
     */
    public function checkFailed($check);

    /**
     * @param Check $check
     */
    public function checkEnd($check);

    /**
     * @param Sync $sync
     */
    public function syncStart($sync);

    /**
     * @param Sync $sync
     */
    public function syncFailed($sync);

    /**
     * @param Sysc $sync
     */
    public function syncEnd($sync);

    /**
     * @param Cleanup $cleanup
     */
    public function cleanupStart($cleanup);

    /**
     * @param Cleanup $cleanup
     */
    public function cleanupFailed($cleanup);

    /**
     * @param Cleanup $cleanup
     */
    public function cleanupEnd($cleanup);
}
