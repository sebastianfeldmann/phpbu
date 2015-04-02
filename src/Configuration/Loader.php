<?php
namespace phpbu\App\Configuration;

interface Loader
{
    /**
     * Returns the phpbu Configuration.
     *
     * @return \phpbu\App\Configuration
     */
    public function getConfiguration();
}
