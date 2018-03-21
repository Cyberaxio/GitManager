<?php

namespace Cyberaxio\GitManager\Commands;

use Symfony\Component\Process\Process;

class GitCommand
{
	private $args;
	private $debug;
	private $commandLine;
	private $cwd;
	private $process;
	private $outputs;
	private $env;

	public function __construct(...$args)
	{
		$this->args = $args;
		$this->generateCommandLine();
	}

	public function run()
	{
		$this->process = new Process($this->commandLine, $this->cwd);
		if ($this->env) {
			$this->process->setEnv($this->env);
		}
		$this->process->run();
		$this->outputs = [
			'standard' => $this->process->getOutput(),
			'error' => $this->process->getErrorOutput(),
		];
		$this->outputs['both'] = $this->outputs['standard'] . $this->outputs['error'];
		if ($this->debug) {
			var_dump($this);
			die();
		}

		return $this;
	}

	public function debug($debug = false)
	{
		$this->debug = $debug;

		return $this;
	}

	public function getOutput($type = 'both')
	{
		return array_filter(explode("\n", $this->outputs[$type]));
	}

	public function getExitCode()
	{
		return $this->process->getExitCode();
	}

	public function parseOutput($callback)
	{
		return array_values(array_filter(array_map($callback, $this->getOutput())));
	}

	private function generateCommandLine(): void
	{
		$args = $this->args;
		$commandLine = [];
		// First arg is program name
		$programName = array_shift($args);

		// Loop over all args and escape them
		foreach ($args as $arg) {
			// If arg is an array => limited to one level depth
			if (is_array($arg)) {
				foreach ($arg as $key => $value) {
					$optionFlag = '';

					if (is_string($key)) {
						$optionFlag = "$key ";
					}

					$commandLine[] = $optionFlag . escapeshellarg($value);
				}

				// If arg is string or other scalar type but not boolean
			} elseif (is_scalar($arg) && ! is_bool($arg)) {
				$commandLine[] = escapeshellarg($arg);
			}
		}

		$this->commandLine = "$programName " . implode(' ', $commandLine);
	}

	public function setCwd($cwd)
	{
		$this->cwd = $cwd;

		return $this;
	}

	public function setEnv($env)
	{
		$this->env = $env;

		return $this;
	}
}
