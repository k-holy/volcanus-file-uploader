<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Volcanus\FileUploader\Exception\FilepathException;

/**
 * PSR-7 アップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class Psr7UploadedFile implements FileInterface
{

    /**
     * @var UploadedFileInterface
     */
    private UploadedFileInterface $file;

    /**
     * @var StreamInterface|null
     */
    private ?StreamInterface $stream;

    /**
     * @var string|null
     */
    private ?string $buffer;

    /**
     * @var string|null アップロードされたファイルのMIMEタイプ
     */
    private ?string $mimeType;

    /**
     * コンストラクタ
     *
     * @param UploadedFileInterface $file
     */
    public function __construct(UploadedFileInterface $file)
    {
        $this->file = $file;
        $this->stream = null;
        $this->buffer = null;
        $this->mimeType = null;
    }

    /**
     * アップロードファイルのパスを返します。
     *
     * @return string|null ファイルパス
     */
    public function getPath(): ?string
    {
        return null;
    }

    /**
     * アップロードファイルのサイズを返します。
     *
     * @return int|null ファイルサイズ
     */
    public function getSize(): ?int
    {
        return $this->file->getSize();
    }

    /**
     * アップロードファイルのMIMEタイプを返します。
     *
     * @return string|null MIMEタイプ
     */
    public function getMimeType(): ?string
    {
        if ($this->mimeType === null && $this->isValid()) {
            $getMimeType = new \finfo(\FILEINFO_MIME_TYPE);
            $mimeType = $getMimeType->buffer($this->getBuffer());
            if ($mimeType !== false) {
                $this->mimeType = $mimeType;
            }
        }
        return $this->mimeType;
    }

    /**
     * アップロードファイルのクライアントファイル名を返します。
     *
     * @return string|null クライアントファイル名
     */
    public function getClientFilename(): ?string
    {
        return $this->file->getClientFilename();
    }

    /**
     * アップロードファイルのクライアントファイル拡張子を返します。
     *
     * @return string|null クライアントファイル拡張子
     */
    public function getClientExtension(): ?string
    {
        return pathinfo($this->getClientFilename(), \PATHINFO_EXTENSION);
    }

    /**
     * アップロードエラーを返します。
     *
     * @return int|null アップロードエラー
     */
    public function getError(): ?int
    {
        return $this->file->getError();
    }

    /**
     * アップロードファイルが妥当かどうかを返します。
     *
     * @return bool アップロードファイルが妥当かどうか
     */
    public function isValid(): bool
    {
        return ($this->getError() === \UPLOAD_ERR_OK && $this->getStream()->isReadable());
    }

    /**
     * アップロードファイルが画像かどうかを返します。
     *
     * @return bool アップロードファイルが画像かどうか
     */
    public function isImage(): bool
    {
        $imageInfo = $this->getImageInfo();
        return (is_array($imageInfo) && isset($imageInfo[2]));
    }

    /**
     * アップロードファイルの画像情報を返します。
     *
     * @return array|false アップロードファイルの画像情報またはfalse
     */
    public function getImageInfo(): mixed
    {
        if ($this->isValid()) {
            return getimagesizefromstring($this->getBuffer());
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
    public function move(string $directory, string $filename): string
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
    public function getContent(): string
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
    public function getContentAsDataUri(): string
    {
        return sprintf('data:%s;base64,%s',
            $this->getMimeType(),
            base64_encode($this->getContent())
        );
    }

    /**
     * @return StreamInterface
     */
    private function getStream(): StreamInterface
    {
        if ($this->stream === null) {
            $this->stream = $this->file->getStream();
        }
        return $this->stream;
    }

    /**
     * @return string|null
     */
    private function getBuffer(): ?string
    {
        if ($this->buffer === null && $this->isValid()) {
            $this->buffer = (string)$this->getStream();
        }
        return $this->buffer;
    }

}
