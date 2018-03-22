<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;
use Cyberaxio\GitManager\GitManagerException;

class WorkingDirectoryTest extends BaseTestCase
{
	public function setUp()
	{
		parent::setUp();
		$path = __DIR__ . '/../storage/test';
		$this->checkStorageDir();
		$this->repository = Manager::init($path);
		$this->repository->gitConfig('user.name', 'Test Name');
		$this->repository->gitConfig('user.email', 'test@example.com');
	}

	/**
	 * @test
	 */
	public function it_can_check_if_repository_is_dirty()
	{
		$this->assertFalse($this->repository->workingDirectory()->isDirty());
		$this->assertTrue($this->repository->workingDirectory()->isClean());
		touch($this->repository->getPath() . '/new.file');
		$this->assertTrue($this->repository->workingDirectory()->isDirty());
		$this->assertFalse($this->repository->workingDirectory()->isClean());
	}

	/**
	 * @test
	 */
	public function it_can_add_file_to_staging()
	{
		touch($this->repository->getPath() . '/new.file');
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingDirectory()->add('new.file');
		$this->assertSame(['A  new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
	}

	/**
	 * @test
	 */
	public function it_can_add_all_files_to_staging()
	{
		touch($this->repository->getPath() . '/new.file');
		touch($this->repository->getPath() . '/new.second.file');
		$this->assertSame(['?? new.file', '?? new.second.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingDirectory()->addAll();
		$this->assertSame(['A  new.file', 'A  new.second.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
	}

	/**
	 * @test
	 */
	public function it_can_commit_files()
	{
		$this->assertTrue($this->repository->workingDirectory()->isClean());
		touch($this->repository->getPath() . '/new.file');
		$this->assertTrue($this->repository->workingDirectory()->isDirty());
		$this->repository->workingDirectory()->add('new.file');
		$this->repository->workingDirectory()->commit('First Commit : Add new.file');
		$this->assertTrue($this->repository->workingDirectory()->isClean());
		$this->assertSame(['First Commit : Add new.file'], $this->repository->command('git log -1 --pretty=%B | cat')->run()->getOutput());
	}

	/**
	 * @test
	 */
	public function it_can_rename_files()
	{
		$path = $this->repository->getPath();
		touch($path . '/new.file');
		$this->assertFalse(file_exists($path . '/renamed.file'));
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingDirectory()->add('new.file');
		$this->repository->workingDirectory()->rename('new.file', 'renamed.file');
		$this->assertSame(['A  renamed.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->assertTrue(file_exists($path . '/renamed.file'));
	}

	/**
	 * @test
	 */
	public function it_can_rename_files_as_array()
	{
		$path = $this->repository->getPath();
		touch($path . '/new.file');
		$this->assertFalse(file_exists($path . '/renamed.file'));
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingDirectory()->add('new.file');
		$this->repository->workingDirectory()->rename(['new.file' => 'renamed.file']);
		$this->assertSame(['A  renamed.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->assertTrue(file_exists($path . '/renamed.file'));
	}

	/**
	 * @test
	 */
	public function it_can_remove_file_from_staging()
	{
		touch($this->repository->getPath() . '/new.file');
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		
		$this->repository->workingDirectory()->add('new.file');
		$this->assertSame(['A  new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		
		$process = $this->repository->workingDirectory()->remove('new.file', true);
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput());
	}

	/**
	 * @test
	 */
	public function it_can_remove_file_from_filesystem()
	{
		touch($this->repository->getPath() . '/new.file');
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingDirectory()->add('new.file');
		$this->assertSame(['A  new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingDirectory()->remove('new.file', false);
		$this->assertSame([], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->assertFalse(file_exists($this->repository->getPath() . '/new.file'));
	}
}