<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;
use Cyberaxio\GitManager\GitManagerException;

class ManagerTest extends BaseTestCase
{
	public function setUp()
	{
		parent::setUp();
		$this->checkStorageDir();
	}

	/**
	 * @test
	 */
	public function it_can_init_repository()
	{
		$path = __DIR__ . '/../storage';
		$this->assertFalse(file_exists($path . '/.git'));
		$repository = Manager::init($path);
		$this->assertTrue(file_exists($path . '/.git'));
	}

	/**
	 * @test
	 */
	public function it_can_find_repository()
	{
		$path = __DIR__ . '/../storage';
		$this->assertFalse(file_exists($path . '/.git'));
		$repository = Manager::init($path)->getPath();

		$this->assertTrue(file_exists($path . '/.git'));

		$repository2 = Manager::find($path)->getPath();

		$this->assertSame($repository, $repository2);
	}

	/**
	 * @test
	 */
	public function it_can_clone_repository()
	{
		$path = __DIR__ . '/../storage';
		$url = "https://github.com/Cyberaxio/GitManager.git";
		$this->assertFalse(file_exists($path . '/test/.git'));
		$repository = Manager::clone($url, $path . '/test', '.');
		$this->assertTrue(file_exists($path . '/test/.git'));
	}

	/**
	 * @test
	 */
	public function it_fail_cloning_if_existing()
	{
		$path = __DIR__ . '/../storage';
		$url = "https://github.com/Cyberaxio/GitManager.git";

		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Target directory is not empty at path " . realpath($path) . '/test' . '/.');

		$repository = Manager::clone($url, $path . '/test', '.');
		$repository2 = Manager::clone($url, $path . '/test', '.');
	}

	/**
	 * @test
	 */
	public function it_can_get_config()
	{
		$repository = Manager::new();

		$this->assertSame(22, $repository->config('port'));
	}
	/**
	 * @test
	 */
	public function it_can_set_config()
	{
		$port = random_int(22, 1000);
		$repository = Manager::new(['port' => $port]);
		$this->assertSame($port, $repository->config('port'));

		$repository->setConfig('port', 20);
		$this->assertSame(20, $repository->config('port'));
	}

	/**
	 * @test
	 */
	public function it_reset_config()
	{
		$port = random_int(50, 1000);
		$repository = Manager::new(['port' => $port]);

		$newRepository = Manager::new();
		$this->assertSame(22, $newRepository->config('port'));
	}
}