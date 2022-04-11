<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test\File;

use PHPUnit\Framework\TestCase;
use Volcanus\FileUploader\Exception\FilepathException;
use Volcanus\FileUploader\File\SplFile;

/**
 * Test for Volcanus\FileUploader\File\SplFile
 *
 * @author k.holy74@gmail.com
 */
class SplFileTest extends TestCase
{

    private $tempDir;

    public function setUp(): void
    {
        $this->tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'temp';
    }

    public function tearDown(): void
    {
        $this->cleanTemp();
    }

    public function testConstructorRaiseExceptionWhenFileNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $file = new SplFile(
            new \SplFileInfo('/file/not/found')
        );
    }

    public function testGetPath()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path)
        );

        $this->assertEquals($path, $file->getPath());
    }

    public function testGetSize()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path)
        );

        $this->assertEquals(filesize($path), $file->getSize());
    }

    public function testGetMimeType()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path)
        );

        $this->assertEquals('image/jpeg', $file->getMimeType());
    }

    public function testGetClientFilename()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg'
        );

        $this->assertEquals('テスト.jpg', $file->getClientFilename());
    }

    public function testGetClientExtension()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg'
        );

        $this->assertEquals('jpg', $file->getClientExtension());
    }

    public function testGetError()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg',
            \UPLOAD_ERR_CANT_WRITE
        );

        $this->assertEquals(\UPLOAD_ERR_CANT_WRITE, $file->getError());
    }

    public function testIsValid()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg',
            \UPLOAD_ERR_OK
        );

        $this->assertTrue($file->isValid());

    }

    public function testIsImage()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg',
            \UPLOAD_ERR_OK
        );

        $this->assertTrue($file->isImage());

    }

    public function testIsNotImage()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is-text.png');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg',
            \UPLOAD_ERR_OK
        );

        $this->assertFalse($file->isImage());

    }

    public function testMove()
    {
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SplFile(
            new \SplFileInfo($temp_path),
            'テスト.jpg',
            \UPLOAD_ERR_OK
        );

        $moved_path = $file->move($this->tempDir, uniqid(mt_rand(), true) . '.jpg');

        $this->assertFileEquals($moved_path, $orig_path);
        $this->assertFileDoesNotExist($temp_path);
    }

    public function testMoveRaiseExceptionWhenAlreadyExists()
    {
        $this->expectException(FilepathException::class);
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'test.jpg', '');

        $file = new SplFile(
            new \SplFileInfo($temp_path),
            'テスト.jpg',
            \UPLOAD_ERR_OK
        );

        $file->move($this->tempDir, 'test.jpg');
    }

    public function testMoveRaiseExceptionWhenUploadedFileIsError()
    {
        $this->expectException(FilepathException::class);
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SplFile(
            new \SplFileInfo($temp_path),
            'テスト.jpg',
            \UPLOAD_ERR_CANT_WRITE
        );

        $file->move($this->tempDir, 'test.jpg');
    }

    public function testGetContent()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg'
        );

        $this->assertEquals(file_get_contents($path), $file->getContent());
    }

    public function testGetContentRaiseExceptionWhenFileIsNotReadable()
    {
        $this->expectException(FilepathException::class);
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SplFile(
            new \SplFileInfo($temp_path),
            'テスト.jpg',
            \UPLOAD_ERR_OK
        );

        unlink($temp_path);

        $file->getContent();
    }

    public function testGetContentAsDataUri()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SplFile(
            new \SplFileInfo($path),
            'テスト.jpg'
        );

        $this->assertStringStartsWith('data:image/jpeg;base64,', $file->getContentAsDataUri());
    }

    private function copyToTemp($path): string
    {
        $temp_path = $this->tempDir . DIRECTORY_SEPARATOR . basename($path);
        copy($path, $temp_path);
        return $temp_path;
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

}
