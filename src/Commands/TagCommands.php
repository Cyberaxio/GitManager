<?php

namespace Cyberaxio\GitManager\Commands;

use Cyberaxio\GitManager\GitManagerException;

class TagCommands
{
	use CommandTrait;

	public function all()
	{
		$result = $this->command('git tag')->run();

		return $result->parseOutput(function ($value) {
			return $value;
		});
	}

	public function create($name)
	{
		if ($this->exists($name)) {
			throw new GitManagerException("Tag [$name] already exists. Aborting.");
		}
		$this->command('git tag', $name)->run();

		return $this;
	}

	public function checkExists($name)
	{
		if ( ! $this->exists($name)) {
			throw new GitManagerException("Tag [$name] does not exists. Aborting.");
		}
	}

	public function add($name)
	{
		return $this->create($name);
	}

	public function exists($name)
	{
		return in_array($name, $this->all());
	}

	public function has($name)
	{
		return $this->exists($name);
	}

	public function remove(...$name)
	{
		foreach ($name as $tag) {
			$this->checkExists($tag);
			$this->command('git tag', ['-d' => $tag])->run();
		}

		return $this;
	}

	public function delete(...$name)
	{
		return $this->remove(...$name);
	}

	public function rename($oldName, $newName)
	{
		$this->checkExists($oldName);

		return $this->alias($oldName, $newName)->remove($oldName);
	}

	public function alias($current, $new)
	{
		$this->checkExists($current);
		$this->command('git tag', $new, $current)->run();

		return $this;
	}
}
