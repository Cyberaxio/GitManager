<?php

namespace Cyberaxio\GitManager;

use Cyberaxio\GitManager\Commands\GitCommand;
use Cyberaxio\GitManager\Commands\BranchCommands;
use Cyberaxio\GitManager\Commands\WorkingCopyCommands;

class Repository
{
	private $path;
	private $url;
	private $exists = false;
	private $config = [
		'port' => 22,
		'debug' => false
	];

	public function __construct($path = null, $config = [])
	{
		$this->setConfig($config);

		if ($path) {
			$this->setPath($path);
			$this->exists();
		}


		return $this;
	}

	public function exists()
	{
		return $this->exists = file_exists($this->path . '/.git');
	}

	public function setPath($path): void
	{
		// Check if path point to .git
		$path = $this->formatPath($path);

		// Try get realpath
		if (!$this->config('cloning') && false === ($this->path = realpath($path))) {
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
			$this->config[$key] = $value =! null ? $value : $this->config[$key];
		}
		return $this;
	}

	public function getEnv(): array
	{
		return $this->env;
	}

	public function setEnv($key, $value)
	{
		$this->env[$key] = $value;

		return $this;
	}

	public function debug($status = true)
	{
		$this->setConfig('debug', $status);
		return $this;
	}

	public function setUrl($url = null)
	{
		$this->url = $url;
		return $this;
	}

	public function setPrivateKey($privateKey, $port = null, $wrapper = null): void
	{
		if (null === $wrapper) {
			$wrapper = __DIR__ . '/bin/git-ssh-wrapper.sh';
		}
		if ( ! $wrapperPath = realpath($wrapper)) {
			throw new GitManagerException('Path to GIT_SSH wrapper script could not be resolved: ' . $wrapper);
		}
		if ( ! $privateKeyPath = realpath($privateKey)) {
			throw new GitManagerException('Path private key could not be resolved: ' . $privateKey);
		}
		$this->setEnv('GIT_SSH', $wrapperPath);
		$this->setEnv('GIT_SSH_KEY', $privateKeyPath);
		$this->setPort($port);
		$this->setConfig('privateKey', $privateKey);
	}

	public function setPort($port = null)
	{
		$this->setConfig('port', $port);
		$this->setEnv('GIT_SSH_PORT', $this->config('port'));
		return $this;
	}

	public function clone($path = null, $bare = false)
	{
		if ($path) {
			$this->path = $this->formatPath($path);
		}
		$this->checkRepo();
		if ( ! $this->path) {
			throw new GitManagerException('No path specified');
		}
		$this->command('git clone', ($bare ? '--bare' : null), $this->url, $this->path)
			->setEnv($this->getEnv())
			->run();

		$this->exists();

		return $this;
	}

	private function checkRepo(): void
	{
		if ('ssh' === $this->checkUrlType() && ! $this->config('privateKey')) {
			throw new GitManagerException('Private key not defined');
		}
		if ( ! $this->isReadable($this->url)) {
			throw new GitManagerException("Repository $this->url is not readable.");
		}
	}

	public function checkUrlType()
	{
		return parse_url($this->url, PHP_URL_SCHEME) ?? 'ssh';
	}

	private function isReadable($url)
	{
		$oldDebug = $this->config('debug');
		$this->debug(false);

		$isReadable = 0 === $this->command(
			'GIT_TERMINAL_PROMPT=0 git ls-remote',
			'--heads',
			'--quiet',
			'--exit-code',
			$url
		)->setEnv($this->getEnv())
			->run()->getExitCode();

		$this->debug($oldDebug);

		return $isReadable;
	}

	public function command(...$command)
	{
		return (new GitCommand(...$command))->setCwd($this->getPath())->debug($this->config('debug'));
	}


	public function rawCommand(...$command)
	{
		return $this->command(...$command)->getOutput();
	}

	public function branches()
	{
		return new BranchCommands($this);
	}

	public function workingCopy()
	{
		return new WorkingCopyCommands($this);
	}

	public function gitConfig($key, $value, $global = null)
	{
		// Handle space on value
		if(stripos($value, ' ') !== false){
			$value = '"' . ltrim(rtrim($value, '"'), '"') . '"';
		}
		return $this->command('git config ' . ($global ? '--global' : null), $key, $value)->run();
	}
}