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
use Volcanus\FileUploader\Exception\UploaderException;
use Volcanus\FileUploader\File\FileInterface;
use Volcanus\FileUploader\FileValidator;
use Volcanus\FileUploader\Uploader;
use Volcanus\FileUploader\Exception\FilepathException;

/**
 * Test for Volcanus\FileUploader\Uploader
 *
 * @author k.holy74@gmail.com
 */
class UploaderTest extends TestCase
{

    public function testConfigRaiseExceptionWhenMoveRetryIsNotDigit()
    {
        $this->expectException(\InvalidArgumentException::class);
        $uploader = new Uploader();
        $uploader->config('moveRetry', 'foo');
    }

    public function testConfigRaiseExceptionWhenMoveDirectoryIsNotString()
    {
        $this->expectException(\InvalidArgumentException::class);
        $uploader = new Uploader();
        $uploader->config('moveDirectory', true);
    }

    public function testConfigRaiseExceptionWhenUnsupportedConfig()
    {
        $this->expectException(\InvalidArgumentException::class);
        $uploader = new Uploader();
        $uploader->config('unsupported-config', 'foo');
    }

    public function testConfigRaiseExceptionWhenInvalidArgumentCount()
    {
        $this->expectException(\InvalidArgumentException::class);
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
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

        $validator->expects($this->once())
            ->method('clearErrors');

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateUploadError()
    {
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

        $validator->expects($this->once())
            ->method('validateUploadError')
            ->will($this->returnValue(true));

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateFilenameWhenConfigHasFilenameEncoding()
    {
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

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
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

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
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

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
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isImage')
            ->will($this->returnValue(true));

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

        $validator->expects($this->once())
            ->method('validateImageType')
            ->will($this->returnValue(true));

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateImageSizeWhenFileIsImageAndConfigHasMaxWidth()
    {
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isImage')
            ->will($this->returnValue(true));

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

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
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isImage')
            ->will($this->returnValue(true));

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);

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
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue('jpg'));
        $file->expects($this->once())
            ->method('move')
            ->will($this->returnCallback(function ($directory, $filename) {
                return $directory . '/' . $filename;
            }));

        $uploader = new Uploader([
            'moveDirectory' => __DIR__,
            'moveRetry' => 1,
        ]);

        $moved_path = $uploader->move($file);

        $this->assertMatchesRegularExpression('~/[a-f0-9]{40}\.jpg\z~i', $moved_path);
    }

    public function testMoveRaiseExceptionWhenFileIsNotValid()
    {
        $this->expectException(UploaderException::class);
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $uploader = new Uploader([
            'moveDirectory' => __DIR__,
            'moveRetry' => 1,
        ]);

        $uploader->move($file);
    }

    public function testMoveRaiseExceptionWhenAllRetryFailed()
    {
        $this->expectException(UploaderException::class);
        /** @var $file FileInterface|MockObject */
        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $file->expects($this->once())
            ->method('getClientExtension')
            ->will($this->returnValue('jpg'));
        $file->expects($this->any())
            ->method('move')
            ->will($this->throwException(new FilepathException()));

        $uploader = new Uploader([
            'moveDirectory' => __DIR__,
            'moveRetry' => 3,
        ]);

        $uploader->move($file);
    }

}
