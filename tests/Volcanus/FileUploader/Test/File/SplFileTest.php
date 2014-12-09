<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test\File;

use Volcanus\FileUploader\File\SplFile;

/**
 * Test for Volcanus\FileUploader\File\SplFile
 *
 * @author k.holy74@gmail.com
 */
class SplFileTest extends \PHPUnit_Framework_TestCase
{

	public function testGetPath()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path)
		);

		$this->assertEquals($path, $file->getPath());
	}

	public function testGetSize()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path)
		);

		$this->assertEquals(filesize($path), $file->getSize());
	}

	public function testGetMimeType()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path)
		);

		$this->assertEquals('image/jpeg', $file->getMimeType());
	}

	public function testGetClientFilename()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path),
			$clientFilename = 'テスト.jpg'
		);

		$this->assertEquals('テスト.jpg', $file->getClientFilename());
	}

	public function testGetClientExtension()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path),
			$clientFilename = 'テスト.jpg'
		);

		$this->assertEquals('jpg', $file->getClientExtension());
	}

	public function testGetError()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path),
			$clientFilename = 'テスト.jpg',
			$error = \UPLOAD_ERR_CANT_WRITE
		);

		$this->assertEquals(\UPLOAD_ERR_CANT_WRITE, $file->getError());
	}

	public function testIsValid()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path),
			$clientFilename = 'テスト.jpg',
			$error = \UPLOAD_ERR_OK
		);

		$this->assertTrue($file->isValid());

	}

}
