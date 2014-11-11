# PHPBU

PHP backup utility

## Features

* Creating backups
    + Directories
    + MySQL
    + PostgreSQL (planned)
* Validate backups (planned)
* Sync backups to other locations (planned)
    + amazon s3
    + dropbox
    + ftp
    + rsync
    + sftp
* Cleanup you backup location (planned)

## Requirements

* PHP 5.3+
    + ext/dom
    + ext/json
    + ext/spl

## Installation

You can download a [PHP Archive (PHAR)](http://php.net/phar) that bundles everything you need to run phpbu in a single file.

    wget http://phar.phpbu.de/phpbu.phar
    chmod +x phpbu.phar
    php phpbu.phar --version

For convenience, you can move the PHAR to a directory that is in your [PATH](http://en.wikipedia.org/wiki/PATH_%28variable%29).

    mv phpbu.phar /usr/local/bin/phpbu
    phpbu --version

We also support installing PHPBU via Composer

```json
  "require": {
    "phpbu/phpbu": "1.0.*@dev"
  }
```

## Usage
```
phpbu [option]

  --bootstrap=<file>     A "bootstrap" PHP file that is included before the backup.
  --configuration=<file> A phpbu xml config file.
  -h, --help             Display the help message and exit.
  -v, --verbose          Output more verbose information.
  -V, --version          Output version information and exit.
```

### Usage Examples

    $ phpbu --bootstrap=backup/bootstrap.php

    $ phpbu --configuration=config.xml