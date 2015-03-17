<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test;

use Volcanus\FileUploader\Uploader;
use Volcanus\FileUploader\Exception\FilepathException;

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

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenUnsupportedConfig()
	{
		$uploader = new Uploader();
		$uploader->config('unsupported-config', 'foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenInvalidArgumentCount()
	{
		$uploader = new Uploader();
		$uploader->config('moveDirectory', 'foo', 'bar');
	}

	public function testConfigMoveDirectory()
	{
		$directory = '/path/to/moveDirectory';
		$uploader = new Uploader();
		$uploader->config('moveDirectory', $directory);
		$this->assertEquals($directory, $uploader->config('moveDirectory'));
	}

	public function testValidateCallClearErrors()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->once())
			->method('clearErrors');

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testValidateCallValidateUploadError()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->once())
			->method('validateUploadError')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testValidateCallValidateFilenameWhenConfigHasFilenameEncoding()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->at(2))
			->method('config')
			->with($this->identicalTo('filenameEncoding'))
			->will($this->returnValue('UTF-8'));

		$validator->expects($this->once())
			->method('validateFilename')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testValidateCallValidateFilesizeWhenConfigHasMaxFilesize()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->at(3))
			->method('config')
			->with($this->identicalTo('maxFilesize'))
			->will($this->returnValue('1M'));

		$validator->expects($this->once())
			->method('validateFilesize')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testValidateCallValidateExtensionWhenConfigHasAllowableType()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->at(4))
			->method('config')
			->with($this->identicalTo('allowableType'))
			->will($this->returnValue('jpg'));

		$validator->expects($this->once())
			->method('validateExtension')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testValidateCallValidateImageTypeWhenFileIsImage()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->once())
			->method('validateImageType')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testValidateCallValidateImageSizeWhenFileIsImageAndConfigHasMaxWidth()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->at(6))
			->method('config')
			->with($this->identicalTo('maxWidth'))
			->will($this->returnValue(180));

		$validator->expects($this->once())
			->method('validateImageSize')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testValidateCallValidateImageSizeWhenFileIsImageAndConfigHasMaxHeight()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));

		$validator = $this->getMock('\Volcanus\FileUploader\FileValidator');

		$validator->expects($this->at(7))
			->method('config')
			->with($this->identicalTo('maxHeight'))
			->will($this->returnValue(180));

		$validator->expects($this->once())
			->method('validateImageSize')
			->will($this->returnValue(true));

		$uploader = new Uploader();

		$this->assertTrue($uploader->validate($file, $validator));
	}

	public function testMove()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));
		$file->expects($this->once())
			->method('move')
			->will($this->returnCallback(function($directory, $filename) {
				return $directory . '/' . $filename;
			}));

		$uploader = new Uploader(array(
			'moveDirectory' => __DIR__,
			'moveRetry'     => 1,
		));

		$moved_path = $uploader->move($file);

		$this->assertRegExp('~/[a-f0-9]{40}\.jpg\z~i', $moved_path);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\UploaderException
	 */
	public function testMoveRaiseExceptionWhenFileIsNotValid()
	{
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
	public function testMoveRaiseExceptionWhenAllRetryFailed()
	{
		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));
		$file->expects($this->any())
			->method('move')
			->will($this->throwException(new FilepathException()));

		$uploader = new Uploader(array(
			'moveDirectory' => __DIR__,
			'moveRetry'     => 3,
		));

		$uploader->move($file);
	}

}