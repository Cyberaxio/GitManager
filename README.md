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

## Run command inside repository directory

```php
<?php

// Get raw command output
$rawOutput = $repository->rawCommand('ls');

// You can also parse the output by passing a callback
$parsedOutput = $repository->command('ls')->run()
	->parseOutput(function($value){
		if($value == condition){
			return $value;
		}
		return false;
	}));

// You can enable debug mode for each command by chaining method debug before method call.
// For example, for a branch command.
$repository->getBranches()->debug()->all();

```

## Clone repo
```php
<?php
// You can clone a public repo by passing the url and the path where it should be cloned.
$repository = Manager::clone($url, $path);

// If it is a private repo, provide the ssh url and the path to the private key (Which should be readable by your webserver) and the port (default to 22)
$repository = Manager::clone($url, $path, $pathToPrivateKey, $port);

// You can also do theses steps separately
// First, you need to get a repository instance.
$repository = Manager::new();

// Pass the path where the repo will be cloned
$repository->setPath($url);

// Pass the url, we will check if the repo is readable before cloning it
$repository->setUrl($url);

// Optionally the port if it's on a private server
$repository->setPort($port);

// Set the private key (or deploy key)
$repository->setPrivateKey($pathToPrivateKey);

// This method accept the port on second parameters for convenience
$repository->setPrivateKey($pathToPrivateKey, $port);

// Now clone it
$repository->clone();

```