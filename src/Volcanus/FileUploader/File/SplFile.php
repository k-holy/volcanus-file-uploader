<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
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
	private $file;

	/**
	 * @var string アップロード元のファイル名
	 */
	private $clientFilename;

	/**
	 * @var int アップロードされたファイルのサイズ
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
	 * @param string アップロードされたファイルのパス
	 * @param string アップロードされたファイルの元のファイル名
	 * @param int アップロードエラーコード
	 */
	public function __construct(\SplFileInfo $file, $clientFilename = null, $error = null)
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
		if ($this->size === null && $this->isValid()) {
			$this->size = $this->file->getSize();
		}
		return $this->size;
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
			$this->mimeType = $getMimeType->file($this->file->getPathname());
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
		return $this->clientFilename;
	}

	/**
	 * アップロードファイルのクライアントファイル拡張子を返します。
	 *
	 * @return string クライアントファイル拡張子
	 */
	public function getClientExtension()
	{
		return pathinfo($this->clientFilename, \PATHINFO_EXTENSION);
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
	 * アップロードファイルが妥当かどうかを返します。
	 *
	 * @return boolean アップロードファイルが妥当かどうか
	 */
	public function isValid()
	{
		return ($this->error === \UPLOAD_ERR_OK && $this->file->isFile());
	}

	/**
	 * アップロードファイルが画像かどうかを返します。
	 *
	 * @return boolean アップロードファイルが画像かどうか
	 */
	public function isImage()
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
	 * @param string 移動先ディレクトリ
	 * @param string 移動先ファイル名
	 * @param string 移動先ファイルパス
	 */
	public function move($directory, $filename)
	{
		$destination = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . $filename;
		$source = $this->file->getPathname();
		if (!$this->isValid()) {
			throw new FilepathException(
				sprintf('The file could not move "%s" -> "%s"', $source, $destination)
			);
		}
		if (false === @rename($source, $destination)) {
			$error = error_get_last();
			$message = (isset($error['message'])) ? sprintf(' (%s)', strip_tags($error['message'])) : '';
			throw new FilepathException(
				sprintf('The file could not move "%s" -> "%s"%s', $source, $destination, $message)
			);
		}
		@chmod($destination, 0666 &~umask());
		return $destination;
	}

	/**
	 * アップロードファイルの内容を返します。
	 *
	 * @return string ファイルの内容
	 */
	public function getContent()
	{
		if (!$this->file->isFile() || !$this->file->isReadable()) {
			throw new FilepathException(
				sprintf('The file "%s" could not read', $this->file->getPathname())
			);
		}
		return file_get_contents($this->file->getPathname());
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

}
