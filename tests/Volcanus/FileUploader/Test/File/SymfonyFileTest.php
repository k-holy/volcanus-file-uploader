<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Test\File;

use Volcanus\FileUploader\File\SymfonyFile;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Test for Volcanus\FileUploader\File\SymfonyFile
 *
 * @author k.holy74@gmail.com
 */
class SymfonyFileTest extends \PHPUnit\Framework\TestCase
{

    private $tempDir;

    public function setUp()
    {
        $this->tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'temp';
    }

    public function tearDown()
    {
        $this->cleanTemp();
    }

    public function testGetPath()
    {
        $path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');

        $file = new SymfonyFile(
            new UploadedFile(
                $path,
                $clientFilename = 'テスト.jpg'
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
                $clientFilename = 'テスト.jpg'
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
                $clientFilename = 'テスト.jpg'
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
                $clientFilename = 'テスト.jpg'
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
                $clientFilename = 'テスト.jpg'
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
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_CANT_WRITE
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
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
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
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
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
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
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
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
            )
        );

        $moved_path = $file->move($this->tempDir, uniqid(mt_rand(), true) . '.jpg');

        $this->assertFileEquals($moved_path, $orig_path);
        $this->assertFileNotExists($temp_path);
    }

    /**
     * @expectedException \Volcanus\FileUploader\Exception\FilepathException
     */
    public function testMoveRaiseExceptionWhenAlreadyExists()
    {
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'test.jpg', '');

        $file = new SymfonyFile(
            new UploadedFile(
                $temp_path,
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
            )
        );

        $file->move($this->tempDir, 'test.jpg');
    }

    /**
     * @expectedException \Volcanus\FileUploader\Exception\FilepathException
     */
    public function testMoveRaiseExceptionWhenUploadedFileIsError()
    {
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SymfonyFile(
            new UploadedFile(
                $temp_path,
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_CANT_WRITE,
                $test = true
            )
        );

        $file->move($this->tempDir, 'test.jpg');
    }

    /**
     * @expectedException \Volcanus\FileUploader\Exception\FilepathException
     */
    public function testMoveRaiseExceptionWhenUploadedFileThrowFileException()
    {
        /** @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile|\PHPUnit_Framework_MockObject_MockObject */
        $uploadedFile = $this->getMockBuilder('\Symfony\Component\HttpFoundation\File\UploadedFile')
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $uploadedFile->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $uploadedFile->expects($this->any())
            ->method('move')
            ->will($this->throwException(
                new \Symfony\Component\HttpFoundation\File\Exception\FileException()
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
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
            )
        );

        $this->assertEquals(file_get_contents($path), $file->getContent());
    }

    /**
     * @expectedException \Volcanus\FileUploader\Exception\FilepathException
     */
    public function testGetContentRaiseExceptionWhenFileIsNotReadable()
    {
        $orig_path = realpath(__DIR__ . '/../Fixtures/this-is.jpg');
        $temp_path = $this->copyToTemp($orig_path);

        $file = new SymfonyFile(
            new UploadedFile(
                $temp_path,
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
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
                $clientFilename = 'テスト.jpg',
                $mimeType = null,
                $size = null,
                $error = \UPLOAD_ERR_OK,
                $test = true
            )
        );

        $this->assertStringStartsWith('data:image/jpeg;base64,', $file->getContentAsDataUri());
    }

    private function copyToTemp($path)
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
