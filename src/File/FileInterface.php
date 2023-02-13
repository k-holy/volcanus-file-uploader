<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

/**
 * アップロードファイルインタフェース
 *
 * @author k.holy74@gmail.com
 */
interface FileInterface
{

    /**
     * アップロードファイルのパスを返します。
     *
     * @return string|null ファイルパス
     */
    public function getPath(): ?string;

    /**
     * アップロードファイルのサイズを返します。
     *
     * @return int|null ファイルサイズ
     */
    public function getSize(): ?int;

    /**
     * アップロードファイルのMIMEタイプを返します。
     *
     * @return string|null MIMEタイプ
     */
    public function getMimeType(): ?string;

    /**
     * アップロードファイルのクライアントファイル名を返します。
     *
     * @return string|null クライアントファイル名
     */
    public function getClientFilename(): ?string;

    /**
     * アップロードファイルのクライアントファイル拡張子を返します。
     *
     * @return string|null クライアントファイル拡張子
     */
    public function getClientExtension(): ?string;

    /**
     * アップロードエラーを返します。
     *
     * @return int|null アップロードエラー
     */
    public function getError(): ?int;

    /**
     * アップロードファイルが妥当かどうかを返します。
     *
     * @return bool アップロードファイルが妥当かどうか
     */
    public function isValid(): bool;

    /**
     * アップロードファイルが画像かどうかを返します。
     *
     * @return bool アップロードファイルが画像かどうか
     */
    public function isImage(): bool;

    /**
     * アップロードファイルを指定されたディレクトリに移動し、移動先のファイルパスを返します。
     *
     * @param string $directory 移動先ディレクトリ
     * @param string $filename 移動先ファイル名
     * @return string 移動先ファイルパス
     */
    public function move(string $directory, string $filename): string;

    /**
     * アップロードファイルの内容を返します。
     *
     * @return string ファイルの内容
     */
    public function getContent(): string;

    /**
     * アップロードファイルの内容をDataURI形式で返します。
     *
     * @return string DataURI
     */
    public function getContentAsDataUri(): string;

}
