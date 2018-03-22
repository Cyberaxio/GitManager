<?php

namespace Cyberaxio\GitManager\Commands;

class WorkingDirectoryCommands
{
	use CommandTrait;

	public function remove($file, $keep = true)
	{
		if ( ! is_array($file)) {
			$file = [$file];
		}

		foreach ($file as $item) {
			$this->command('git rm ' . $item . ($keep == true ? ' --cached ' : null) . ' -rf')
			->run();
		}

		return $this;
	}

	public function add($file)
	{
		if ( ! is_array($file)) {
			$file = func_get_args();
		}

		foreach ($file as $item) {
			$this->command('git add', $item)
			->run();
		}

		return $this;
	}

	public function addAll()
	{
		$this->command('git add --all')
			->run();

		return $this;
	}

	public function rename($file, $to = null)
	{
		if ( ! is_array($file)) {
			$file = [$file => $to];
		}

		foreach ($file as $from => $to) {
			$this->command('git mv', $from, $to)
			->run();
		}

		return $this;
	}

	public function commit($message, $options = null)
	{
		if ( ! is_array($options)) {
			$options = [];
		}
		$process = $this->command('git commit', $options, ['-m' => $message])
			->run();
		return $process;
		return $this;
	}

	public function isDirty()
	{
		$output = $this->command('git status')
			->run()
			->getOutput();

		return false === (strpos(implode('', $output), 'nothing to commit'));
	}

	public function isClean()
	{
		return ! $this->isDirty();
	}
}
