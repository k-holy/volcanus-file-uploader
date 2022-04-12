<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException as SymfonyFileException;
use Volcanus\FileUploader\Exception\FilepathException;

/**
 * Symfony Http-Foundationアップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class SymfonyFile implements FileInterface
{

    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * コンストラクタ
     *
     * @param UploadedFile $file
     */
    public function __construct(UploadedFile $file)
    {
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
        return $this->file->getSize();
    }

    /**
     * アップロードファイルのMIMEタイプを返します。
     *
     * @return string|null MIMEタイプ
     */
    public function getMimeType(): ?string
    {
        return $this->file->getMimeType();
    }

    /**
     * アップロードファイルのクライアントファイル名を返します。
     *
     * @return string|null クライアントファイル拡張子
     */
    public function getClientFilename(): ?string
    {
        return $this->file->getClientOriginalName();
    }

    /**
     * アップロードファイルのクライアントファイル拡張子を返します。
     *
     * @return string|null クライアントファイル拡張子
     */
    public function getClientExtension(): ?string
    {
        return $this->file->getClientOriginalExtension();
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
        return $this->file->isValid();
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
        $source = $this->file->getPathname();
        $destination = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . $filename;
        if ($this->file->isValid() && !file_exists($destination)) {
            try {
                $file = $this->file->move($directory, $filename);
                return $file->getPathname();
            } catch (SymfonyFileException $e) {
                throw new FilepathException(
                    sprintf('The file could not move "%s" -> "%s"', $source, $destination), 0, $e
                );
            }
        }
        throw new FilepathException(
            sprintf('The file could not move "%s" -> "%s"', $source, $destination)
        );
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
