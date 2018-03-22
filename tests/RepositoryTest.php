<?php

namespace Cyberaxio\GitManager\Tests;

use Cyberaxio\GitManager\Manager;
use Cyberaxio\GitManager\GitManagerException;

class RepositoryTest extends BaseTestCase
{
	public function setUp()
	{
		parent::setUp();
		$this->checkStorageDir();
		$this->repository = Manager::new();
		$this->repository->gitConfig('user.name', 'Test Name');
		$this->repository->gitConfig('user.email', 'test@example.com');
	}

	/**
	 * @test
	 */
	public function it_can_set_repository_path()
	{	
		$path = __DIR__ . '/../storage';

		$this->assertSame(null, $this->repository->getPath());
		$this->repository->setPath($path);
		$this->assertSame(realpath($path), $this->repository->getPath());
	}

	/**
	 * @test
	 */
	public function it_can_set_repository_url()
	{
		$url = "https://github.com/Cyberaxio/GitManager.git";

		$this->assertSame(null, $this->repository->getUrl());
		$this->repository->setUrl($url);
		$this->assertSame($url, $this->repository->getUrl());
	}

	/**
	 * @test
	 */
	public function it_can_get_repository_name_from_url()
	{
		$url = "https://github.com/Cyberaxio/GitManager.git";

		$this->assertSame("GitManager", $this->repository->getRepoName($url));
		$url = "https://github.com/Cyberaxio/AnOtherName.git";
		$this->assertSame("AnOtherName", $this->repository->getRepoName($url));
	}

	/**
	 * @test
	 */
	public function it_can_set_repository_port()
	{
		$port = random_int(23, 1000);

		$this->assertSame(22, $this->repository->config('port'));
		$this->assertArrayNotHasKey('GIT_SSH_PORT', $this->repository->getEnv());
		$this->assertNotContains($port, $this->repository->getEnv());
		
		$this->repository->setPort($port);

		$this->assertSame($port, $this->repository->config('port'));
		$this->assertArrayHasKey('GIT_SSH_PORT', $this->repository->getEnv());
		$this->assertContains($port, $this->repository->getEnv());
		$this->assertSame($port, $this->repository->getEnv()['GIT_SSH_PORT']);
	}

	/**
	 * @test
	 */
	public function it_can_set_repository_private_key()
	{
		$path = __DIR__ . "/../storage/path_to_private_key";
		touch($path);

		$this->assertSame(null, $this->repository->config('privateKey'));
		$this->assertArrayNotHasKey('GIT_SSH_KEY', $this->repository->getEnv());
		$this->assertNotContains($path, $this->repository->getEnv());

		$this->repository->setPrivateKey($path);

		$this->assertSame($path, $this->repository->config('privateKey'));
		$this->assertArrayHasKey('GIT_SSH_KEY', $this->repository->getEnv());
		$this->assertContains(realpath($path), $this->repository->getEnv());
		$this->assertSame(realpath($path), $this->repository->getEnv()['GIT_SSH_KEY']);
	}

	/**
	 * @test
	 */
	public function it_can_set_repository_private_key_and_port()
	{
		$path = __DIR__ . "/../storage/path_to_private_key";
		touch($path);
		$port = random_int(23, 1000);
		$this->assertSame(22, $this->repository->config('port'));
		$this->assertArrayNotHasKey('GIT_SSH_PORT', $this->repository->getEnv());
		$this->assertNotContains($port, $this->repository->getEnv());

		$this->repository->setPrivateKey($path, $port);

		$this->assertSame($port, $this->repository->config('port'));
		$this->assertArrayHasKey('GIT_SSH_PORT', $this->repository->getEnv());
		$this->assertContains($port, $this->repository->getEnv());
		$this->assertSame($port, $this->repository->getEnv()['GIT_SSH_PORT']);
	}

	/**
	 * @test
	 */
	public function it_can_abort_if_private_key_not_readable()
	{
		$path = __DIR__ . "/../storage/path_to_private_key_that_dont_exist";
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage('Path private key could not be resolved: ' . $path);
		
		$this->repository->setPrivateKey($path);
	}

	/**
	 * @test
	 */
	public function it_can_abort_if_wrapper_not_readable()
	{
		$path = __DIR__ . "/../storage/path_to_private_key";
		touch($path);
		$binary = __DIR__ . "/../storage/path_to_other_binary";
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage('Path to GIT_SSH wrapper script could not be resolved: ' . $binary);
		
		$this->repository->setPrivateKey($path, 22, $binary);
	}

	/**
	 * @test
	 */
	public function it_can_clone_public_remote_repository()
	{
		$path = __DIR__ . "/../storage";
		$this->assertFalse(file_exists($path . '/GitManager'));
		$this->assertFalse($this->repository->exists());

		$this->repository->setUrl('https://github.com/Cyberaxio/GitManager.git');
		$this->repository->clone($path);

		$this->assertTrue($this->repository->exists());
		$this->assertTrue(file_exists($path . '/GitManager'));
	}

	/**
	 * @test
	 */
	public function it_can_clone_public_remote_repository_with_alias()
	{
		$path = __DIR__ . "/../storage";
		$this->assertFalse(file_exists($path . '/test_clone'));
		$this->assertFalse($this->repository->exists());

		$this->repository->setUrl('https://github.com/Cyberaxio/GitManager.git');
		$this->repository->clone($path, 'test_clone');

		$this->assertTrue($this->repository->exists());
		$this->assertTrue(file_exists($path . '/test_clone'));
	}

	/**
	 * @test
	 */
	public function it_can_clone_public_remote_repository_in_folder()
	{
		$path = __DIR__ . "/../storage";
		$this->assertFalse(file_exists($path . '/.git'));
		$this->assertFalse($this->repository->exists());
		
		$this->repository->setUrl('https://github.com/Cyberaxio/GitManager.git');
		$this->repository->clone($path, '.');

		$this->assertTrue($this->repository->exists());
		$this->assertTrue(file_exists($path . '/.git'));
	}

	/**
	 * @test
	 */
	public function it_can_fail_cloning_if_not_path_specified()
	{
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage('No path specified');
		$this->repository->setUrl('https://github.com/Cyberaxio/GitManager.git');
		$this->repository->clone();
	}

	/**
	 * @test
	 */
	public function it_return_correct_url_type()
	{
		$this->repository->setUrl('https://github.com/Cyberaxio/GitManager.git');
		$this->assertSame('https', $this->repository->checkUrlType());
		$this->repository->setUrl('http://github.com/Cyberaxio/GitManager.git');
		$this->assertSame('http', $this->repository->checkUrlType());
		$this->repository->setUrl('git@github.com:Cyberaxio/GitManager.git');
		$this->assertSame('ssh', $this->repository->checkUrlType());
		$this->repository->setUrl('file:///home/cyberaxio/Cyberaxio/GitManager.git');
		$this->assertSame('file', $this->repository->checkUrlType());
	}

	/**
	 * @test
	 */
	public function it_check_for_private_key_on_ssh_url()
	{
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage('Private key not defined');
		
		$this->repository->setUrl('git@github.com:Cyberaxio/GitManager.git');
		$this->repository->clone();
	}

	/**
	 * @test
	 */
	public function it_fail_if_url_is_invalid()
	{
		$path = __DIR__ . "/../storage";
		$url = "https://github.com/Cyberaxio/FakeGitManager.git";
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Repository $url is not readable.");

		$this->repository->setUrl($url);
		$this->repository->clone($path);
	}

	/**
	 * @test
	 */
	public function it_fail_cloning_if_folder_not_empty()
	{
		$path = __DIR__ . "/../storage";
		touch($path. '/file');
		$url = "https://github.com/Cyberaxio/GitManager.git";
		$this->expectException(GitManagerException::class);
		$this->expectExceptionMessage("Target directory is not empty at path " . realpath($path) . '/.');

		$this->repository->setUrl($url);
		$this->repository->clone($path, '.');
	}

	/**
	 * @test
	 */
	public function it_can_fetch_remote()
	{
		$path = __DIR__ . "/../storage";
		$remote = Manager::init($path . "/testremote");

		$remote->gitConfig('user.name', 'Test Name');
		$remote->gitConfig('user.email', 'test@example.com');
		touch($remote->getPath() . '/file1');
		$remote->workingCopy()->addAll();
		$remote->workingCopy()->commit('Add file1');

		$local = $this->repository;

		$local->setUrl('file://' . $remote->getPath());
		$local->clone($path, "testfetch");

		$this->assertSame('file://' . $remote->getPath(), $local->remotes()->getUrl('origin'));
		$lastCommitLocal = $local->commits()->last();
		$lastCommitRemote = $remote->commits()->last();
		$this->assertSame($lastCommitLocal, $lastCommitRemote);

		touch($remote->getPath() . '/file2');
		$remote->workingCopy()->addAll();
		$remote->workingCopy()->commit('Add file2');
		$newCommitRemote = $remote->commits()->last();
		$this->assertNotSame($lastCommitLocal, $newCommitRemote);

		$lastRefRemote = explode(' ', $local->command('git show-ref')->run()->parseOutput(function($item){
			if(stripos($item, "refs/remotes/origin/master") !== false){
				return $item;
			}
			return false;
		})[0])[0];

	
		$this->assertSame($lastCommitRemote, $lastRefRemote);


		$local->fetch();
		$newRefRemote = explode(' ', $local->command('git show-ref')->run()->parseOutput(function($item){
			if(stripos($item, "refs/remotes/origin/master") !== false){
				return $item;
			}
			return false;
		})[0])[0];

		// Check local was not updated
		$this->assertNotSame($local->commits()->last(), $newCommitRemote);
		// But refs does
		$this->assertSame($newCommitRemote, $newRefRemote);
	}

	/**
	 * @test
	 */
	public function it_can_pull_from_remote()
	{
		$path = __DIR__ . "/../storage";
		$remote = Manager::init($path . "/testremote");
		$remote->gitConfig('user.name', 'Test Name');
		$remote->gitConfig('user.email', 'test@example.com');
		touch($remote->getPath() . '/file1');
		$remote->workingCopy()->addAll();
		$remote->workingCopy()->commit('Add file1');

		$local = $this->repository;

		$local->setUrl('file://' . $remote->getPath());
		$local->clone($path, "testpull");

		$lastCommitRemote = $remote->commits()->last();
		$lastCommitLocal = $local->commits()->last();

		$this->assertSame($lastCommitLocal, $lastCommitRemote);

		touch($remote->getPath() . '/file2');
		$remote->workingCopy()->addAll();
		$remote->workingCopy()->commit('Add file2');

		$newCommitRemote = $remote->commits()->last();
		$this->assertNotSame($lastCommitLocal, $newCommitRemote);
		$this->assertFalse(file_exists($local->getPath() . '/file2'));

		$local->pull();
		
		$this->assertTrue(file_exists($local->getPath() . '/file2'));
		$this->assertSame($newCommitRemote, $local->commits()->last());
	}

	/**
	 * @test
	 */
	public function it_can_push_to_remote()
	{
		$path = __DIR__ . "/../storage";
		$remote = Manager::init($path . "/testremote");
		$remote->gitConfig('user.name', 'Test Name');
		$remote->gitConfig('user.email', 'test@example.com');
		touch($remote->getPath() . '/file1');
		$remote->workingCopy()->addAll();
		$remote->workingCopy()->commit('Add file1');

		$local = $this->repository;
		$local->gitConfig('push.default', 'simple');

		$local->setUrl('file://' . $remote->getPath());
		$local->clone($path, "testpull");

		$lastCommitRemote = $remote->commits()->last();
		$lastCommitLocal = $local->commits()->last();

		$this->assertSame($lastCommitLocal, $lastCommitRemote);

		touch($local->getPath() . '/file2');
		$local->workingCopy()->addAll();
		$local->workingCopy()->commit('Add file2');

		$newCommitLocal = $local->commits()->last();
		$this->assertNotSame($lastCommitRemote, $newCommitLocal);
		$this->assertFalse(file_exists($remote->getPath() . '/file2'));

		// Switch branches to avoid error
		$remote->branches()->checkout('other_branch', true);
		$local->push();
		$remote->branches()->checkout('master');
		
		$this->assertTrue(file_exists($remote->getPath() . '/file2'));
		$this->assertSame($newCommitLocal, $remote->commits()->last());
	}
}