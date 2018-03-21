# A simple PHP git wrapper [![Build Status](https://img.shields.io/travis/Cyberaxio/gitwrapper.svg)](https://travis-ci.org/Cyberaxio/gitwrapper)

[![Latest Stable Version](https://img.shields.io/packagist/v/cyberaxio/gitwrapper.svg?style=flat)](https://packagist.org/packages/cyberaxio/gitwrapper)


## Installation

Install the latest version with

```bash
$ composer require cyberaxio/gitmanager
```

## Basic Usage

```php
<?php

use Cyberaxio\GitManager\Manager;

// create a new repository
$repository = Manager::init($path);

// retrieve an existing repository
$repository = Manager::find($path);

// create a new repository manager (Without actually init or cloning it)
$repository = Manager::new();

// You can optionally pass an array config to the new method to alter default configuration
$repository = Manager::new(['port' => 12345]);
// Or use the following method
$repository->setConfig('port', 12345);

```

