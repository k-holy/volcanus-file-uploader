<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test;

use Volcanus\FileUploader\Uploader;
use Volcanus\FileUploader\Exception\UploaderException;

/**
 * Test for Volcanus\FileUploader\Uploader
 *
 * @author k.holy74@gmail.com
 */
class UploaderTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenMoveRetryIsNotDigit()
	{
		$uploader = new Uploader();
		$uploader->config('moveRetry', 'foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenMoveDirectoryIsNotString()
	{
		$uploader = new Uploader();
		$uploader->config('moveDirectory', true);
	}

	public function testConfigMoveDirectory()
	{
		$directory = '/path/to/moveDirectory';
		$uploader = new Uploader();
		$uploader->config('moveDirectory', $directory);
		$this->assertEquals($directory, $uploader->config('moveDirectory'));
	}

	public function testValidate()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');
		$validator->expects($this->any())
			->method('validateUploadError')
			->will($this->returnValue(true));
		$validator->expects($this->any())
			->method('validateFilename')
			->will($this->returnValue(true));
		$validator->expects($this->any())
			->method('validateImageType')
			->will($this->returnValue(true));
		$validator->expects($this->any())
			->method('validateFilesize')
			->will($this->returnValue(true));
		$validator->expects($this->any())
			->method('validateExtension')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testMove()
	{
		$orig_path = realpath(__DIR__ . '/Fixtures/this-is.jpg');
		$temp_path = $this->copyToTemp($orig_path);

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue($temp_path));

		$uploader = new Uploader(array(
			'moveDirectory' => __DIR__,
			'moveRetry'     => 1,
		));

		$moved_path = $uploader->move($file);

		$this->assertFileEquals($moved_path, $orig_path);
		$this->assertFileNotExists($temp_path);
		$this->assertRegExp('/\A[a-z0-9]{13}\.jpg\z/i', basename($moved_path));
		unlink($moved_path);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\UploaderException
	 */
	public function testMoveRaiseExceptionWhenFileIsNotValid()
	{
		$orig_path = realpath(__DIR__ . '/Fixtures/this-is.jpg');
		$temp_path = $this->copyToTemp($orig_path);

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$uploader = new Uploader(array(
			'moveDirectory' => __DIR__,
			'moveRetry'     => 1,
		));

		$uploader->move($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\UploaderException
	 */
	public function testMoveRaiseExceptionWhenDirectoryCouldNotWrite()
	{
		$orig_path = realpath(__DIR__ . '/Fixtures/this-is.jpg');
		$temp_path = $this->copyToTemp($orig_path);

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue($temp_path));

		$uploader = new Uploader(array(
			'moveDirectory' => '/',
			'moveRetry'     => 1,
		));

		$uploader->move($file);
	}

	private function copyToTemp($path)
	{
		$temp_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($path);
		copy($path, $temp_path);
		return $temp_path;
	}

}
