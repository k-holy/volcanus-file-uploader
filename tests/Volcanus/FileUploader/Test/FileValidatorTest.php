<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test;

use Volcanus\FileUploader\FileValidator;
use Volcanus\FileUploader\File\SplFile;

/**
 * Test for Volcanus\FileUploader\FileValidator
 *
 * @author k.holy74@gmail.com
 */
class FileValidatorTest extends \PHPUnit_Framework_TestCase
{

	public function testConfigFilesizeIsInt()
	{
		$validator = new FileValidator();

		$validator->config('maxFilesize', 1024);
		$this->assertEquals(1024, $validator->config('maxFilesize'));
	}

	public function testConfigFilesizeIsDigit()
	{
		$validator = new FileValidator();

		$validator->config('maxFilesize', '1024');
		$this->assertEquals('1024', $validator->config('maxFilesize'));
	}

	public function testConfigFilesizeIsDigitWithUnit()
	{
		$validator = new FileValidator();

		$validator->config('maxFilesize', '1K');
		$this->assertEquals('1K', $validator->config('maxFilesize'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenInvalidMaxFilesize()
	{
		$validator = new FileValidator();

		$validator->config('maxFilesize', '100B');
	}

	public function testConfigAllowableType()
	{
		$validator = new FileValidator();

		$validator->config('allowableType', 'jpeg');
		$this->assertEquals('jpeg', $validator->config('allowableType'));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilenameException
	 */
	public function testValidateFilenameRaiseExceptionWhenInvalidEncoding()
	{
		$validator = new FileValidator(array(
			'filenameEncoding' => 'UTF-8',
		));

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientFilename')
			->will($this->returnValue("\0xfc\xbf\xbf\xbf\xbf\xbf". '.jpg'));

		$validator->validateFilename($file);
	}

	public function testValidateUploadErrorUploadErrOk()
	{
		$validator = new FileValidator();

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_OK));

		$this->assertTrue($validator->validateUploadError($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateUploadErrorRaiseExceptionWhenUploadErrIniSize()
	{
		$validator = new FileValidator();

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_INI_SIZE));

		$validator->validateUploadError($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateUploadErrorRaiseExceptionWhenUploadErrFormSize()
	{
		$validator = new FileValidator();

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_FORM_SIZE));

		$validator->validateUploadError($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\UploaderException
	 */
	public function testValidateUploadErrorRaiseExceptionWhenAnotherError()
	{
		$validator = new FileValidator();

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_PARTIAL));

		$validator->validateUploadError($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateFilesizeRaiseExceptionWhenLargerThanMaxFilesize()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '1K',
		));

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(1025));

		$validator->validateFilesize($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ExtensionException
	 */
	public function testValidateExtensionRaiseExceptionWhenExtensionDoesNotMatch()
	{
		$validator = new FileValidator(array(
			'allowableType' => 'gif',
		));

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));

		$validator->validateExtension($file);
	}

	public function testValidateExtensionJpegAndJpg()
	{
		$validator = new FileValidator();

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));

		$validator->config('allowableType', 'jpeg');

		$this->assertTrue($validator->validateExtension($file));

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpeg'));

		$validator->config('allowableType', 'jpg');

		$this->assertTrue($validator->validateExtension($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageTypeException
	 */
	public function testValidateImageTypeRaiseExceptionWhenExtensionDoesNotMatch()
	{
		$validator = new FileValidator();

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/jpeg'));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('gif'));

		$validator->validateImageType($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageTypeException
	 */
	public function testValidateImageTypeRaiseExceptionWhenMimeTypeDoesNotMatch()
	{
		$validator = new FileValidator();

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/gif'));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));

		$validator->validateImageType($file);
	}

	public function testValidateImageSize()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 180,
			'maxHeight' => 180,
		));

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));

		$this->assertTrue($validator->validateImageSize($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageWidthException
	 */
	public function testValidateImageSizeRaiseExceptionWhenLargerThanMaxWidth()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 179,
			'maxHeight' => 180,
		));

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));

		$validator->validateImageSize($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageHeightException
	 */
	public function testValidateImageRaiseExceptionWhenLargerThanMaxHeight()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 180,
			'maxHeight' => 179,
		));

		$file = $this->getMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));

		$validator->validateImageSize($file);
	}

}
