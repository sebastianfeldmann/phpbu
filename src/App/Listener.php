<?php
namespace phpbu\App;

interface Listener
{
    /**
     * @param array $settings
     */
    public function phpbuStart($settings);

    /**
     */
    public function phpbuEnd(Result $result);

    /**
     * @param array $backup
     */
    public function backupStart($backup);

    /**
     * @param array $backup
     */
    public function backupFailed($backup);

    /**
     * @param array $backup
     */
    public function backupEnd($backup);

    /**
     * @param array $check
     */
    public function checkStart($check);

    /**
     * @param array $check
     */
    public function checkFailed($check);

    /**
     * @param array $check
     */
    public function checkEnd($check);

    /**
     * @param array $sync
     */
    public function syncStart($sync);

    /**
     * @param array $sync
     */
    public function syncSkipped($sync);

    /**
     * @param array $sync
     */
    public function syncFailed($sync);

    /**
     * @param array $sync
     */
    public function syncEnd($sync);

    /**
     * @param array $cleanup
     */
    public function cleanupStart($cleanup);

    /**
     * @param array $cleanup
     */
    public function cleanupSkipped($cleanup);

    /**
     * @param array $cleanup
     */
    public function cleanupFailed($cleanup);

    /**
     * @param array $cleanup
     */
    public function cleanupEnd($cleanup);

    /**
     * @param string $msg
     */
    public function debug($msg);
}
