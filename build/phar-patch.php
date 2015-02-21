#!/usr/bin/env php
<?php

$patches = array(
    array(
        'path'    => __DIR__ . '/phar/dropbox/Curl.php',
        'search'  => array('$this->set(CURLOPT_CAINFO', '$this->set(CURLOPT_CAPATH'),
        'replace' => array('//$this->set(CURLOPT_CAINFO', '//$this->set(CURLOPT_CAPATH'),
    ),
    array(
        'path'    => __DIR__ . '/phar/copy/Api.php',
        'search'  => array('curl_setopt($this->curl, CURLOPT_CAINFO'),
        'replace' => array('//curl_setopt($this->curl, CURLOPT_CAINFO'),
    ),
);

$replacements = array(
    array(
        'path'    => __DIR__ . '/phar/aws-sdk/DynamoDb/Session/SessionHandlerInterface.php',
        'content' => '<?php

namespace Aws\DynamoDb\Session;

/**
 * @see http://php.net/manual/en/class.sessionhandlerinterface.php
 */
interface SessionHandlerInterface extends \SessionHandlerInterface {}

',
    )
);

foreach ($patches as $file) {
    echo 'patching file: ' . $file['path'] . "...";
    file_put_contents(
        $file['path'],
        str_replace(
            $file['search'],
            $file['replace'],
            file_get_contents($file['path'])
        )
    );
    echo ' done' . PHP_EOL;
};

foreach ($replacements as $file) {
    echo 'replacing file: ' . $file['path'] . "...";
    file_put_contents($file['path'], $file['content']);
    echo ' done' . PHP_EOL;
}
