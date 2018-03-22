<?php

namespace Cyberaxio\GitManager\Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
	public function checkStorageDir()
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