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
	 * @var int アップロードされたファイルのサイズ
	 */
	private $size;

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
	 * @param array アップロードされたファイルの情報 $_FILES['userfile']
	 */
	public function __construct(array $file)
	{
		if (!array_key_exists('tmp_name', $file)) {
			throw new \InvalidArgumentException(
				'The files key "tmp_name" does not exists.'
			);
		}
		$this->path = $file['tmp_name'];

		$isFile = is_file($this->path);

		$this->error = (!array_key_exists('error', $file)) ? \UPLOAD_ERR_OK : $file['error'];

		if ($this->error === \UPLOAD_ERR_OK && !$isFile) {
			throw new \InvalidArgumentException(
				sprintf('The file "%s" is not a file.', $this->path)
			);
		}

		if (!array_key_exists('name', $file)) {
			if (!$isFile) {
				throw new \InvalidArgumentException(
					'The files key "name" does not exists.'
				);
			}
			$this->clientFilename = basename($this->path);
		} else {
			$this->clientFilename = $file['name'];
		}

		if ($isFile) {
			$this->size = filesize($this->path);
		} elseif (array_key_exists('size', $file)) {
			$this->size = $file['size'];
		}

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
		return $this->size;
	}

	/**
	 * アップロードファイルのMIMEタイプを返します。
	 *
	 * @return string MIMEタイプ
	 */
	public function getMimeType()
	{
		if ($this->isValid()) {
			$getMimeType = new \finfo(\FILEINFO_MIME_TYPE);
			return $getMimeType->file($this->path);
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
	 * アップロードファイルを指定されたディレクトリに移動し、移動先のファイルパスを返します。
	 *
	 * @param string 移動先ディレクトリ
	 * @param string 移動先ファイル名
	 * @param string 移動先ファイルパス
	 */
	public function move($directory, $filename)
	{
		$destination = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;
		$source = $this->path;
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

}
