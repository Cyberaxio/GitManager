<?php

namespace Cyberaxio\GitManager\Commands;

class RemoteCommands
{
	use CommandTrait;

	public function add($name, $url, array $options = null)
	{
		$this->command('git remote add', $options, $name, $url)->run();

		return $this;
	}

	public function create($name, $url, array $options = null)
	{
		return $this->add($name, $url, $options);
	}

	public function rename($oldName, $newName)
	{
		$this->command('git remote rename', $oldName, $newName)->run();

		return $this;
	}

	public function remove($name)
	{
		$this->command('git remote remove', $name)->run();

		return $this;
	}

	public function delete($name)
	{
		return $this->remove($name);
	}

	public function getUrl($name = "origin")
	{
		return $this->command('git remote get-url', $name)->run()->getOutput()[0];
	}

	public function setUrl($name, $url, array $options = null)
	{
		$this->command('git remote set-url', $name, $options,  $url)->run();

		return $this;
	}

	public function all()
	{
		return $this->command('git remote')->run()->getOutput();
	}

	public function exists($name)
	{
		return in_array($name, $this->all());
	}

	public function isReadable($url, $refs = null)
	{
		return 0 === $this->command(
			'GIT_TERMINAL_PROMPT=0 git ls-remote',
				'--heads',
				'--quiet',
				'--exit-code',
				$url,
				$refs
		)->run()->getExitCode();
	}
}
