# PHPBU

PHP Backup Utility

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
  --colors               Use colors in output.
  --debug                Display debugging information during backup generation.
  -h, --help             Prints this usage information.
  -v, --verbose          Output more verbose information.
  -V, --version          Output version information and exit.
```

### Usage Examples

    $ phpbu

This requires a valid XML phpbu configuration file (phpbu.xml or phpbu.xml.dist) in your current working directory.
Alternatively, you can specify the path to your configuration file

    $ phpbu --configuration=backup/config.xml

## Configuration

Simple configuration example:

```xml
  <?xml version="1.0" encoding="UTF-8"?>
  <phpbu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="http://schema.phpbu.de/1.0/phpbu.xsd"
         verbose="true">
    <backups>
      <backup>
        <!-- source -->
        <source type="mysql">
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
