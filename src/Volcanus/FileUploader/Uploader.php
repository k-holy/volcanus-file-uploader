<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader;

use Volcanus\FileUploader\FileValidator;
use Volcanus\FileUploader\File\FileInterface;
use Volcanus\FileUploader\Exception\FilepathException;
use Volcanus\FileUploader\Exception\UploaderException;

/**
 * アップローダ
 *
 * @author k.holy74@gmail.com
 */
class Uploader
{

	/**
	 * @var array 設定オプション
	 */
	private $config;

	/**
	 * コンストラクタ
	 *
	 * @param array | ArrayAccess 設定オプション
	 */
	public function __construct($configurations = array())
	{
		$this->initialize($configurations);
	}

	/**
	 * オブジェクトを初期化します。
	 *
	 * @param array | ArrayAccess 設定オプション
	 */
	public function initialize($configurations = array())
	{
		$this->config = array();
		$this->config['moveDirectory'] = null;
		$this->config['moveRetry'] = null;
		if (!empty($configurations)) {
			foreach ($configurations as $name => $value) {
				$this->config($name, $value);
			}
		}
		return $this;
	}

	/**
	 * 引数1の場合は指定された設定の値を返します。
	 * 引数2の場合は指定された設置の値をセットして$thisを返します。
	 *
	 * @param string 設定名
	 * @return mixed 設定値 または $this
	 */
	public function config($name)
	{
		switch (func_num_args()) {
		case 1:
			return $this->config[$name];
		case 2:
			$value = func_get_arg(1);
			if (isset($value)) {
				switch ($name) {
				case 'moveRetry':
					if (!is_int($value) && !ctype_digit($value)) {
						throw new \InvalidArgumentException(
							sprintf('The config parameter "%s" accepts numeric.', $name));
					}
					$value = intval($value);
					break;
				case 'moveDirectory':
					if (!is_string($value)) {
						throw new \InvalidArgumentException(
							sprintf('The config parameter "%s" only accepts string.', $name));
					}
					break;
				default:
					throw new \InvalidArgumentException(
						sprintf('The config parameter "%s" is not defined.', $name)
					);
				}
				$this->config[$name] = $value;
			}
			return $this;
		}
		throw new \InvalidArgumentException('Invalid argument count.');
	}

	/**
	 * バリデータを利用してアップロードファイルを検証します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @param Volcanus\FileUploader\FileValidator アップロードファイルバリデータ
	 *
	 * @throws Acme\Uploader\Exception\FilesizeException ファイルサイズが設定値を超えている場合
	 * @throws Acme\Uploader\Exception\FilenameException 設定されたエンコーディングに存在しない文字がファイル名に含まれている場合
	 * @throws Acme\Uploader\Exception\ExtensionException ファイルの拡張子が設定された拡張子の許可リストに一致しない場合
	 * @throws Acme\Uploader\Exception\ImageTypeException 画像ファイルの拡張子がファイルの内容と一致しない場合
	 * @throws Acme\Uploader\Exception\UploaderException その他何らかの理由でアップロードが受け付けられない場合
	 */
	public function validate(FileInterface $file, FileValidator $validator)
	{
		$validator->validateUploadError($file);

		if ($validator->config('filenameEncoding') !== null) {
			$validator->validateFilename($file);
		}

		if ($validator->config('maxFilesize') !== null) {
			$validator->validateFilesize($file);
		}

		if ($validator->config('allowableType') !== null) {
			$validator->validateExtension($file);
		}

		if ($file->isImage()) {

			$validator->validateImageType($file);

			if ($validator->config('maxWidth') !== null || $validator->config('maxHeight') !== null) {
				$validator->validateImageSize($file);
			}

		}

		return true;
	}

	/**
	 * アップロードファイルを移動し、移動先のファイルパスを返します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @return string 移動先のファイルパス
	 *
	 * @throws Acme\Uploader\Exception\UploaderException アップロードファイルの移動に失敗した場合
	 */
	public function move(FileInterface $file)
	{
		$moveDirectory = $this->config('moveDirectory');
		$moveRetry = $this->config('moveRetry');
		if ($file->isValid()) {
			$this->prepareMove($moveDirectory);
			$extension = $file->getClientExtension();
			while ($moveRetry > 0) {
				$filename = uniqid();
				if (strlen($extension) >= 1) {
					$filename .= '.' . $extension;
				}
				try {
					return $file->move($moveDirectory, $filename);
				} catch (FilepathException $e) {
				}
				$moveRetry--;
			}
		}
		throw new UploaderException(
			sprintf('A temporary file was not able to be created in %s.', $moveDirectory)
		);
	}

	private function prepareMove($directory)
	{
		if (!is_dir($directory) && false === @mkdir($directory, 0777, true)) {
			throw new UploaderException(
				sprintf('The directory "%s" could not create.', $directory)
			);
		}
		if (!is_writable($directory)) {
			throw new UploaderException(
				sprintf('The directory "%s" could not write.', $directory)
			);
		}
	}

}
