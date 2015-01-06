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

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructorRaiseExceptionWhenFileNotFound()
	{
		$file = new SplFile(
			 new \SplFileInfo('/file/not/found')
		);
	}

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

	public function testMove()
	{
		$orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
		$temp_path = $this->copyToTemp($orig_path);

		$file = new SplFile(
			new \SplFileInfo($temp_path),
			$clientFilename = 'テスト.jpg',
			$error = \UPLOAD_ERR_OK
		);

		$moved_path = $file->move(__DIR__, 'test.jpg');

		$this->assertFileEquals($moved_path, $orig_path);
		$this->assertFileNotExists($temp_path);
		unlink($moved_path);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilepathException
	 */
	public function testMoveRaiseExceptionWhenUploadedFileIsError()
	{
		$orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
		$temp_path = $this->copyToTemp($orig_path);

		$file = new SplFile(
			new \SplFileInfo($temp_path),
			$clientFilename = 'テスト.jpg',
			$error = \UPLOAD_ERR_CANT_WRITE
		);

		$moved_path = $file->move(__DIR__, 'test.jpg');
	}

	public function testGetContent()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path),
			$clientFilename = 'テスト.jpg'
		);

		$this->assertEquals(file_get_contents($path), $file->getContent());
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilepathException
	 */
	public function testGetContentRaiseExceptionWhenUploadedFileIsError()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path),
			$clientFilename = 'テスト.jpg',
			$error = \UPLOAD_ERR_CANT_WRITE
		);

		$file->getContent();
	}

	public function testGetContentAsDataUri()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SplFile(
			new \SplFileInfo($path),
			$clientFilename = 'テスト.jpg'
		);

		$this->assertStringStartsWith('data:image/jpeg;base64,', $file->getContentAsDataUri());
	}

	private function copyToTemp($path)
	{
		$temp_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($path);
		copy($path, $temp_path);
		return $temp_path;
	}

}
