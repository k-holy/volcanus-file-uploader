<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

use Volcanus\FileUploader\Exception\FilepathException;

/**
 * SPL(SplFileInfo)アップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class SplFile implements FileInterface
{

    /**
     * @var \SplFileInfo
     */
    private \SplFileInfo $file;

    /**
     * @var string|null アップロード元のファイル名
     */
    private ?string $clientFilename;

    /**
     * @var int|null アップロードされたファイルのサイズ
     */
    private ?int $size;

    /**
     * @var string|null アップロードされたファイルのMIMEタイプ
     */
    private ?string $mimeType;

    /**
     * @var int|null アップロードエラーコード
     * @see http://jp.php.net/manual/ja/features.file-upload.errors.php
     */
    private ?int $error;

    /**
     * コンストラクタ
     *
     * @param \SplFileInfo $file アップロードされたファイルのパス
     * @param string|null $clientFilename アップロードされたファイルの元のファイル名
     * @param int|null $error アップロードエラーコード
     */
    public function __construct(\SplFileInfo $file, string $clientFilename = null, int $error = null)
    {
        $this->error = ($error === null) ? \UPLOAD_ERR_OK : $error;
        if ($this->error === \UPLOAD_ERR_OK && !is_file($file->getPathname())) {
            throw new \InvalidArgumentException(
                sprintf('The file "%s" is not a file.', $file->getPathname())
            );
        }
        $this->clientFilename = ($clientFilename === null) ? $file->getBasename() : $clientFilename;
        $this->size = null;
        $this->mimeType = null;
        $this->file = $file;
    }

    /**
     * アップロードファイルのパスを返します。
     *
     * @return string|null ファイルパス
     */
    public function getPath(): ?string
    {
        return $this->file->getPathname();
    }

    /**
     * アップロードファイルのサイズを返します。
     *
     * @return int|null ファイルサイズ
     */
    public function getSize(): ?int
    {
        if ($this->size === null && $this->isValid()) {
            $size = $this->file->getSize();
            if ($size !== false) {
                $this->size = $size;
            }
        }
        return $this->size;
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
            $mimeType = $getMimeType->file($this->file->getPathname());
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
        return $this->clientFilename;
    }

    /**
     * アップロードファイルのクライアントファイル拡張子を返します。
     *
     * @return string|null クライアントファイル拡張子
     */
    public function getClientExtension(): ?string
    {
        return pathinfo($this->clientFilename, \PATHINFO_EXTENSION);
    }

    /**
     * アップロードエラーコードを返します。
     *
     * @return int|null アップロードエラー
     */
    public function getError(): ?int
    {
        return $this->error;
    }

    /**
     * アップロードファイルが妥当かどうかを返します。
     *
     * @return bool アップロードファイルが妥当かどうか
     */
    public function isValid(): bool
    {
        return ($this->error === \UPLOAD_ERR_OK && $this->file->isFile());
    }

    /**
     * アップロードファイルが画像かどうかを返します。
     *
     * @return bool アップロードファイルが画像かどうか
     */
    public function isImage(): bool
    {
        if ($this->file->isFile()) {
            $imagesize = @getimagesize($this->file->getPathname());
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
    public function move(string $directory, string $filename): string
    {
        $destination = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . $filename;
        $source = $this->file->getPathname();
        if (!$this->isValid()) {
            throw new FilepathException(
                sprintf('The file could not move "%s" -> "%s"', $source, $destination)
            );
        }
        if (file_exists($destination) || !@rename($source, $destination)) {
            $error = error_get_last();
            $message = (isset($error['message'])) ? sprintf(' (%s)', strip_tags($error['message'])) : '';
            throw new FilepathException(
                sprintf('The file could not move "%s" -> "%s"%s', $source, $destination, $message)
            );
        }
        @chmod($destination, 0666 & ~umask());
        return $destination;
    }

    /**
     * アップロードファイルの内容を返します。
     *
     * @return string ファイルの内容
     */
    public function getContent(): string
    {
        if (!$this->file->isFile() || !$this->file->isReadable()) {
            throw new FilepathException(
                sprintf('The file "%s" could not read', $this->file->getPathname())
            );
        }
        $content = file_get_contents($this->file->getPathname());
        if ($content !== false) {
            return $content;
        }
        throw new FilepathException(
            sprintf('The file "%s" could not get contents', $this->file->getPathname())
        );
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

}
