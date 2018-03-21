<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;

class ManagerTest extends BaseTestCase
{
	public function setUp()
	{
		parent::setUp();
	}

	/**
	 * @test
	 */
	public function it_can_init_repository()
	{
		$this->checkStorageDir();
		$path = __DIR__ . '/../storage';
		$this->assertFalse(file_exists($path . '/.git'));
		$repository = Manager::init($path);
		$this->assertTrue(file_exists($path . '/.git'));
		$this->checkStorageDir();
	}

	/**
	 * @test
	 */
	public function it_can_find_repository()
	{
		$this->checkStorageDir();
		$path = __DIR__ . '/../storage';
		$this->assertFalse(file_exists($path . '/.git'));
		$repository = Manager::init($path)->getPath();

		$this->assertTrue(file_exists($path . '/.git'));

		$repository2 = Manager::find($path)->getPath();

		$this->assertSame($repository, $repository2);
		$this->checkStorageDir();
	}


	// /**
	//  * @test
	//  */
	// public function it_can_clone_repository()
	// {
	// 	$this->checkStorageDir();
	// 	$path = __DIR__ . '/../storage';
	// 	$repository = Manager::init($path . '/test');

	// 	$this->assertFalse(file_exists($path . '/test2/.git'));

	// 	Manager::clone('file://' . realpath($path. '/test'), $path . '/test2');

	// 	$this->assertTrue(file_exists($path . '/test2/.git'));
	// }

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

	private function checkStorageDir()
	{
		$dir = __DIR__ . '/../storage';
		if(file_exists($dir)){
			$it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
			$it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
			foreach($it as $file) {
				if ($file->isDir()) rmdir($file->getPathname());
				else unlink($file->getPathname());
			}
		}else{
			mkdir($dir);
		}
	}
}