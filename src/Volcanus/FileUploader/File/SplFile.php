<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

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
	private $file;

	/**
	 * アップロード元のファイル名
	 *
	 * @var string
	 */
	private $clientFilename;

	/**
	 * アップロードエラーコード
	 *
	 * @var int
	 * @see http://jp.php.net/manual/ja/features.file-upload.errors.php
	 */
	private $error;

	/**
	 * コンストラクタ
	 *
	 * @param string アップロードされたファイルのパス
	 * @param string アップロードされたファイルの元のファイル名
	 * @param int アップロードエラーコード
	 */
	public function __construct(\SplFileInfo $file, $clientFilename = null, $error = null)
	{
		$this->error = ($error === null) ? \UPLOAD_ERR_OK : $error;
		if ($this->error === \UPLOAD_ERR_OK && !is_file($file->getPathname())) {
			throw new \InvalidArgumentException(
				sprintf('The file "%s" is not a file.', (string)$file)
			);
		}
		$this->clientFilename = ($clientFilename === null) ? $file->getBasename() : $clientFilename;
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
		return $this->clientFilename;
	}

	/**
	 * アップロードファイルのクライアントファイル拡張子を返します。
	 *
	 * @return string クライアントファイル拡張子
	 */
	public function getClientExtension()
	{
		return pathinfo($this->clientFilename, PATHINFO_EXTENSION);
	}

	/**
	 * アップロードエラーコードを返します。
	 *
	 * @return int アップロードエラーコード
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * アップロードが完了したかどうかを返します。
	 *
	 * @return boolean アップロードが完了したかどうか
	 */
	public function isValid()
	{
		return ($this->error === \UPLOAD_ERR_OK);
	}

}
