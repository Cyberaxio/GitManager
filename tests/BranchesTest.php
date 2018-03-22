<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;
use Cyberaxio\GitManager\GitManagerException;

class BranchesTest extends BaseTestCase
{
	public function setUp()
	{
		parent::setUp();
		$path = __DIR__ . '/../storage/test';
		$this->checkStorageDir();
		$this->repository = Manager::init($path);
		touch($path . '/new.file');
		$this->repository->workingCopy()->add($path . '/new.file');
		$this->repository->gitConfig('user.name', 'Test Name');
		$this->repository->gitConfig('user.email', 'test@example.com');
		$this->repository->workingCopy()->commit('Add new.file');
	}

	/**
	 * @test
	 */
	public function it_can_get_branches()
	{
		$branches = $this->repository->branches()->all();
		$branchesLocal = $this->repository->branches()->local();
		$this->assertSame(['master'], $branches);
		$this->assertSame($branches, $branchesLocal);
		$this->assertSame(['master'], $branchesLocal);

		$this->assertSame('master', $this->repository->branches()->current());
	}

	/**
	 * @test
	 */
	public function it_can_create_branch()
	{
		$this->assertFalse($this->repository->branches()->exists('new_branch'));

		$this->repository->branches()->add('new_branch');

		$this->assertTrue($this->repository->branches()->exists('new_branch'));
		
		$branches = $this->repository->branches()->all();
		$this->assertCount(2, $branches);
		$this->assertSame(['master', 'new_branch'], $branches);
	}

	/**
	 * @test
	 */
	public function it_can_create_branch_and_checkout()
	{
		$this->assertSame('master', $this->repository->branches()->current());
		$this->assertFalse($this->repository->branches()->exists('new_branch'));

		$this->repository->branches()->add('new_branch', true);

		$this->assertTrue($this->repository->branches()->exists('new_branch'));
		$this->assertSame('new_branch', $this->repository->branches()->current());
	}

	/**
	 * @test
	 */
	public function it_can_abort_create_branch_if_exists()
	{
		$name = 'existing_branch';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Branch [$name] already exists. Aborting.");

		$this->repository->branches()->add('existing_branch');
		$this->repository->branches()->add('existing_branch');
	}

	/**
	 * @test
	 */
	public function it_can_delete_branch()
	{
		$this->repository->branches()->add('new_branch');
		$this->assertTrue($this->repository->branches()->exists('new_branch'));

		$this->repository->branches()->remove('new_branch');
		$branches = $this->repository->branches()->all();

		$this->assertFalse($this->repository->branches()->exists('new_branch'));
		$this->assertCount(1, $branches);
	}

	/**
	 * @test
	 */
	public function it_can_prevent_delete_branch_if_current()
	{
		$name = 'new_branch';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Can't remove branch [$name], because it is currently active. Please checkout first.");
		
		$this->repository->branches()->add('new_branch', true);

		$this->assertSame('new_branch', $this->repository->branches()->current());

		$this->repository->branches()->delete('new_branch');
	}

	/**
	 * @test
	 */
	public function it_can_prevent_delete_branch_if_doesnt_exists()
	{
		$name = 'new_branch';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Branch [$name] does not exists. Aborting.");
		
		$this->assertFalse($this->repository->branches()->exists('new_branch'));

		$this->repository->branches()->delete('new_branch');
	}

	/**
	 * @test
	 */
	public function it_can_checkout_branches()
	{
		$this->repository->branches()->add('new_branch');
		$this->assertSame('master', $this->repository->branches()->current());

		$this->repository->branches()->checkout('new_branch');

		$this->assertSame('new_branch', $this->repository->branches()->current());

		$this->assertFalse($this->repository->branches()->has('create_and_checkout_branch'));
		$this->repository->branches()->checkout('create_and_checkout_branch', true);
		$this->assertTrue($this->repository->branches()->has('create_and_checkout_branch'));
	}

	/**
	 * @test
	 */
	public function it_can_prevent_checkout_on_non_existing_branches()
	{
		$name = 'new_branch';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Branch [$name] doesn't exists. Aborting.");
		
		$this->repository->branches()->checkout('new_branch');
	}

	/**
	 * @test
	 */
	public function it_can_prevent_checkout_on_existing_branches()
	{
		$this->repository->branches()->add('existing_branch');

		$name = 'existing_branch';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Branch [$name] already exists. Aborting.");

		$this->repository->branches()->checkout('existing_branch', true);
	}

	/**
	 * @test
	 */
	public function it_can_rename_branches()
	{
		$this->repository->branches()->add('new_branch');
		$this->assertFalse($this->repository->branches()->has('renamed_branch'));
		$this->assertTrue($this->repository->branches()->has('new_branch'));

		$this->repository->branches()->rename('new_branch', 'renamed_branch');
		$this->assertTrue($this->repository->branches()->has('renamed_branch'));
	}

	/**
	 * @test
	 */
	public function it_can_merge_branches()
	{
		$this->repository->branches()->add('new_branch', true);

		$this->assertFalse(file_exists($this->repository->getPath() . '/new.file.in.newbranch'));
		
		touch($this->repository->getPath() . '/new.file.in.newbranch');
		
		$this->repository->workingCopy()->add('new.file.in.newbranch');
		$this->repository->workingCopy()->commit('Add new.file.in.newbranch');
		$this->assertTrue(file_exists($this->repository->getPath() . '/new.file.in.newbranch'));

		$this->repository->branches()->checkout('master');
		$this->assertFalse(file_exists($this->repository->getPath() . '/new.file.in.newbranch'));

		$this->repository->branches()->merge('new_branch');
		$this->assertTrue(file_exists($this->repository->getPath() . '/new.file.in.newbranch'));
	}

	/**
	 * @test
	 */
	public function it_can_prevent_merge_branches_if_active()
	{
		$this->repository->branches()->add('new_branch', true);
		$name = 'new_branch';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Can't merge branch [$name], because it is currently active. Please checkout first.");

		$this->repository->branches()->merge('new_branch');
	}

	/**
	 * @test
	 */
	public function it_can_prevent_merge_branches_if_doesnt_exists()
	{
		$name = 'new_branch';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Branch [$name] does not exists. Aborting.");

		$this->repository->branches()->merge('new_branch');
	}

	/**
	 * @test
	 */
	public function it_can_debug_command()
	{
		$this->assertFalse($this->repository->branches()->getDebug());
		$this->assertTrue($this->repository->branches()->debug()->getDebug());
	}
}