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
 * Symfony Http-Foundationアップロードファイル
 *
 * @author k.holy74@gmail.com
 */
class SymfonyFile implements FileInterface
{

	/**
	 * @var \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	private $file;

	/**
	 * コンストラクタ
	 *
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
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
	 * @return string クライアントファイル名
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
	 * アップロードファイルが妥当かどうかを返します。
	 *
	 * @return boolean アップロードファイルが妥当かどうか
	 */
	public function isValid()
	{
		return $this->file->isValid();
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
	 * @param string $directory 移動先ディレクトリ
	 * @param string $filename 移動先ファイル名
	 * @return string 移動先ファイルパス
	 */
	public function move($directory, $filename)
	{
		$source = $this->file->getPathname();
		$destination = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . $filename;
		if ($this->file->isValid() && !file_exists($destination)) {
			try {
				$file = $this->file->move($directory, $filename);
				return $file->getPathname();
			} catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
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
