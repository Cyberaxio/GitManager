<?php

namespace Cyberaxio\GitManager;

use Cyberaxio\GitManager\Commands\GitCommand;
use Cyberaxio\GitManager\Commands\BranchCommands;
use Cyberaxio\GitManager\Commands\WorkingCopyCommands;
use Cyberaxio\GitManager\Commands\RemoteCommands;
use Cyberaxio\GitManager\Commands\TagCommands;
use Cyberaxio\GitManager\Commands\CommitCommands;

class Repository
{
	private $path;
	private $name;
	private $url;
	private $env = [];
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
		return $this->exists = file_exists($this->getPath() . '/.git');
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

	public function getFullPath()
	{
		return $this->getPath() . ($this->getName() ? '/' . $this->getName() : null );
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

	public function getUrl()
	{
		return $this->url;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getName()
	{
		return $this->name;
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

	public function clone($path = null, $name = null, $bare = false)
	{
		if ($path) {
			$path = $this->formatPath($path);
			if(! file_exists($path)){
				mkdir($path, 0777, true);
			}
			$this->path = realpath($path);
		}
		if (! $this->getUrl()) {
			throw new GitManagerException('No url specified');
		}
		$this->setName($name ?? $this->getRepoName($this->getUrl()));
		$this->checkRepo();
		if ( glob($this->getFullPath() . '/*')) {
			throw new GitManagerException("Target directory is not empty at path " . $this->getFullPath());
		}
		if ( ! $this->path) {
			throw new GitManagerException('No path specified');
		}
		$this->command('git clone', ($bare ? '--bare' : null), $this->url, $name)
			->setEnv($this->getEnv())
			->run();

		$this->path = $this->getFullPath();
		$this->exists();
		$this->setName(null);

		return $this;
	}

	public function getRepoName($url)
	{
		$scheme = parse_url($url, PHP_URL_SCHEME);
		if (null === $scheme) {
			$parts = explode('/', $url);
			$path = end($parts);
		} else {
			$strpos = strpos($url, ':');
			$path = substr($url, $strpos + 1);
		}

		return basename($path, '.git');
	}
	public function pull($remote = null, array $params = [])
	{
		$this->checkRepo();

		return $this->command('git pull', $remote, $params)
					->setEnv($this->getEnv())->run();
	}

	public function push($remote = null, array $params = [])
	{
		$this->checkRepo();

		return $this->command('git push', $remote, $params)
					->setEnv($this->getEnv())->run();
	}

	public function fetch($remote = null, array $params = [])
	{
		$this->checkRepo();

		return $this->command('git fetch', $remote, $params)
					->setEnv($this->getEnv())->run();
	}

	private function checkRepo(): void
	{
		if ( ! $this->getUrl() ) {
			$this->setUrl($this->remotes()->getUrl('origin'));
		}
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
		return $this->remotes()->isReadable($url);
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

	public function remotes()
	{
		return new RemoteCommands($this);
	}

	public function tags()
	{
		return new TagCommands($this);
	}

	public function commits()
	{
		return new CommitCommands($this);
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