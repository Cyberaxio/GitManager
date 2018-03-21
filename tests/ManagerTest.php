<?php

namespace Cyberaxio\GitWrapper\Tests;

use Cyberaxio\GitWrapper\Manager;

class ManagerTest extends BaseTestCase
{
	public function setUp()
	{
		parent::setUp();
	}

	/**
	 * @test
	 */
	public function it_can_get_config()
	{
		$manager = Manager::new();

		$this->assertSame(22, $manager->config('port'));
	}
	/**
	 * @test
	 */
	public function it_can_set_config()
	{
		$port = random_int(22, 1000);
		$manager = Manager::new(['port' => $port]);

		$this->assertSame($port, $manager->config('port'));

		$manager->setConfig('port', 20);
		$this->assertSame(20, $manager->config('port'));
	}

	/**
	 * @test
	 */
	public function it_reset_config()
	{
		$port = random_int(50, 1000);
		$manager = Manager::new(['port' => $port]);

		$newManager = Manager::new();
		$this->assertSame(22, $newManager->config('port'));
	}
}