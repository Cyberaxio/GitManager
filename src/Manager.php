<?php

namespace Cyberaxio\GitManager;

use Cyberaxio\GitManager\Commands\GitCommand;

class Manager
{
	private function __construct()
	{}

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
		if(! file_exists($path)){
			mkdir($path, 0777, true);
		}
		$path = realpath($path);
		(new GitCommand('git init'))
		->setCwd($path)
		->run();

		return self::find($path);
	}

	public static function clone($url, $path, $privateKey = null, $port = 22)
	{
		$repository = self::find($path, ['cloning' => true]);
		if ($repository->exists()) {
			return $repository;
		}
		$repository->setUrl($url);
		$repository->setPort($port);
		$repository->setPrivateKey($privateKey);
		$repository->clone($path);

		return $repository;
	}
}