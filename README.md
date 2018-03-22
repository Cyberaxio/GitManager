# A simple PHP git wrapper [![Build Status](https://travis-ci.org/Cyberaxio/GitManager.svg?branch=master)](https://travis-ci.org/Cyberaxio/GitManager)

[![Latest Stable Version](https://img.shields.io/packagist/v/cyberaxio/gitmanager.svg?style=flat)](https://packagist.org/packages/cyberaxio/gitmanager)


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
$repository->branches()->debug()->all();

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

## Interact with branches
```php
<?php
// You can access branches commands like this
$repository->branches()->method();

// Or save branches command into variable
$branches = $repository->branches();
$branches->method();
```

* Get branches infos

```php
 <?php
// Return array
// Get all branches (local + remote)
$branches->all();
// Get only local branches
$branches->local();
// Get current branch
$branches->current();

// Return boolean
// Check if branch exists
$branches->exists('master'); // true
// or
$branches->has('master'); // true
```

### Fluent methods (can be chained)
* Create and checkout branches

```php
<?php
// create a branch with name MyNewBranch in the repo
$branches->create("MyNewBranch");
// or
$branches->add("MyNewBranch");

// Switch on branch MyNewBranch
$branches->checkout("MyNewBranch");
// Repo is now on MyNewBranch branch

// You can switch directly on branch after creating it by passing true to create method
$branches->create($name, true);
// You can also create a new branch and switch on by passing true to checkout method
$branches->checkout($name, true);

// Or chaining methods
$tags->create('v3.0')->checkout('v3.0');
```

* Rename branches

```php
 <?php
// To rename current branch
$branches->rename($newName);

// To rename any other branch
$branches->rename($branch, $newName);
```

* Delete branches

```php
 <?php
// To remove a branch
$branches->remove($name);
// Or
$branches->delete($name);
```

* Merge branches

```php
 <?php
// To merge a branch into active branch
$branches->merge($name);

// To merge a branch into master branch, just chain methods
$branches->checkout("master")->merge("BranchToMerge");

// Combine all theses methods (Useless example, but this is for purpose only)
$name="FeatureTwo";
$branches->create($name)->checkout('master')->merge($name)->remove($name);
// -> Create FeatureTwo branch, checkout on master, merge FeatureTwo into master and remove FeatureTwo

```


## Working Copy commands
```php
<?php

// You can access Working Copy commands like this
$repository->workingCopy()->method();

// Or save Working Copy command into variable
$workingCopy = $repository->workingCopy();
$workingCopy->method();
```

* Check if working directory is clean/dirty

```php
 <?php
// Theses methods return boolean
$workingCopy->isDirty(); // true
// Or
$workingCopy->isClean(); // false
```

### Fluent methods (can be chained)
* Add files to index
```php
 <?php
$workingCopy->add($file);
// To add all file in staging
$workingCopy->addAll();
```

* Remove file from index
```php
 <?php
// This will remove file from index only, and keep file on the system
$workingCopy->remove($file);
// If you wish to delete the file on the system, set the second parameter to false
$workingCopy->remove($file, false);
```

* Rename file into index
```php
 <?php
$workingCopy->rename($file, $to);
```

* Make a commit
```php
 <?php
// To commit all staged files
$workingCopy->commit($message, $options);
```

## Remotes commands
```php
<?php
// You can access remotes commands like this
$repository->remotes()->method();

// Or save remotes command into variable
$remotes = $repository->remotes();
$remotes->method();
```

* Get remotes infos

```php
 <?php
// Return array
// Get all remotes
$repository->remotes()->all();

// Check if remote exists (return boolean)
$remotes->exists('origin'); // true

```

### Fluent methods (can be chained)
* Add remote

```php
 <?php
// Add a remote 
$remotes->add("upstream", $url, $options);
// or
$remotes->create("upstream", $url, $options);
```

* Remove remote

```php
 <?php
// Remove a remote 
$remotes->remove($name);
// or
$remotes->delete($name);
```

* Rename remote

```php
 <?php

$remotes->rename($oldName, $newName);
```

* Set/Get remote url

```php
 <?php
 // Set remote url
$remotes->setUrl($name, $url, $options);

// Get remote url
$remotes->getUrl($name);

```

* Check if remote is readable

```php
 <?php
// Check if a remote is readable
$remotes->isReadable($url);
```

## Tags commands
```php
<?php
// You can access tags commands like this
$repository->tags()->method();

// Or save tags command into variable
$tags = $repository->tags();
$tags->method();
```

* Get tags infos

```php
 <?php
// Return arrays
// Get all tags
$tags->all();

// Return boolean
// Check if tag exists
$tags->exists('v1.0.rc01'); // false
// Or
$tags->has('v1.0.rc01'); // false
```

### Fluent methods (can be chained)
* Create tag

```php
 <?php
// create a tag for active branch with name v2.0
$tags->create('v2.0');
// or
$tags->add('v2.0');
```

* Remove tags

```php
 <?php
// To remove a tag
$tags->remove($name);
// or
$tags->delete($name);
```

* Create an alias/Rename tag

```php
 <?php
// To create an alias from tag
$tags->alias($current, $new);

// To rename a tag (make alias and delete)
$tags->rename($oldName, $newName);
// Equivalent to 
$tags->alias($oldName, $newName)->remove($oldName);

```