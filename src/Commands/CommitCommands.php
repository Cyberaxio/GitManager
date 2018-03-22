<?php

namespace Cyberaxio\GitManager\Commands;

use Cyberaxio\GitManager\GitManagerException;

class CommitCommands
{
	use CommandTrait;

	public function last()
	{
		return $this->command('git rev-parse HEAD')->run()->getOutput()[0];
	}
}
