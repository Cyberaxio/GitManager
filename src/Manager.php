<?php

namespace Cyberaxio\GitManager;

use Cyberaxio\GitManager\Commands\GitCommand;

class Manager
{
	private function __construct()
	{		
	}

	public static function new($config = [])
	{
		return self::find(null, $config);
	}

	public static function find($path = null, $config = [])
	{
		return new Repository($path, $config);
	}

	public static function init($path = null)
	{
		(new GitCommand('git init'))
		->setCwd(realpath($path))
		->run();

		return self::find($path);
	}
}