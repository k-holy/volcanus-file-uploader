<?php
/**
 * Volcanus libraries for PHP 8.1~
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
use Volcanus\FileUploader\Exception\UploaderException;
use Volcanus\FileUploader\File\NativeFile;
use Volcanus\FileUploader\FileValidator;
use Volcanus\FileUploader\Uploader;

/**
 * Test for Volcanus\FileUploader\Uploader
 *
 * @author k.holy74@gmail.com
 */
class UploaderTest extends TestCase
{

    private string $tempDir;

    public function setUp(): void
    {
        $this->tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'temp';
    }

    public function tearDown(): void
    {
        $this->cleanTemp();
    }

    private function cleanTemp()
    {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->tempDir)
        );
        foreach ($it as $file) {
            if ($file->isFile() && $file->getBaseName() !== '.gitignore') {
                unlink($file);
            }
        }
    }

    /**
     *
     * @param string $filePath
     * @param array $files
     * @return NativeFile
     */
    private function copyFile(string $filePath, array $files = []): NativeFile
    {
        $basename = basename($filePath);
        $copiedFile = $this->tempDir . DIRECTORY_SEPARATOR . $basename;
        copy($filePath, $copiedFile);
        if (!array_key_exists('tmp_name', $files)) {
            $files['tmp_name'] = $copiedFile;
        }
        if (!array_key_exists('name', $files)) {
            $files['name'] = $basename;
        }
        return new NativeFile($files);
    }

    /**
     * @param array|\ArrayAccess $configurations
     * @return FileValidator
     */
    private function createValidator(array|\ArrayAccess $configurations): FileValidator
    {
        return new FileValidator($configurations);
    }

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
        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'
        );

        /** @var $validator FileValidator|MockObject */
        $validator = $this->createMock(FileValidator::class);
        $validator->expects($this->once())
            ->method('clearErrors');

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateUploadError()
    {
        $file = $this->copyFile(
            realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg')
        );

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
        $this->expectException(FilenameException::class);

        $file = $this->copyFile(
            realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'),
            ['name' => "\0xfc\xbf\xbf\xbf\xbf\xbf" . '.jpg']
        );

        $validator = $this->createValidator([
            'filenameEncoding' => 'UTF-8',
            'throwExceptionOnValidate' => true,
        ]);

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateFilesizeWhenConfigHasMaxFilesize()
    {
        $this->expectException(FilesizeException::class);

        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'
        );

        $validator = $this->createValidator([
            'maxFilesize' => $file->getSize() - 1,
            'throwExceptionOnValidate' => true,
        ]);

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateExtensionWhenConfigHasAllowableType()
    {

        $this->expectException(ExtensionException::class);

        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'
        );

        $validator = $this->createValidator([
            'allowableType' => 'png',
            'throwExceptionOnValidate' => true,
        ]);

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateImageTypeWhenFileIsImage()
    {
        $this->expectException(ImageTypeException::class);

        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is-gif-not.jpg'
        );

        $validator = $this->createValidator([
            'throwExceptionOnValidate' => true,
        ]);

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateImageSizeWhenFileIsImageAndConfigHasMaxWidth()
    {
        $this->expectException(ImageWidthException::class);

        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'
        );

        list($width, $height) = getimagesize($file->getPath());

        $validator = $this->createValidator([
            'maxWidth' => $width - 1,
            'maxHeight' => $height,
            'throwExceptionOnValidate' => true,
        ]);

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testValidateCallValidateImageSizeWhenFileIsImageAndConfigHasMaxHeight()
    {
        $this->expectException(ImageHeightException::class);

        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'
        );

        list($width, $height) = getimagesize($file->getPath());

        $validator = $this->createValidator([
            'maxWidth' => $width,
            'maxHeight' => $height - 1,
            'throwExceptionOnValidate' => true,
        ]);

        $uploader = new Uploader();

        $this->assertTrue($uploader->validate($file, $validator));
    }

    public function testMove()
    {
        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'
        );

        $uploader = new Uploader([
            'moveDirectory' => $this->tempDir,
            'moveRetry' => 1,
        ]);

        $movedPath = $uploader->move($file);

        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFileExists($movedPath);
    }

    public function testMoveRaiseExceptionWhenAllRetryFailed()
    {
        $this->expectException(UploaderException::class);

        $file = $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'this-is.jpg'
        );

        unlink($file->getPath());

        $uploader = new Uploader([
            'moveDirectory' => $this->tempDir,
            'moveRetry' => 1,
        ]);

        $uploader->move($file);
    }

}
