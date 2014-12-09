<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

/**
 * Symfony Http-Foundationアップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class SymfonyFile implements FileInterface
{

	/**
	 * @var Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	private $file;

	/**
	 * コンストラクタ
	 *
	 * @param Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function __construct(\Symfony\Component\HttpFoundation\File\UploadedFile $file)
	{
		$this->file = $file;
	}

	/**
	 * アップロードファイルのパスを返します。
	 *
	 * @return string ファイルパス
	 */
	public function getPath()
	{
		return $this->file->getPathname();
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
		return $this->file->getMimeType();
	}

	/**
	 * アップロードファイルのクライアントファイル名を返します。
	 *
	 * @return mixed クライアントファイル名
	 */
	public function getClientFilename()
	{
		return $this->file->getClientOriginalName();
	}

	/**
	 * アップロードファイルのクライアントファイル拡張子を返します。
	 *
	 * @return string クライアントファイル拡張子
	 */
	public function getClientExtension()
	{
		return $this->file->getClientOriginalExtension();
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
	 * アップロードが完了したかどうかを返します。
	 *
	 * @return boolean アップロードが完了したかどうか
	 */
	public function isValid()
	{
		return $this->file->isValid();
	}

}
