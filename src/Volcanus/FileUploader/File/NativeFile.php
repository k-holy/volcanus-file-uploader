<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

/**
 * ネイティブ($_FILES)アップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class NativeFile implements FileInterface
{

	/**
	 * @var array アップロードされたファイルの情報 $_FILES['userfile']
	 */
	private $file;

	/**
	 * コンストラクタ
	 *
	 * @param array アップロードされたファイルの情報 $_FILES['userfile']
	 */
	public function __construct(array $file)
	{
		if (!array_key_exists('tmp_name', $file)) {
			throw new \InvalidArgumentException(
				'The files key "tmp_name" does not exists.'
			);
		}

		$isFile = is_file($file['tmp_name']);

		if (!array_key_exists('name', $file) || $file['name'] === null) {
			if (!$isFile) {
				throw new \InvalidArgumentException(
					'The files key "name" does not exists.'
				);
			}
			$file['name'] = basename($file['tmp_name']);
		}

		if (!array_key_exists('size', $file) || $file['size'] === null) {
			if (!$isFile) {
				throw new \InvalidArgumentException(
					'The files key "size" does not exists.'
				);
			}
			$file['size'] = filesize($file['tmp_name']);
		}

		if (!array_key_exists('error', $file) || $file['error'] === null) {
			$file['error'] = \UPLOAD_ERR_OK;
		}

		if ($file['error'] === \UPLOAD_ERR_OK && !$isFile) {
			throw new \InvalidArgumentException(
				sprintf('The file "%s" is not a file.', $file['tmp_name'])
			);
		}
		$this->file = $file;
	}

	/**
	 * アップロードファイルのパスを返します。
	 *
	 * @return string ファイルパス
	 */
	public function getPath()
	{
		return $this->file['tmp_name'];
	}

	/**
	 * アップロードファイルのサイズを返します。
	 *
	 * @return int ファイルサイズ
	 */
	public function getSize()
	{
		return $this->file['size'];
	}

	/**
	 * アップロードファイルのMIMEタイプを返します。
	 *
	 * @return string MIMEタイプ
	 */
	public function getMimeType()
	{
		if ($this->getError() === \UPLOAD_ERR_OK) {
			$getMimeType = new \finfo(\FILEINFO_MIME_TYPE);
			return $getMimeType->file($this->getPath());
		}
		return null;
	}

	/**
	 * アップロードファイルのクライアントファイル名を返します。
	 *
	 * @return mixed クライアントファイル名
	 */
	public function getClientFilename()
	{
		return $this->file['name'];
	}

	/**
	 * アップロードファイルのクライアントファイル拡張子を返します。
	 *
	 * @return string クライアントファイル拡張子
	 */
	public function getClientExtension()
	{
		return pathinfo($this->getClientFilename(), PATHINFO_EXTENSION);
	}

	/**
	 * アップロードエラーコードを返します。
	 *
	 * @return int アップロードエラーコード
	 */
	public function getError()
	{
		return $this->file['error'];
	}

	/**
	 * アップロードが完了したかどうかを返します。
	 *
	 * @return boolean アップロードが完了したかどうか
	 */
	public function isValid()
	{
		return ($this->file['error'] === \UPLOAD_ERR_OK);
	}

}
