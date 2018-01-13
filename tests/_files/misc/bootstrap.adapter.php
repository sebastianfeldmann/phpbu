<?php
// bootstrap demo file
use phpbu\App;

/**
 * Class DemoAdapter
 */
class DemoAdapter implements App\Adapter
{
    public function setup(array $conf = [])
    {
        // do something fooish
    }

    public function getValue(string $path) : string
    {
        return 'demo';
    }
}

App\Factory::register('adapter', 'demo', DemoAdapter::class);
