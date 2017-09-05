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
class FileValidatorTest extends \PHPUnit\Framework\TestCase
{

	public function testConfigEnableGmp()
	{
		$validator = new FileValidator();

		$validator->config('enableGmp', true);
		$this->assertTrue($validator->config('enableGmp'));
		$validator->config('enableGmp', false);
		$this->assertFalse($validator->config('enableGmp'));
		$validator->config('enableGmp', 1);
		$this->assertTrue($validator->config('enableGmp'));
		$validator->config('enableGmp', 0);
		$this->assertFalse($validator->config('enableGmp'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenEnableGmpIsNotBoolOrIntOrDigit()
	{
		$validator = new FileValidator();

		$validator->config('enableGmp', 'TRUE');
	}

	public function testConfigEnableBcmath()
	{
		$validator = new FileValidator();

		$validator->config('enableBcmath', true);
		$this->assertTrue($validator->config('enableBcmath'));
		$validator->config('enableBcmath', false);
		$this->assertFalse($validator->config('enableBcmath'));
		$validator->config('enableBcmath', 1);
		$this->assertTrue($validator->config('enableBcmath'));
		$validator->config('enableBcmath', 0);
		$this->assertFalse($validator->config('enableBcmath'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenEnableBcmathIsNotBoolOrIntOrDigit()
	{
		$validator = new FileValidator();

		$validator->config('enableBcmath', 'TRUE');
	}

	public function testConfigEnableExif()
	{
		$validator = new FileValidator();

		$validator->config('enableExif', true);
		$this->assertTrue($validator->config('enableExif'));
		$validator->config('enableExif', false);
		$this->assertFalse($validator->config('enableExif'));
		$validator->config('enableExif', 1);
		$this->assertTrue($validator->config('enableExif'));
		$validator->config('enableExif', 0);
		$this->assertFalse($validator->config('enableExif'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenEnableExifIsNotBoolOrIntOrDigit()
	{
		$validator = new FileValidator();

		$validator->config('enableExif', 'TRUE');
	}

	public function testConfigAllowableType()
	{
		$validator = new FileValidator();

		$validator->config('allowableType', 'jpeg');
		$this->assertEquals('jpeg', $validator->config('allowableType'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenAllowableTypeIsNotString()
	{
		$validator = new FileValidator();

		$validator->config('allowableType', true);
	}

	public function testConfigFilenameEncoding()
	{
		$validator = new FileValidator();

		$validator->config('filenameEncoding', 'UTF-8');
		$this->assertEquals('UTF-8', $validator->config('filenameEncoding'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenFilenameEncodingIsNotString()
	{
		$validator = new FileValidator();

		$validator->config('filenameEncoding', true);
	}

	public function testConfigMaxWidthIsInt()
	{
		$validator = new FileValidator();

		$validator->config('maxWidth', 800);
		$this->assertEquals(800, $validator->config('maxWidth'));
	}

	public function testConfigMaxWidthIsDigit()
	{
		$validator = new FileValidator();

		$validator->config('maxWidth', '800');
		$this->assertEquals(800, $validator->config('maxWidth'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenMaxWidthIsNotIntOrDigit()
	{
		$validator = new FileValidator();

		$validator->config('maxWidth', '800px');
	}

	public function testConfigMaxHeightIsInt()
	{
		$validator = new FileValidator();

		$validator->config('maxHeight', 800);
		$this->assertEquals(800, $validator->config('maxHeight'));
	}

	public function testConfigMaxHeightIsDigit()
	{
		$validator = new FileValidator();

		$validator->config('maxHeight', '800');
		$this->assertEquals(800, $validator->config('maxHeight'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenMaxHeightIsNotIntOrDigit()
	{
		$validator = new FileValidator();

		$validator->config('maxHeight', '800px');
	}

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
		$validator->config('maxFilesize', '1M');
		$this->assertEquals('1M', $validator->config('maxFilesize'));
		$validator->config('maxFilesize', '1G');
		$this->assertEquals('1G', $validator->config('maxFilesize'));
		$validator->config('maxFilesize', '1T');
		$this->assertEquals('1T', $validator->config('maxFilesize'));
		$validator->config('maxFilesize', '1P');
		$this->assertEquals('1P', $validator->config('maxFilesize'));
		$validator->config('maxFilesize', '1E');
		$this->assertEquals('1E', $validator->config('maxFilesize'));
		$validator->config('maxFilesize', '1Z');
		$this->assertEquals('1Z', $validator->config('maxFilesize'));
		$validator->config('maxFilesize', '1Y');
		$this->assertEquals('1Y', $validator->config('maxFilesize'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenInvalidMaxFilesize()
	{
		$validator = new FileValidator();

		$validator->config('maxFilesize', '無効な引数');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenThrowExceptionOnValidateIsNotBool()
	{
		$validator = new FileValidator();

		$validator->config('throwExceptionOnValidate', 'A');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenUnsupportedConfig()
	{
		$validator = new FileValidator();

		$validator->config('unsupported-config', 'foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigRaiseExceptionWhenInvalidArgumentCount()
	{
		$validator = new FileValidator();

		$validator->config('allowableType', 'jpeg', 'gif', 'png');
	}

	public function testValidateUploadErrorUploadErrOk()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_OK));

		$this->assertTrue($validator->validateUploadError($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\NotFoundException
	 */
	public function testValidateUploadErrorRaiseExceptionWhenUploadErrNoFile()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_NO_FILE));

		$validator->validateUploadError($file);
	}

	public function testValidateUploadErrorReturnFalseWhenUploadErrNoFile()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_NO_FILE));

		$this->assertFalse($validator->validateUploadError($file));
		$this->assertTrue($validator->hasError('notFound'));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateUploadErrorRaiseExceptionWhenUploadErrIniSize()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_INI_SIZE));

		$validator->validateUploadError($file);
	}

	public function testValidateUploadErrorReturnFalseWhenUploadErrIniSize()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_INI_SIZE));

		$this->assertFalse($validator->validateUploadError($file));
		$this->assertTrue($validator->hasError('filesize'));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateUploadErrorRaiseExceptionWhenUploadErrFormSize()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_FORM_SIZE));

		$validator->validateUploadError($file);
	}

	public function testValidateUploadErrorReturnFalseWhenUploadErrFormSize()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_FORM_SIZE));

		$this->assertFalse($validator->validateUploadError($file));
		$this->assertTrue($validator->hasError('filesize'));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\UploaderException
	 */
	public function testValidateUploadErrorRaiseExceptionWhenAnotherError()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_PARTIAL));

		$validator->validateUploadError($file);
	}

	public function testValidateUploadErrorReturnFalseWhenAnotherError()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_PARTIAL));

		$this->assertFalse($validator->validateUploadError($file));
		$this->assertTrue($validator->hasError('uploader'));
	}

	public function testValidateFilename()
	{
		$validator = new FileValidator(array(
			'filenameEncoding' => 'UTF-8',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientFilename')
			->will($this->returnValue('テスト.jpg'));

		$this->assertTrue($validator->validateFilename($file));
	}

	public function testValidateFilenameReturnNullWhenFilenameEncodingIsNotSet()
	{
		$validator = new FileValidator(array(
			'filenameEncoding' => null,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');

		$this->assertNull($validator->validateFilename($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilenameException
	 */
	public function testValidateFilenameRaiseExceptionWhenInvalidEncoding()
	{
		$validator = new FileValidator(array(
			'filenameEncoding' => 'UTF-8',
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientFilename')
			->will($this->returnValue("\0xfc\xbf\xbf\xbf\xbf\xbf". '.jpg'));

		$validator->validateFilename($file);
	}

	public function testValidateFilenameReturnFalseWhenInvalidEncoding()
	{
		$validator = new FileValidator(array(
			'filenameEncoding' => 'UTF-8',
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientFilename')
			->will($this->returnValue("\0xfc\xbf\xbf\xbf\xbf\xbf". '.jpg'));

		$this->assertFalse($validator->validateFilename($file));
		$this->assertTrue($validator->hasError('filename'));
	}

	public function testValidateFilenameReturnNullWhenFilenameIsNull()
	{
		$validator = new FileValidator(array(
			'filenameEncoding' => 'UTF-8',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientFilename')
			->will($this->returnValue(null));

		$this->assertNull($validator->validateFilename($file));
	}

	public function testValidateFilenameReturnNullWhenFilenameIsEmpty()
	{
		$validator = new FileValidator(array(
			'filenameEncoding' => 'UTF-8',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientFilename')
			->will($this->returnValue(''));

		$this->assertNull($validator->validateFilename($file));
	}

	public function testValidateFilesize()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '2G',
			'enableGmp' => false,
			'enableBcmath' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(2147483647));

		$this->assertTrue($validator->validateFilesize($file));
	}

	public function testValidateFilesizeByGmp()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '2G',
			'enableGmp' => true,
			'enableBcmath' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(2147483647));

		$this->assertTrue($validator->validateFilesize($file));
	}

	public function testValidateFilesizeByBcMath()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '2G',
			'enableGmp' => false,
			'enableBcmath' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(2147483647));

		$this->assertTrue($validator->validateFilesize($file));
	}

	public function testValidateFilesizeOver2gb()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '1Y',
			'enableGmp' => false,
			'enableBcmath' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(-1));

		$this->assertTrue($validator->validateFilesize($file));
	}

	public function testValidateFilesizeReturnNullWhenFilesizeIsNull()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '2G',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(null));

		$this->assertNull($validator->validateFilesize($file));
	}

	public function testValidateFilesizeReturnNullWhenFilesizeIsZero()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '2G',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(0));

		$this->assertNull($validator->validateFilesize($file));
	}

	public function testValidateFilesizeReturnNullWhenMaxFilesizeIsNotSet()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => null,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');

		$this->assertNull($validator->validateFilesize($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateFilesizeRaiseExceptionWhenLargerThanMaxFilesize()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '1024',
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(1025));

		$validator->validateFilesize($file);
	}

	public function testValidateFilesizeReturnFalseWhenLargerThanMaxFilesize()
	{
		$validator = new FileValidator(array(
			'maxFilesize' => '1024',
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getSize')
			->will($this->returnValue(1025));

		$this->assertFalse($validator->validateFilesize($file));
		$this->assertTrue($validator->hasError('filesize'));
	}

	public function testValidateExtension()
	{
		$validator = new FileValidator(array(
			'allowableType' => 'jpeg,png',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('png'));

		$this->assertTrue($validator->validateExtension($file));
	}

	public function testValidateExtensionJpegAndJpg()
	{
		$validator = new FileValidator();

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));

		$validator->config('allowableType', 'jpeg,png');

		$this->assertTrue($validator->validateExtension($file));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpeg'));

		$validator->config('allowableType', 'jpeg,png');

		$this->assertTrue($validator->validateExtension($file));
	}

	public function testValidateExtensionReturnNullWhenAllowableTypeIsNotSet()
	{
		$validator = new FileValidator(array(
			'allowableType' => null,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');

		$this->assertNull($validator->validateExtension($file));
	}

	public function testValidateExtensionReturnNullWhenClientExtensionIsNotSet()
	{
		$validator = new FileValidator(array(
			'allowableType' => 'jpeg,png',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue(null));

		$this->assertNull($validator->validateExtension($file));
	}

	public function testValidateExtensionReturnNullWhenClientExtensionIsEmpty()
	{
		$validator = new FileValidator(array(
			'allowableType' => 'jpeg,png',
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue(''));

		$this->assertNull($validator->validateExtension($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ExtensionException
	 */
	public function testValidateExtensionRaiseExceptionWhenExtensionDoesNotMatch()
	{
		$validator = new FileValidator(array(
			'allowableType' => 'jpeg,png',
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('gif'));

		$validator->validateExtension($file);
	}

	public function testValidateExtensionReturnFalseWhenExtensionDoesNotMatch()
	{
		$validator = new FileValidator(array(
			'allowableType' => 'jpeg,png',
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('gif'));

		$this->assertFalse($validator->validateExtension($file));
		$this->assertTrue($validator->hasError('extension'));
	}

	public function testValidateImageTypeGif()
	{
		$validator = new FileValidator(array(
			'enableExif' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('gif'));
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/gif'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.gif'))); // 180 * 180 gif

		$this->assertTrue($validator->validateImageType($file));
	}

	public function testValidateImageTypeJpeg()
	{
		$validator = new FileValidator(array(
			'enableExif' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/jpeg'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$this->assertTrue($validator->validateImageType($file));
	}

	public function testValidateImageTypeByExif()
	{
		$validator = new FileValidator(array(
			'enableExif' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/jpeg'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$validator->validateImageType($file);
	}

	public function testValidateImageTypeReturnNullWhenFileIsNotImage()
	{
		$validator = new FileValidator(array(
			'enableExif' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(false));

		$this->assertNull($validator->validateImageType($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageTypeException
	 */
	public function testValidateImageTypeRaiseExceptionWhenExtensionDoesNotMatch()
	{
		$validator = new FileValidator(array(
			'enableExif' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('gif'));
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/jpeg'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$validator->validateImageType($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageTypeException
	 */
	public function testValidateImageTypeRaiseExceptionWhenMimeTypeDoesNotMatch()
	{
		$validator = new FileValidator(array(
			'enableExif' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('jpg'));
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/gif'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$validator->validateImageType($file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageTypeException
	 */
	public function testValidateImageTypeRaiseExceptionWhenFileIsNotImage()
	{
		$validator = new FileValidator(array(
			'enableExif' => false,
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('gif'));
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/gif'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is-text.png'))); // text

		$validator->validateImageType($file);
	}

	public function testValidateImageTypeReturnFalseWhenFileIsNotImage()
	{
		$validator = new FileValidator(array(
			'enableExif' => false,
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getClientExtension')
			->will($this->returnValue('gif'));
		$file->expects($this->once())
			->method('getMimeType')
			->will($this->returnValue('image/gif'));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is-text.png'))); // text

		$this->assertFalse($validator->validateImageType($file));
		$this->assertTrue($validator->hasError('imageType'));
	}

	public function testValidateImageSize()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 180,
			'maxHeight' => 180,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$this->assertTrue($validator->validateImageSize($file));
	}

	public function testValidateImageSizeReturnNullWhenMaxWidthAndMaxHeightIsNotSet()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => null,
			'maxHeight' => null,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));

		$this->assertNull($validator->validateImageSize($file));
	}

	public function testValidateImageSizeReturnNullWhenExtensionIsNotImage()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 180,
			'maxHeight' => 180,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(false));

		$this->assertNull($validator->validateImageSize($file));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageWidthException
	 */
	public function testValidateImageSizeRaiseExceptionWhenLargerThanMaxWidth()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 179,
			'maxHeight' => 180,
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$validator->validateImageSize($file);
	}

	public function testValidateImageSizeReturnFalseWhenLargerThanMaxWidth()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 179,
			'maxHeight' => 180,
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$this->assertFalse($validator->validateImageSize($file));
		$this->assertTrue($validator->hasError('imageWidth'));
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageHeightException
	 */
	public function testValidateImageSizeRaiseExceptionWhenLargerThanMaxHeight()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 180,
			'maxHeight' => 179,
			'throwExceptionOnValidate' => true,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$validator->validateImageSize($file);
	}

	public function testValidateImageSizeReturnFalseWhenLargerThanMaxHeight()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 180,
			'maxHeight' => 179,
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$this->assertFalse($validator->validateImageSize($file));
		$this->assertTrue($validator->hasError('imageHeight'));
	}

	public function testValidateImageSizeReturnFalseWhenLargerThanMaxWidthAndMaxHeight()
	{
		$validator = new FileValidator(array(
			'maxWidth'  => 179,
			'maxHeight' => 179,
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('isImage')
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getPath')
			->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

		$this->assertFalse($validator->validateImageSize($file));
		$this->assertTrue($validator->hasError('imageWidth'));
		$this->assertTrue($validator->hasError('imageHeight'));
	}

	public function testClearErrors()
	{
		$validator = new FileValidator(array(
			'throwExceptionOnValidate' => false,
		));

        /** @var $file \Volcanus\FileUploader\File\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
		$file = $this->createMock('\Volcanus\FileUploader\File\FileInterface');
		$file->expects($this->once())
			->method('getError')
			->will($this->returnValue(\UPLOAD_ERR_NO_FILE));

		$validator->validateUploadError($file);
		$this->assertTrue($validator->hasError());

		$validator->clearErrors();
		$this->assertFalse($validator->hasError());
	}

}
