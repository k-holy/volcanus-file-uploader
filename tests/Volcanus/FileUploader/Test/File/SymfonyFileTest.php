<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test\File;

use Volcanus\FileUploader\File\SymfonyFile;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Test for Volcanus\FileUploader\File\SymfonyFile
 *
 * @author k.holy74@gmail.com
 */
class SymfonyFileTest extends \PHPUnit_Framework_TestCase
{

	public function testGetPath()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SymfonyFile(
			new UploadedFile(
				$path,
				$clientFilename = 'テスト.jpg'
			)
		);

		$this->assertEquals($path, $file->getPath());
	}

	public function testGetSize()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SymfonyFile(
			new UploadedFile(
				$path,
				$clientFilename = 'テスト.jpg'
			)
		);

		$this->assertEquals(filesize($path), $file->getSize());
	}

	public function testGetMimeType()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SymfonyFile(
			new UploadedFile(
				$path,
				$clientFilename = 'テスト.jpg'
			)
		);

		$this->assertEquals('image/jpeg', $file->getMimeType());
	}

	public function testGetClientFilename()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SymfonyFile(
			new UploadedFile(
				$path,
				$clientFilename = 'テスト.jpg'
			)
		);

		$this->assertEquals('テスト.jpg', $file->getClientFilename());
	}

	public function testGetClientExtension()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SymfonyFile(
			new UploadedFile(
				$path,
				$clientFilename = 'テスト.jpg'
			)
		);

		$this->assertEquals('jpg', $file->getClientExtension());
	}

	public function testGetError()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SymfonyFile(
			new UploadedFile(
				$path,
				$clientFilename = 'テスト.jpg',
				$mimeType = null,
				$size = null,
				$error = \UPLOAD_ERR_CANT_WRITE
			)
		);

		$this->assertEquals(\UPLOAD_ERR_CANT_WRITE, $file->getError());
	}

	public function testIsValid()
	{
		$path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

		$file = new SymfonyFile(
			new UploadedFile(
				$path,
				$clientFilename = 'テスト.jpg',
				$mimeType = null,
				$size = null,
				$error = \UPLOAD_ERR_OK,
				$test = true
			)
		);

		$this->assertTrue($file->isValid());

	}

}
