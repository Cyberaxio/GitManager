<?php

namespace Cyberaxio\GitWrapper;

use Cyberaxio\GitWrapper\Commands\GitCommand;

class Manager
{
	private $config = [
		'port' => 22,
		'debug' => false
	];

	private function __construct($config = [])
	{
		$this->setConfig($config);
	}

	public static function new($config = [])
	{
		return new Manager($config);
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
}