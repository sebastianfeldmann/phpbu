#!/usr/bin/env php
<?php
echo 'phpbu/phpbu: ';

$tag = @exec('git describe --tags 2>&1');

if (strpos($tag, '-') === false && strpos($tag, 'No names found') === false) {
    echo $tag;
} else {
    $branch = @exec('git rev-parse --abbrev-ref HEAD');
    $hash   = @exec('git log -1 --format="%H"');
    echo $branch . '@' . $hash;
}
echo PHP_EOL;

$lock = json_decode(file_get_contents(__DIR__ . '/../composer.lock'));

foreach ($lock->packages as $package) {
    echo $package->name . ': ' . $package->version;
    if (!preg_match('/^[v= ]*(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9\\.\\-:]*)?)?)?)?)$/', $package->version)) {
        echo '@' . $package->source->reference;
    }
    echo PHP_EOL;
}
foreach ($lock->{'packages-dev'} as $package) {
    if (!in_array($package->name, [
        "aws/aws-sdk-php",
        "google/apiclient",
        "guzzlehttp/guzzle",
        "kunalvarma05/dropbox-php-sdk",
        "microsoft/azure-storage-blob",
        "php-opencloud/openstack",
        "phpmailer/phpmailer",
        "phpseclib/phpseclib",
        "sebastian/environment",
        "sebastianfeldmann/cli",
        "sebastianfeldmann/ftp",
        "softlayer/objectstorage",
        "vlucas/phpdotenv",

    ])) {
      continue;
    }
    echo $package->name . ': ' . $package->version;
    if (!preg_match('/^[v= ]*(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9\\.\\-:]*)?)?)?)?)$/', $package->version)) {
        echo '@' . $package->source->reference;
    }
    echo PHP_EOL;
}
