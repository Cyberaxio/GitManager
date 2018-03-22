<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;
use Cyberaxio\GitManager\GitManagerException;

class WorkingCopyTest extends BaseTestCase
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
		$this->assertFalse($this->repository->workingCopy()->isDirty());
		$this->assertTrue($this->repository->workingCopy()->isClean());
		touch($this->repository->getPath() . '/new.file');
		$this->assertTrue($this->repository->workingCopy()->isDirty());
		$this->assertFalse($this->repository->workingCopy()->isClean());
	}

	/**
	 * @test
	 */
	public function it_can_add_file_to_staging()
	{
		touch($this->repository->getPath() . '/new.file');
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingCopy()->add('new.file');
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
		$this->repository->workingCopy()->addAll();
		$this->assertSame(['A  new.file', 'A  new.second.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
	}

	/**
	 * @test
	 */
	public function it_can_commit_files()
	{
		$this->assertTrue($this->repository->workingCopy()->isClean());
		touch($this->repository->getPath() . '/new.file');
		$this->assertTrue($this->repository->workingCopy()->isDirty());
		$this->repository->workingCopy()->add('new.file');
		$this->repository->workingCopy()->commit('First Commit : Add new.file');
		$this->assertTrue($this->repository->workingCopy()->isClean());
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
		$this->repository->workingCopy()->add('new.file');
		$this->repository->workingCopy()->rename('new.file', 'renamed.file');
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
		$this->repository->workingCopy()->add('new.file');
		$this->repository->workingCopy()->rename(['new.file' => 'renamed.file']);
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
		
		$this->repository->workingCopy()->add('new.file');
		$this->assertSame(['A  new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		
		$process = $this->repository->workingCopy()->remove('new.file', true);
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput());
	}

	/**
	 * @test
	 */
	public function it_can_remove_file_from_filesystem()
	{
		touch($this->repository->getPath() . '/new.file');
		$this->assertSame(['?? new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingCopy()->add('new.file');
		$this->assertSame(['A  new.file'], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->repository->workingCopy()->remove('new.file', false);
		$this->assertSame([], $this->repository->command('git status --porcelain')->run()->getOutput('stdout'));
		$this->assertFalse(file_exists($this->repository->getPath() . '/new.file'));
	}
}