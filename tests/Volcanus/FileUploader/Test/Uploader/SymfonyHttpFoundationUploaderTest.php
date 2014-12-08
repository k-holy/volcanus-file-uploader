<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test\Uploader;

use Volcanus\FileUploader\Uploader\SymfonyHttpFoundationUploader;
use Volcanus\FileUploader\FileValidator;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Symfony Http-Foundationコンポーネント用アップローダのテスト
 *
 * @author k.holy74@gmail.com
 */
class SymfonyHttpFoundationUploaderTest extends \PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		if (!ini_get('file_uploads')) {
			$this->markTestSkipped('file_uploads is disabled in php.ini');
		}
	}

	public function testGetClientFilename()
	{
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			realpath(__DIR__ . '/../Fixtures/this-is.jpg'),
			'オリジナル.jpg'
		));
		$this->assertEquals('オリジナル.jpg', $uploader->getClientFilename());
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateRaiseExceptionWhenLargerThanMaxFilesize()
	{
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			realpath(__DIR__ . '/../Fixtures/this-is.jpg'),
			'オリジナル.jpg'
		));
		$uploader->validate(
			new FileValidator(array(
				'maxFilesize' => '14K',
			))
		);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateRaiseExceptionWhenUploadErrIniSize()
	{
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			realpath(__DIR__ . '/../Fixtures/this-is.jpg'),
			'オリジナル.jpg',
			null,
			null,
			\UPLOAD_ERR_INI_SIZE
		));
		$uploader->validate(
			new FileValidator()
		);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilesizeException
	 */
	public function testValidateRaiseExceptionWhenUploadErrFormSize()
	{
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			realpath(__DIR__ . '/../Fixtures/this-is.jpg'),
			'オリジナル.jpg',
			null,
			null,
			\UPLOAD_ERR_FORM_SIZE
		));
		$uploader->validate(
			new FileValidator()
		);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ExtensionException
	 */
	public function testValidateRaiseExceptionWhenInvalidExtension()
	{
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			realpath(__DIR__ . '/../Fixtures/this-is.jpg'),
			'オリジナル.jpg'
		));
		$uploader->validate(
			new FileValidator(array(
				'allowableType' => 'gif',
			))
		);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\ImageTypeException
	 */
	public function testValidateRaiseExceptionWhenInvalidImageType()
	{
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			realpath(__DIR__ . '/../Fixtures/this-is-gif-not.jpg'),
			'オリジナル.jpg'
		));
		$uploader->validate(
			new FileValidator()
		);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\FilenameException
	 */
	public function testValidateRaiseExceptionWhenInvalidFilename()
	{
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			realpath(__DIR__ . '/../Fixtures/this-is.jpg'),
			"\0xfc\xbf\xbf\xbf\xbf\xbf". '.jpg'
		));
		$uploader->validate(
			new FileValidator(array(
				'filenameEncoding' => 'UTF-8',
			))
		);
	}

	public function testMove()
	{
		$uploaded_file = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
		$temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'this-is.jpg';
		copy($uploaded_file, $temp_file);
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			$temp_file,
			'オリジナル.jpg'
		));
		$moved_file = $uploader->move(array(
			'moveDirectory' => __DIR__,
			'moveRetry'     => 1,
		));
		$this->assertFileExists($moved_file);
		unlink($moved_file);
	}

	/**
	 * @expectedException \Volcanus\FileUploader\Exception\UploaderException
	 */
	public function testMoveRaiseExceptionWhenCouldNotMoveFile()
	{
		$uploaded_file = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
		$temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'this-is.jpg';
		copy($uploaded_file, $temp_file);
		$uploader = new SymfonyHttpFoundationUploader($this->createUploadedFile(
			$temp_file,
			'オリジナル.jpg'
		));
		$moved_file = $uploader->move(array(
			'moveDirectory' => null,
			'moveRetry'     => 1,
		));
	}

	private function createUploadedFile($path, $clientName, $mimeType = null, $filesize = null, $uploadError = null, $testMode = null)
	{
		return new UploadedFile($path, $clientName, $mimeType, $filesize ?: filesize($path), $uploadError, $testMode ?: true);
	}

}
