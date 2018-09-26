<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

use Volcanus\FileUploader\Exception\FilepathException;

/**
 * PSR-7 アップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class Psr7UploadedFile implements FileInterface
{

    /**
     * @var \Psr\Http\Message\UploadedFileInterface
     */
    private $file;

    /**
     * @var \Psr\Http\Message\StreamInterface
     */
    private $stream;

    /**
     * @var string
     */
    private $buffer;

    /**
     * @var string アップロードされたファイルのMIMEタイプ
     */
    private $mimeType;

    /**
     * コンストラクタ
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file
     */
    public function __construct(\Psr\Http\Message\UploadedFileInterface $file)
    {
        $this->file = $file;
        $this->stream = null;
        $this->buffer = null;
        $this->mimeType = null;
    }

    /**
     * アップロードファイルのパスを返します。
     *
     * @return string ファイルパス
     */
    public function getPath()
    {
        return null;
    }

    /**
     * アップロードファイルのサイズを返します。
     *
     * @return int ファイルサイズ
     */
    public function getSize()
    {
        return $this->file->getSize();
    }

    /**
     * アップロードファイルのMIMEタイプを返します。
     *
     * @return string MIMEタイプ
     */
    public function getMimeType()
    {
        if ($this->mimeType === null && $this->isValid()) {
            $getMimeType = new \finfo(\FILEINFO_MIME_TYPE);
            $this->mimeType = $getMimeType->buffer($this->getBuffer());
        }
        return $this->mimeType;
    }

    /**
     * アップロードファイルのクライアントファイル名を返します。
     *
     * @return string クライアントファイル名
     */
    public function getClientFilename()
    {
        return $this->file->getClientFilename();
    }

    /**
     * アップロードファイルのクライアントファイル拡張子を返します。
     *
     * @return string クライアントファイル拡張子
     */
    public function getClientExtension()
    {
        return pathinfo($this->getClientFilename(), \PATHINFO_EXTENSION);
    }

    /**
     * アップロードエラーを返します。
     *
     * @return int アップロードエラー
     */
    public function getError()
    {
        return $this->file->getError();
    }

    /**
     * アップロードファイルが妥当かどうかを返します。
     *
     * @return boolean アップロードファイルが妥当かどうか
     */
    public function isValid()
    {
        return ($this->getError() === \UPLOAD_ERR_OK && $this->getStream()->isReadable());
    }

    /**
     * アップロードファイルが画像かどうかを返します。
     *
     * @return boolean アップロードファイルが画像かどうか
     */
    public function isImage()
    {
        if ($this->isValid()) {
            $imagesize = @getimagesizefromstring($this->getBuffer());
            return (isset($imagesize[2]));
        }
        return false;
    }

    /**
     * アップロードファイルを指定されたディレクトリに移動し、移動先のファイルパスを返します。
     *
     * @param string $directory 移動先ディレクトリ
     * @param string $filename 移動先ファイル名
     * @return string 移動先ファイルパス
     */
    public function move($directory, $filename)
    {
        $destination = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . $filename;
        if (file_exists($destination)) {
            throw new FilepathException(
                sprintf('The file could not move to "%s"', $destination)
            );
        }
        try {
            $this->file->moveTo($destination);
        } catch (\Exception $e) {
            throw new FilepathException(
                sprintf('The file could not move to "%s"', $destination), 0, $e
            );
        }
        return $destination;
    }

    /**
     * アップロードファイルの内容を返します。
     *
     * @return string ファイルの内容
     */
    public function getContent()
    {
        try {
            return $this->getBuffer();
        } catch (\Exception $e) {
            throw new FilepathException('The file could not get content', 0, $e);
        }
    }

    /**
     * アップロードファイルの内容をDataURI形式で返します。
     *
     * @return string DataURI
     */
    public function getContentAsDataUri()
    {
        return sprintf('data:%s;base64,%s',
            $this->getMimeType(),
            base64_encode($this->getContent())
        );
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    private function getStream()
    {
        if ($this->stream === null) {
            $this->stream = $this->file->getStream();
        }
        return $this->stream;
    }

    /**
     * @return string
     */
    private function getBuffer()
    {
        if ($this->buffer === null && $this->isValid()) {
            $this->buffer = (string)$this->getStream();
        }
        return $this->buffer;
    }

}
