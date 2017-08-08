# PHPBU

**PHP Backup Utility**

*PHPBU* is a php framework that creates and encrypts backups, syncs your backups to other servers or cloud services
and assists you monitor your backup creation.

Get an in depth look into all the features and a short 'getting started' tutorial at the [PHPBU Website](https://phpbu.de).

[![Latest Stable Version](https://poser.pugx.org/phpbu/phpbu/v/stable.svg)](https://packagist.org/packages/phpbu/phpbu)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.0-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/phpbu/phpbu.svg?v1)](https://packagist.org/packages/phpbu/phpbu)
[![License](https://poser.pugx.org/phpbu/phpbu/license.svg)](https://packagist.org/packages/phpbu/phpbu)
[![Build Status](https://travis-ci.org/sebastianfeldmann/phpbu.svg?branch=master)](https://travis-ci.org/sebastianfeldmann/phpbu)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sebastianfeldmann/phpbu/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sebastianfeldmann/phpbu/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/sebastianfeldmann/phpbu/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sebastianfeldmann/phpbu/?branch=master)
[![PHP Website](https://img.shields.io/website-up-down-green-red/https/phpbu.de.svg)](https://phpbu.de)

## Features

* Creating backups
    + ArangoDB
    + Directories
    + Elasticsearch
    + MongoDB
    + MySQL
    + Percona XtraBackup
    + PostgreSQL
    + Redis
* Compress backups
    + bzip2
    + gzip
    + xz
    + zip
* Validate backups
    + Check min size
    + Comparing with previous backups
* Encrypting backups
    + mcrypt
    + openssl
* Sync backups to other locations
    + Amazon s3
    + Dropbox
    + FTP
    + rsync
    + SFTP
    + Softlayer
* Cleanup your local backup
    + Delete backups older x
    + Store only x MB of backups
    + Keep only last x backups
    + Keep less backups for more distant past

## Requirements

* PHP >= 7.0
    + ext/curl
    + ext/dom
    + ext/json
    + ext/spl
* POSIX Shell
    + tar
    + bzip2 or gzip

## Installation

You can [download](https://phar.phpbu.de/phpbu.phar) a PHP Archive [(PHAR)](http://php.net/phar) that bundles everything you need to run *PHPBU* in a single file.

    wget https://phar.phpbu.de/phpbu.phar
    chmod +x phpbu.phar
    php phpbu.phar --version

For convenience, you can move the PHAR to a directory that is in your [PATH](http://en.wikipedia.org/wiki/PATH_%28variable%29).

    mv phpbu.phar /usr/local/bin/phpbu
    phpbu --version

Using [PHIVE](https://phar.io) to install *PHPBU*.

    phive install phpbu

Installing *PHPBU* via Composer is also supported.

```json
  "require": {
    "phpbu/phpbu": "~5.0"
  }
```

## Usage
```
phpbu [option]

  --bootstrap=<file>     A "bootstrap" PHP file that is included before the backup.
  --configuration=<file> A phpbu xml config file.
  --colors               Use colors in output.
  --debug                Display debugging information during backup generation.
  --limit=<subset>       Limit backup execution to a subset.
  --simulate             Perform a trial run with no changes made.
  -h, --help             Print this usage information.
  -v, --verbose          Output more verbose information.
  -V, --version          Output version information and exit.
```

### Usage Examples

    $ phpbu

This requires a valid XML *PHPBU* configuration file (phpbu.xml or phpbu.xml.dist) in your current working directory.
Alternatively, you can specify the path to your configuration file.

    $ phpbu --configuration=backup/config.xml

Use the *--limit* option to execute only a subset of your configured backups.

    $ phpbu --limit=myAppDB

Use the *--simulate* option to perform a trial run without actually executing the configured backups.

    $ phpbu --simulate

## Configuration Example

Simple configuration example:

```xml
  <?xml version="1.0" encoding="UTF-8"?>
  <phpbu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="http://schema.phpbu.de/5.0/phpbu.xsd"
         verbose="true">
    <backups>
      <backup name="myAppDB">
        <!-- source -->
        <source type="mysqldump">
          <option name="databases" value="mydbname"/>
          <option name="user" value="user.name"/>
          <option name="password" value="topsecret"/>
        </source>
        <!-- where should the backup be stored -->
        <target dirname="backup/mysql"
                filename="mysqldump-%Y%m%d-%H%i.sql"
                compress="bzip2"/>
      </backup>
    </backups>
  </phpbu>
```
