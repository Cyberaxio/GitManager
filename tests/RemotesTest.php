<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;
use Cyberaxio\GitManager\GitManagerException;

class RemotesTest extends BaseTestCase
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
	public function it_can_add_remote()
	{
		$this->assertFalse($this->repository->remotes()->exists('origin'));
		$this->repository->remotes()->add('origin', 'https://github.com/Cyberaxio/GitWrapper.git');
		$this->assertTrue($this->repository->remotes()->exists('origin'));
	}

	/**
	 * @test
	 */
	public function it_can_rename_remote()
	{
		$this->assertFalse($this->repository->remotes()->exists('origin'));
		$this->assertFalse($this->repository->remotes()->exists('old_origin'));
		$this->repository->remotes()->create('origin', 'https://github.com/Cyberaxio/GitWrapper.git');
		$this->assertTrue($this->repository->remotes()->exists('origin'));
		$this->repository->remotes()->rename('origin', 'old_origin');
		$this->assertFalse($this->repository->remotes()->exists('origin'));
		$this->assertTrue($this->repository->remotes()->exists('old_origin'));
	}

	/**
	 * @test
	 */
	public function it_can_remove_remote()
	{
		$this->assertFalse($this->repository->remotes()->exists('origin'));
		$this->repository->remotes()->create('origin', 'https://github.com/Cyberaxio/GitWrapper.git');
		$this->assertTrue($this->repository->remotes()->exists('origin'));
		$this->repository->remotes()->remove('origin');
		$this->assertFalse($this->repository->remotes()->exists('origin'));
	}

	/**
	 * @test
	 */
	public function it_can_delete_remote()
	{
		$this->assertFalse($this->repository->remotes()->exists('origin'));
		$this->repository->remotes()->create('origin', 'https://github.com/Cyberaxio/GitWrapper.git');
		$this->assertTrue($this->repository->remotes()->exists('origin'));
		$this->repository->remotes()->delete('origin');
		$this->assertFalse($this->repository->remotes()->exists('origin'));
	}

	/**
	 * @test
	 */
	public function it_can_get_remote_url()
	{
		$this->repository->remotes()->create('origin', 'https://github.com/Cyberaxio/GitWrapper.git');
		$this->assertEquals('https://github.com/Cyberaxio/GitWrapper.git', $this->repository->remotes()->getUrl('origin'));
	}

	/**
	 * @test
	 */
	public function it_can_set_remote_url()
	{
		$this->repository->remotes()->create('origin', 'https://github.com/Cyberaxio/GitWrapper.git');
		$this->assertEquals('https://github.com/Cyberaxio/GitWrapper.git', $this->repository->remotes()->getUrl('origin'));
		$this->repository->remotes()->setUrl('origin', 'https://github.com/Cyberaxio/NewGitWrapper.git');
		$this->assertEquals('https://github.com/Cyberaxio/NewGitWrapper.git', $this->repository->remotes()->getUrl('origin'));
	}

	/**
	 * @test
	 */
	public function it_can_get_all_remotes()
	{
		$this->repository->remotes()->create('origin', 'https://github.com/Cyberaxio/GitWrapper.git');
		$this->repository->remotes()->create('upstream', 'https://github.com/Cyberaxio/OtherGitWrapper.git');
		$this->assertEquals(['origin', 'upstream'], $this->repository->remotes()->all());
	}

	/**
	 * @test
	 */
	public function it_can_check_if_remotes_is_readable()
	{
		$this->assertTrue($this->repository->remotes()->isReadable('https://github.com/Cyberaxio/GitWrapper.git'));
		$this->assertFalse($this->repository->remotes()->isReadable('https://github.com/Cyberaxio/FakeGitWrapper.git'));
	}
}