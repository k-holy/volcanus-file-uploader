<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volcanus\FileUploader\Exception\ExtensionException;
use Volcanus\FileUploader\Exception\FilenameException;
use Volcanus\FileUploader\Exception\FilesizeException;
use Volcanus\FileUploader\Exception\ImageHeightException;
use Volcanus\FileUploader\Exception\ImageTypeException;
use Volcanus\FileUploader\Exception\ImageWidthException;
use Volcanus\FileUploader\Exception\NotFoundException;
use Volcanus\FileUploader\Exception\UploaderException;
use Volcanus\FileUploader\File\FileInterface;
use Volcanus\FileUploader\FileValidator;

/**
 * Test for Volcanus\FileUploader\FileValidator
 *
 * @author k.holy74@gmail.com
 */
class FileValidatorTest extends TestCase
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

    public function testConfigRaiseExceptionWhenEnableGmpIsNotBoolOrIntOrDigit()
    {
        $this->expectException(\InvalidArgumentException::class);
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

    public function testConfigRaiseExceptionWhenEnableBcmathIsNotBoolOrIntOrDigit()
    {
        $this->expectException(\InvalidArgumentException::class);
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

    public function testConfigRaiseExceptionWhenEnableExifIsNotBoolOrIntOrDigit()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validator = new FileValidator();

        $validator->config('enableExif', 'TRUE');
    }

    public function testConfigAllowableType()
    {
        $validator = new FileValidator();

        $validator->config('allowableType', 'jpeg');
        $this->assertEquals('jpeg', $validator->config('allowableType'));
    }

    public function testConfigRaiseExceptionWhenAllowableTypeIsNotString()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validator = new FileValidator();

        $validator->config('allowableType', true);
    }

    public function testConfigFilenameEncoding()
    {
        $validator = new FileValidator();

        $validator->config('filenameEncoding', 'UTF-8');
        $this->assertEquals('UTF-8', $validator->config('filenameEncoding'));
    }

    public function testConfigRaiseExceptionWhenFilenameEncodingIsNotString()
    {
        $this->expectException(\InvalidArgumentException::class);
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

    public function testConfigRaiseExceptionWhenMaxWidthIsNotIntOrDigit()
    {
        $this->expectException(\InvalidArgumentException::class);
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

    public function testConfigRaiseExceptionWhenMaxHeightIsNotIntOrDigit()
    {
        $this->expectException(\InvalidArgumentException::class);
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

    public function testConfigRaiseExceptionWhenInvalidMaxFilesize()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validator = new FileValidator();

        $validator->config('maxFilesize', '無効な引数');
    }

    public function testConfigRaiseExceptionWhenThrowExceptionOnValidateIsNotBool()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validator = new FileValidator();

        $validator->config('throwExceptionOnValidate', 'A');
    }

    public function testConfigRaiseExceptionWhenUnsupportedConfig()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validator = new FileValidator();

        $validator->config('unsupported-config', 'foo');
    }

    public function testConfigRaiseExceptionWhenInvalidArgumentCount()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validator = new FileValidator();

        $validator->config('allowableType', 'jpeg', 'gif', 'png');
    }

    public function testValidateUploadErrorUploadErrOk()
    {
        $validator = new FileValidator([
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_OK));

        $this->assertTrue($validator->validateUploadError($file));
    }

    public function testValidateUploadErrorRaiseExceptionWhenUploadErrNoFile()
    {
        $this->expectException(NotFoundException::class);
        $validator = new FileValidator([
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_NO_FILE));

        $validator->validateUploadError($file);
    }

    public function testValidateUploadErrorReturnFalseWhenUploadErrNoFile()
    {
        $validator = new FileValidator([
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_NO_FILE));

        $this->assertFalse($validator->validateUploadError($file));
        $this->assertTrue($validator->hasError('notFound'));
    }

    public function testValidateUploadErrorRaiseExceptionWhenUploadErrIniSize()
    {
        $this->expectException(FilesizeException::class);
        $validator = new FileValidator([
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_INI_SIZE));

        $validator->validateUploadError($file);
    }

    public function testValidateUploadErrorReturnFalseWhenUploadErrIniSize()
    {
        $validator = new FileValidator([
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_INI_SIZE));

        $this->assertFalse($validator->validateUploadError($file));
        $this->assertTrue($validator->hasError('filesize'));
    }

    public function testValidateUploadErrorRaiseExceptionWhenUploadErrFormSize()
    {
        $this->expectException(FilesizeException::class);
        $validator = new FileValidator([
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_FORM_SIZE));

        $validator->validateUploadError($file);
    }

    public function testValidateUploadErrorReturnFalseWhenUploadErrFormSize()
    {
        $validator = new FileValidator([
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_FORM_SIZE));

        $this->assertFalse($validator->validateUploadError($file));
        $this->assertTrue($validator->hasError('filesize'));
    }

    public function testValidateUploadErrorRaiseExceptionWhenAnotherError()
    {
        $this->expectException(UploaderException::class);
        $validator = new FileValidator([
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_PARTIAL));

        $validator->validateUploadError($file);
    }

    public function testValidateUploadErrorReturnFalseWhenAnotherError()
    {
        $validator = new FileValidator([
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_PARTIAL));

        $this->assertFalse($validator->validateUploadError($file));
        $this->assertTrue($validator->hasError('uploader'));
    }

    public function testValidateFilename()
    {
        $validator = new FileValidator([
            'filenameEncoding' => 'UTF-8',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientFilename')
            ->will($this->returnValue('テスト.jpg'));

        $this->assertTrue($validator->validateFilename($file));
    }

    public function testValidateFilenameReturnNullWhenFilenameEncodingIsNotSet()
    {
        $validator = new FileValidator([
            'filenameEncoding' => null,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        $this->assertNull($validator->validateFilename($file));
    }

    public function testValidateFilenameRaiseExceptionWhenInvalidEncoding()
    {
        $this->expectException(FilenameException::class);
        $validator = new FileValidator([
            'filenameEncoding' => 'UTF-8',
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientFilename')
            ->will($this->returnValue("\0xfc\xbf\xbf\xbf\xbf\xbf" . '.jpg'));

        $validator->validateFilename($file);
    }

    public function testValidateFilenameReturnFalseWhenInvalidEncoding()
    {
        $validator = new FileValidator([
            'filenameEncoding' => 'UTF-8',
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientFilename')
            ->will($this->returnValue("\0xfc\xbf\xbf\xbf\xbf\xbf" . '.jpg'));

        $this->assertFalse($validator->validateFilename($file));
        $this->assertTrue($validator->hasError('filename'));
    }

    public function testValidateFilenameReturnNullWhenFilenameIsNull()
    {
        $validator = new FileValidator([
            'filenameEncoding' => 'UTF-8',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientFilename')
            ->will($this->returnValue(null));

        $this->assertNull($validator->validateFilename($file));
    }

    public function testValidateFilenameReturnNullWhenFilenameIsEmpty()
    {
        $validator = new FileValidator([
            'filenameEncoding' => 'UTF-8',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientFilename')
            ->will($this->returnValue(''));

        $this->assertNull($validator->validateFilename($file));
    }

    public function testValidateFilesize()
    {
        $validator = new FileValidator([
            'maxFilesize' => '2G',
            'enableGmp' => false,
            'enableBcmath' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(2147483647));

        $this->assertTrue($validator->validateFilesize($file));
    }

    public function testValidateFilesizeByGmp()
    {
        $validator = new FileValidator([
            'maxFilesize' => '2G',
            'enableGmp' => true,
            'enableBcmath' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(2147483647));

        $this->assertTrue($validator->validateFilesize($file));
    }

    public function testValidateFilesizeByBcMath()
    {
        $validator = new FileValidator([
            'maxFilesize' => '2G',
            'enableGmp' => false,
            'enableBcmath' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(2147483647));

        $this->assertTrue($validator->validateFilesize($file));
    }

    public function testValidateFilesizeOver2gb()
    {
        $validator = new FileValidator([
            'maxFilesize' => '1Y',
            'enableGmp' => false,
            'enableBcmath' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(-1));

        $this->assertTrue($validator->validateFilesize($file));
    }

    public function testValidateFilesizeReturnNullWhenFilesizeIsNull()
    {
        $validator = new FileValidator([
            'maxFilesize' => '2G',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(null));

        $this->assertNull($validator->validateFilesize($file));
    }

    public function testValidateFilesizeReturnNullWhenFilesizeIsZero()
    {
        $validator = new FileValidator([
            'maxFilesize' => '2G',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));

        $this->assertNull($validator->validateFilesize($file));
    }

    public function testValidateFilesizeReturnNullWhenMaxFilesizeIsNotSet()
    {
        $validator = new FileValidator([
            'maxFilesize' => null,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        $this->assertNull($validator->validateFilesize($file));
    }

    public function testValidateFilesizeRaiseExceptionWhenLargerThanMaxFilesize()
    {
        $this->expectException(FilesizeException::class);
        $validator = new FileValidator([
            'maxFilesize' => '1024',
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1025));

        $validator->validateFilesize($file);
    }

    public function testValidateFilesizeReturnFalseWhenLargerThanMaxFilesize()
    {
        $validator = new FileValidator([
            'maxFilesize' => '1024',
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1025));

        $this->assertFalse($validator->validateFilesize($file));
        $this->assertTrue($validator->hasError('filesize'));
    }

    public function testValidateExtension()
    {
        $validator = new FileValidator([
            'allowableType' => 'jpeg,png',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue('png'));

        $this->assertTrue($validator->validateExtension($file));
    }

    public function testValidateExtensionJpegAndJpg()
    {
        $validator = new FileValidator();

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue('jpg'));

        $validator->config('allowableType', 'jpeg,png');

        $this->assertTrue($validator->validateExtension($file));

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue('jpeg'));

        $validator->config('allowableType', 'jpeg,png');

        $this->assertTrue($validator->validateExtension($file));
    }

    public function testValidateExtensionReturnNullWhenAllowableTypeIsNotSet()
    {
        $validator = new FileValidator([
            'allowableType' => null,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        $this->assertNull($validator->validateExtension($file));
    }

    public function testValidateExtensionReturnNullWhenClientExtensionIsNotSet()
    {
        $validator = new FileValidator([
            'allowableType' => 'jpeg,png',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue(null));

        $this->assertNull($validator->validateExtension($file));
    }

    public function testValidateExtensionReturnNullWhenClientExtensionIsEmpty()
    {
        $validator = new FileValidator([
            'allowableType' => 'jpeg,png',
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue(''));

        $this->assertNull($validator->validateExtension($file));
    }

    public function testValidateExtensionRaiseExceptionWhenExtensionDoesNotMatch()
    {
        $this->expectException(ExtensionException::class);
        $validator = new FileValidator([
            'allowableType' => 'jpeg,png',
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue('gif'));

        $validator->validateExtension($file);
    }

    public function testValidateExtensionReturnFalseWhenExtensionDoesNotMatch()
    {
        $validator = new FileValidator([
            'allowableType' => 'jpeg,png',
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue('gif'));

        $this->assertFalse($validator->validateExtension($file));
        $this->assertTrue($validator->hasError('extension'));
    }

    public function testValidateImageTypeGif()
    {
        $validator = new FileValidator([
            'enableExif' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'enableExif' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'enableExif' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'enableExif' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isImage')
            ->will($this->returnValue(false));

        $this->assertNull($validator->validateImageType($file));
    }

    public function testValidateImageTypeRaiseExceptionWhenExtensionDoesNotMatch()
    {
        $this->expectException(ImageTypeException::class);
        $validator = new FileValidator([
            'enableExif' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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

    public function testValidateImageTypeRaiseExceptionWhenMimeTypeDoesNotMatch()
    {
        $this->expectException(ImageTypeException::class);
        $validator = new FileValidator([
            'enableExif' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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

    public function testValidateImageTypeRaiseExceptionWhenFileIsNotImage()
    {
        $this->expectException(ImageTypeException::class);
        $validator = new FileValidator([
            'enableExif' => false,
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'enableExif' => false,
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'maxWidth' => 180,
            'maxHeight' => 180,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'maxWidth' => null,
            'maxHeight' => null,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isImage')
            ->will($this->returnValue(true));

        $this->assertNull($validator->validateImageSize($file));
    }

    public function testValidateImageSizeReturnNullWhenExtensionIsNotImage()
    {
        $validator = new FileValidator([
            'maxWidth' => 180,
            'maxHeight' => 180,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isImage')
            ->will($this->returnValue(false));

        $this->assertNull($validator->validateImageSize($file));
    }

    public function testValidateImageSizeRaiseExceptionWhenLargerThanMaxWidth()
    {
        $this->expectException(ImageWidthException::class);
        $validator = new FileValidator([
            'maxWidth' => 179,
            'maxHeight' => 180,
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'maxWidth' => 179,
            'maxHeight' => 180,
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isImage')
            ->will($this->returnValue(true));
        $file->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue(realpath(__DIR__ . '/Fixtures/this-is.jpg'))); // 180 * 180 jpeg

        $this->assertFalse($validator->validateImageSize($file));
        $this->assertTrue($validator->hasError('imageWidth'));
    }

    public function testValidateImageSizeRaiseExceptionWhenLargerThanMaxHeight()
    {
        $this->expectException(ImageHeightException::class);
        $validator = new FileValidator([
            'maxWidth' => 180,
            'maxHeight' => 179,
            'throwExceptionOnValidate' => true,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'maxWidth' => 180,
            'maxHeight' => 179,
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'maxWidth' => 179,
            'maxHeight' => 179,
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
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
        $validator = new FileValidator([
            'throwExceptionOnValidate' => false,
        ]);

        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('getError')
            ->will($this->returnValue(\UPLOAD_ERR_NO_FILE));

        $validator->validateUploadError($file);
        $this->assertTrue($validator->hasError());

        $validator->clearErrors();
        $this->assertFalse($validator->hasError());
    }

}
