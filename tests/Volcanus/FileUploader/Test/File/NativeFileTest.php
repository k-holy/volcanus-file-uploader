<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test\File;

use Volcanus\FileUploader\File\NativeFile;

/**
 * Test for Volcanus\FileUploader\File\NativeFile
 *
 * @author k.holy74@gmail.com
 */
class NativeFileTest extends \PHPUnit_Framework_TestCase
{

	public function testGetPath()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new NativeFile(array(
			'tmp_name' => $path,
		));

		$this->assertEquals($path, $file->getPath());
	}

	public function testGetSize()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new NativeFile(array(
			'tmp_name' => $path,
		));

		$this->assertEquals(filesize($path), $file->getSize());
	}

	public function testGetMimeType()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new NativeFile(array(
			'tmp_name' => $path,
		));

		$this->assertEquals('image/jpeg', $file->getMimeType());
	}

	public function testGetClientFilename()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new NativeFile(array(
			'tmp_name' => $path,
			'name' => 'テスト.jpg',
		));

		$this->assertEquals('テスト.jpg', $file->getClientFilename());
	}

	public function testGetClientExtension()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new NativeFile(array(
			'tmp_name' => $path,
			'name' => 'テスト.jpg',
		));

		$this->assertEquals('jpg', $file->getClientExtension());
	}

	public function testGetError()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new NativeFile(array(
			'tmp_name' => $path,
			'name' => 'テスト.jpg',
			'error' => \UPLOAD_ERR_CANT_WRITE,
		));

		$this->assertEquals(\UPLOAD_ERR_CANT_WRITE, $file->getError());
	}

	public function testIsValid()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new NativeFile(array(
			'tmp_name' => $path,
			'name' => 'テスト.jpg',
			'error' => \UPLOAD_ERR_OK,
		));

		$this->assertTrue($file->isValid());

	}

}
