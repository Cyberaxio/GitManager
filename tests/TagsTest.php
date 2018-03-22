<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;
use Cyberaxio\GitManager\GitManagerException;

class TagsTest extends BaseTestCase
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
		$this->tags = $this->repository->tags();
	}

	/**
	 * @test
	 */
	public function it_can_add_tag()
	{
		$tag = 'v1.0';
		$this->assertFalse($this->tags->exists($tag));
		$this->tags->create($tag);
		$this->assertTrue($this->tags->has($tag));
	}

	/**
	 * @test
	 */
	public function it_cannot_add_tag_if_exists()
	{
		$tag = 'v1.0';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Tag [$tag] already exists. Aborting.");

		$this->tags->add($tag);
		$this->assertTrue($this->tags->exists($tag));
		$this->tags->add($tag);
	}

	/**
	 * @test
	 */
	public function it_can_remove_tag()
	{
		$tag = 'v1.0';

		$this->tags->add($tag);
		$this->assertTrue($this->tags->exists($tag));
		$this->tags->remove($tag);
		$this->assertFalse($this->tags->exists($tag));
	}

	/**
	 * @test
	 */
	public function it_can_remove_multiple_tags()
	{
		$tag = 'v1.0';
		$tag2 = 'v2.0';
		$tag3 = 'v3.0';
		$tag4 = 'v4.0';

		$this->assertCount(0, $this->tags->all());
		$this->tags->add($tag);
		$this->tags->add($tag2);
		$this->tags->add($tag3);
		$this->tags->add($tag4);
		$this->assertCount(4, $this->tags->all());
		$this->tags->delete($tag, $tag2, $tag3, $tag4);
		$this->assertCount(0, $this->tags->all());
	}

	/**
	 * @test
	 */
	public function it_can_fail_if_tag_not_exists()
	{
		$tag = 'v1.0';
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Tag [$tag] does not exists. Aborting.");
		$this->tags->checkExists($tag);
	}

	/**
	 * @test
	 */
	public function it_can_alias_tag()
	{
		$tag = 'v1.0';

		$this->assertFalse($this->tags->exists($tag));
		$this->tags->add($tag);
		$this->assertTrue($this->tags->exists($tag));
		$this->tags->alias($tag, 'v1.0.0');
		$this->assertTrue($this->tags->exists($tag));
		$this->assertTrue($this->tags->exists('v1.0.0'));
		$this->assertCount(2, $this->tags->all());
	}

	/**
	 * @test
	 */
	public function it_can_rename_tag()
	{
		$tag = 'v1.0';

		$this->assertFalse($this->tags->exists($tag));
		$this->tags->add($tag);
		$this->assertTrue($this->tags->exists($tag));
		$this->tags->rename($tag, 'v1.0.0');
		$this->assertFalse($this->tags->exists($tag));
		$this->assertTrue($this->tags->exists('v1.0.0'));
		$this->assertCount(1, $this->tags->all());
	}
}