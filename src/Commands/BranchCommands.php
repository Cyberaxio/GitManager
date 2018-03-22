<?php

namespace Cyberaxio\GitManager\Commands;

use Cyberaxio\GitManager\GitManagerException;

class BranchCommands
{
	use CommandTrait;

	public function all($parse = true)
	{
		$result = $this->command('git branch -a')->run();

		if ($parse) {
			return $result->parseOutput(function ($value) {
				return trim(substr($value, 1));
			});
		}

		return $result;
	}

	public function local()
	{
		return $this->command('git branch')->run()
			->parseOutput(function ($value) {
				return trim(substr($value, 1));
			});
	}

	public function current()
	{
		$branch = $this->all(false)
		->parseOutput(function ($value) {
			if (isset($value[0]) && '*' === $value[0]) {
				return trim(substr($value, 1));
			}

			return false;
		});
		return is_array($branch) ? $branch[0] : false;
	}

	public function checkout($name, $create = false)
	{
		if ( ! $create && ! $this->exists($name)) {
			throw new GitManagerException("Branch [$name] doesn't exists. Aborting.");
		}
		if ($create && $this->exists($name)) {
			throw new GitManagerException("Branch [$name] already exists. Aborting.");
		}
		$this->command('git checkout', ($create ? '-b' : null), $name)->run();

		return $this;
	}

	public function create($name, $checkout = false)
	{
		if ($this->exists($name)) {
			throw new GitManagerException("Branch [$name] already exists. Aborting.");
		}
		$this->command('git branch', $name)->run();

		if ($checkout) {
			$this->checkout($name);
		}

		return $this;
	}

	public function add($name, $checkout = false)
	{
		return $this->create($name, $checkout);
	}

	public function remove($name)
	{
		if ($this->current() == $name) {
			throw new GitManagerException("Can't remove branch [$name], because it is currently active. Please checkout first.");
		}
		if ( ! $this->exists($name)) {
			throw new GitManagerException("Branch [$name] does not exists. Aborting.");
		}
		$this->command('git branch', ['-d' => $name])->run();

		return $this;
	}

	public function delete($name)
	{
		return $this->remove($name);
	}

	public function merge($name, $options = null)
	{
		if ($this->current() == $name) {
			throw new GitManagerException("Can't merge branch [$name], because it is currently active. Please checkout first.");
		}
		if ( ! $this->exists($name)) {
			throw new GitManagerException("Branch [$name] does not exists. Aborting.");
		}
		$this->command('git merge', $options, $name)->run();

		return $this;
	}

	public function rename($oldName, $newName = null)
	{
		$this->command('git branch -m ', $oldName, $newName)->run();

		return $this;
	}

	public function exists($name)
	{
		return in_array($name, $this->all());
	}

	public function has($name)
	{
		return $this->exists($name);
	}
}
