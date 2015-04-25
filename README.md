# a Simle Fluent Logger for PHP

a PHP library to record events to fluentd

# Requirements

- PHP 5.4 or higher
- fluentd v0.9.20 or higher

# Installation

You can install it by composer.
```
composer require dqneo/fluent-simplelogger
```

or create composer.json and exec `composer install'

```json
{
    "require": {
        "dqneo/fluent-simplelogger": "0.0.1"
    }
}
````

# Usage

````
<?php
require_once __DIR__ . '/vendor/autoload.php';

use DQNEO\Fluent\SimpleLogger;

$logger = new SimpleLogger("localhost","24224");
$logger->post("debug.test", ["hello"=>"world"]);
````

# Restrictions

* Buffering and re-send support

PHP does not have threads. So, I strongaly recommend you use fluentd as a local fluent proxy.

````
apache2(mod_php)
fluent-logger-php
                 `-----proxy-fluentd
                                    `------aggregator fluentd
````

# License
Apache License, Version 2.0


# Contributors

* DQNEO
* Daniele Alessandri
* Hiro Yoshikawa
* Kazuki Ohta
* Shuhei Tanuma
* Sotaro KARASAWA
* edy
* kiyoto
* sasezaki
* satokoma
