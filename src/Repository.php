<?php

namespace Cyberaxio\GitManager;

class Repository
{
	private $path;
	private $config = [
		'port' => 22,
		'debug' => false
	];

	public function __construct($path = null, $config = [])
	{
		if ($path) {
			$this->setPath($path);
			$this->exists = true;
		}

		$this->setConfig($config);

		return $this;
	}

	public function setPath($path): void
	{
		// Check if path point to .git
		$path = $this->formatPath($path);

		// Try get realpath
		if (false === ($this->path = realpath($path))) {
			throw new GitManagerException("Repository '$path' not found.");
		}
	}

	private function formatPath($path)
	{
		return '.git' === basename($path) ? dirname($path) : $path;
	}

	public function getPath()
	{
		return $this->path;
	}


	public function config($key = null)
	{
		if($key){
			return $this->config[$key] ?? null;
		}
		return $this->config;
	}

	public function setConfig($key, $value = null)
	{
		if(is_array($key)){
			$this->config = array_merge($this->config, $key);
		}else{
			$this->config[$key] = $value;
		}
		return $this;
	}

	public function debug($status = true)
	{
		$this->setConfig('debug', $status);
		return $this;
	}
}