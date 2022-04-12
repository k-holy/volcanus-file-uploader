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
 * ネイティブ($_FILES)アップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class NativeFile implements FileInterface
{

    /**
     * @var string アップロードされたファイルのパス
     */
    private $path;

    /**
     * @var string アップロード元のファイル名
     */
    private $clientFilename;

    /**
     * @var int|null アップロードされたファイルのサイズ
     */
    private $size;

    /**
     * @var string アップロードされたファイルのMIMEタイプ
     */
    private $mimeType;

    /**
     * @var int アップロードエラーコード
     * @see http://jp.php.net/manual/ja/features.file-upload.errors.php
     */
    private $error;

    /**
     * コンストラクタ
     *
     * @param array $file アップロードされたファイルの情報 $_FILES['userfile']
     */
    public function __construct(array $file)
    {
        if (!array_key_exists('tmp_name', $file)) {
            throw new \InvalidArgumentException(
                'The files key "tmp_name" does not exists.'
            );
        }
        $this->path = $file['tmp_name'];

        $this->error = (!array_key_exists('error', $file)) ? \UPLOAD_ERR_OK : $file['error'];

        if ($this->error === \UPLOAD_ERR_OK && !is_file($this->path)) {
            throw new \InvalidArgumentException(
                sprintf('The file "%s" is not a file.', $this->path)
            );
        }

        if (array_key_exists('name', $file)) {
            $this->clientFilename = $file['name'];
        }

        $this->size = null;
        $this->mimeType = null;
    }

    /**
     * アップロードファイルのパスを返します。
     *
     * @return string|null ファイルパス
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * アップロードファイルのサイズを返します。
     *
     * @return int|null ファイルサイズ
     */
    public function getSize(): ?int
    {
        if ($this->size === null && $this->isValid()) {
            $size = filesize($this->path);
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
            $mimeType = $getMimeType->file($this->path);
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
        if ($this->clientFilename !== null) {
            return pathinfo($this->clientFilename, \PATHINFO_EXTENSION);
        }
        return null;
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
        return ($this->error === \UPLOAD_ERR_OK && is_file($this->path));
    }

    /**
     * アップロードファイルが画像かどうかを返します。
     *
     * @return bool アップロードファイルが画像かどうか
     */
    public function isImage(): bool
    {
        if (is_file($this->path)) {
            $imagesize = @getimagesize($this->path);
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
        $source = $this->path;
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
        if (!is_file($this->path) || !is_readable($this->path)) {
            throw new FilepathException(
                sprintf('The file "%s" could not read', $this->path)
            );
        }
        $content = file_get_contents($this->path);
        if ($content !== false) {
            return $content;
        }
        throw new FilepathException(
            sprintf('The file "%s" could not get contents', $this->path)
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
