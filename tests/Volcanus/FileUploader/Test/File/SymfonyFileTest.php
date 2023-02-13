<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test\File;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Volcanus\FileUploader\Exception\FilepathException;
use Volcanus\FileUploader\File\SymfonyFile;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Test for Volcanus\FileUploader\File\SymfonyFile
 *
 * @author k.holy74@gmail.com
 */
class SymfonyFileTest extends TestCase
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

    public function testGetPath()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg'
            )
        );

        $this->assertEquals($path, $file->getPath());
    }

    public function testGetSize()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg'
            )
        );

        $this->assertEquals(filesize($path), $file->getSize());
    }

    public function testGetMimeType()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg'
            )
        );

        $this->assertEquals('image/jpeg', $file->getMimeType());
    }

    public function testGetClientFilename()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg'
            )
        );

        $this->assertEquals('テスト.jpg', $file->getClientFilename());
    }

    public function testGetClientExtension()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg'
            )
        );

        $this->assertEquals('jpg', $file->getClientExtension());
    }

    public function testGetError()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_CANT_WRITE
            )
        );

        $this->assertEquals(\UPLOAD_ERR_CANT_WRITE, $file->getError());
    }

    public function testIsValid()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
        );

        $this->assertTrue($file->isValid());

    }

    public function testIsImage()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
        );

        $this->assertTrue($file->isImage());

    }

    public function testIsNotImage()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is-text.png');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
        );

        $this->assertFalse($file->isImage());

    }

    public function testMove()
    {
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SymfonyFile(
            new UploadedFile(
                $temp_path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
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

        $file = new SymfonyFile(
            new UploadedFile(
                $temp_path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
        );

        $file->move($this->tempDir, 'test.jpg');
    }

    public function testMoveRaiseExceptionWhenUploadedFileIsError()
    {
        $this->expectException(FilepathException::class);
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SymfonyFile(
            new UploadedFile(
                $temp_path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_CANT_WRITE,
                true
            )
        );

        $file->move($this->tempDir, 'test.jpg');
    }

    public function testMoveRaiseExceptionWhenUploadedFileThrowFileException()
    {
        $this->expectException(FilepathException::class);
        /** @var $uploadedFile UploadedFile|MockObject */
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $uploadedFile->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $uploadedFile->expects($this->any())
            ->method('move')
            ->will($this->throwException(
                new FileException()
            ));

        $file = new SymfonyFile($uploadedFile);

        /** @noinspection PhpUnusedLocalVariableInspection */
        $moved_path = $file->move($this->tempDir, 'test.jpg');
    }

    public function testGetContent()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
        );

        $this->assertEquals(file_get_contents($path), $file->getContent());
    }

    public function testGetContentRaiseExceptionWhenFileIsNotReadable()
    {
        $this->expectException(FilepathException::class);
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SymfonyFile(
            new UploadedFile(
                $temp_path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
        );

        unlink($temp_path);

        $file->getContent();
    }

    public function testGetContentAsDataUri()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                'テスト.jpg',
                null,
                \UPLOAD_ERR_OK,
                true
            )
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
