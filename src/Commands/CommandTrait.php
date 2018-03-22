<?php

namespace Cyberaxio\GitManager\Commands;

trait CommandTrait
{
	private $repository;
	private $debug = false;

	public function __construct($repository)
	{
		$this->repository = $repository;
	}

	public function repo()
	{
		return $this->repository;
	}

	public function command(...$args)
	{
		return (new GitCommand(...$args))
				->setCwd($this->repo()->getPath())
				->debug($this->debug);
	}

	public function debug()
	{
		$this->debug = true;

		return $this;
	}

	public function getDebug()
	{
		return $this->debug;
	}
}
