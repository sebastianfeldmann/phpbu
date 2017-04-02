<?php

namespace phpbu\App\Cmd;


class Backup
{
    public function getOptions() : array
    {
        return [
            'bootstrap='     => true,
            'configuration=' => true,
            'limit='         => true,
            'simulate'       => true,
        ];
    }

    public function execute()
    {

    }
}
