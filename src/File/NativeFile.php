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
	 * @return string ファイルパス
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * アップロードファイルのサイズを返します。
	 *
	 * @return int ファイルサイズ
	 */
	public function getSize()
	{
		if ($this->size === null && $this->isValid()) {
			$this->size = filesize($this->path);
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
			$this->mimeType = $getMimeType->file($this->path);
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
		return ($this->error === \UPLOAD_ERR_OK && is_file($this->path));
	}

	/**
	 * アップロードファイルが画像かどうかを返します。
	 *
	 * @return boolean アップロードファイルが画像かどうか
	 */
	public function isImage()
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
	public function move($directory, $filename)
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
		if (!is_file($this->path) || !is_readable($this->path)) {
			throw new FilepathException(
				sprintf('The file "%s" could not read', $this->path)
			);
		}
		return file_get_contents($this->path);
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
