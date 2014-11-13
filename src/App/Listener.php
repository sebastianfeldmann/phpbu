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
     * @param Sanity $sanity
     */
    public function sanityStart($sanity);

    /**
     * @param Sanity $sanity
     */
    public function sanityFailed($sanity);

    /**
     * @param Sanity $sanity
     */
    public function sanityEnd($sanity);

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
}
