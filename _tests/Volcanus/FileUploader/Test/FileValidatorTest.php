<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test;

use Volcanus\FileUploader\FileValidator;

/**
 * Test for Volcanus\FileUploader\FileValidator
 *
 * @author k.holy74@gmail.com
 */
class FileValidatorTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilenameException
	 */
	public function testValidateFilename()
	{
		$validator = new FileValidator();
		$validator->validateFilename("\0xfc\xbf\xbf\xbf\xbf\xbf". '.jpg', 'UTF-8');
	}

	public function testValidateUploadErrorUploadErrOk()
	{
		$validator = new FileValidator();
		$this->assertTrue($validator->validateUploadError(\UPLOAD_ERR_OK));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateUploadErrorUploadErrIniSize()
	{
		$validator = new FileValidator();
		$validator->validateUploadError(\UPLOAD_ERR_INI_SIZE);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateUploadErrorUploadErrFormSize()
	{
		$validator = new FileValidator();
		$validator->validateUploadError(\UPLOAD_ERR_FORM_SIZE);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\UploaderException
	 */
	public function testValidateUploadErrorAnotherError()
	{
		$validator = new FileValidator();
		$validator->validateUploadError(\UPLOAD_ERR_PARTIAL);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateFilesize()
	{
		$validator = new FileValidator();
		$validator->validateFilesize(1025, '1K');
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ExtensionException
	 */
	public function testValidateExtension()
	{
		$validator = new FileValidator();
		$validator->validateExtension('jpg', 'gif');
	}

	public function testValidateExtensionJpegAndJpg()
	{
		$validator = new FileValidator();
		$this->assertTrue($validator->validateExtension('jpg', 'jpeg'));
		$this->assertTrue($validator->validateExtension('jpeg', 'jpg'));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageTypeException
	 */
	public function testValidateImageType()
	{
		$validator = new FileValidator();
		$validator->validateImageType(
			realpath(__DIR__ . '/Fixtures/this-is-gif-not.jpg'),
			'jpg'
		);
	}

	public function testValidateImageTypeJpegAndJpg()
	{
		$validator = new FileValidator();
		$this->assertTrue($validator->validateImageType(
			realpath(__DIR__ . '/Fixtures/this-is.jpg'),
			'jpg'
		));
		$this->assertTrue($validator->validateImageType(
			realpath(__DIR__ . '/Fixtures/this-is.jpg'),
			'jpeg'
		));
	}

	public function testValidateImageSize()
	{
		$validator = new FileValidator();
		$this->assertTrue($validator->validateImageSize(
			realpath(__DIR__ . '/Fixtures/this-is.jpg'),
			180,
			180
		));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageWidthException
	 */
	public function testValidateImageWidth()
	{
		$validator = new FileValidator();
		$validator->validateImageSize(
			realpath(__DIR__ . '/Fixtures/this-is.jpg'),
			179,
			180
		);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageHeightException
	 */
	public function testValidateImageHight()
	{
		$validator = new FileValidator();
		$validator->validateImageSize(
			realpath(__DIR__ . '/Fixtures/this-is.jpg'),
			180,
			179
		);
	}

}
