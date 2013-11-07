<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Uploader;

use Volcanus\FileUploader\Exception\UploaderException;
use Volcanus\FileUploader\FileValidatorInterface;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Symfony Http-Foundationコンポーネント用アップローダ
 *
 * @author k.holy74@gmail.com
 */
class SymfonyHttpFoundationUploader implements UploaderInterface
{

	/**
	 * @var Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	private $file;

	/**
	 * @var array 設定オプション
	 */
	private $config;

	/**
	 * コンストラクタ
	 *
	 * @param Symfony\Component\HttpFoundation\File\UploadedFile
	 * @param array | ArrayAccess 設定オプション
	 */
	public function __construct(UploadedFile $file, $configurations = array())
	{
		$this->initialize($file, $configurations);
	}

	/**
	 * オブジェクトを初期化します。
	 *
	 * @param Symfony\Component\HttpFoundation\File\UploadedFile
	 * @param array | ArrayAccess 設定オプション
	 */
	public function initialize(UploadedFile $file, $configurations = array())
	{
		$this->file = $file;
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
					if ('\\' === \DIRECTORY_SEPARATOR) {
						$value = str_replace('\\', '/', $value);
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
	 * アップロードファイルのクライアントファイル名を返します。
	 *
	 * @return mixed クライアントファイル名
	 */
	public function getClientFilename()
	{
		return $this->file->getClientOriginalName();
	}

	/**
	 * アップロードファイルを検証します。
	 *
	 * @param Volcanus\FileUploader\FileValidatorInterface
	 * @return boolean
	 *
	 * @throws Volcanus\FileUploader\Exception\FilesizeException ファイルサイズが設定値を超えている場合
	 * @throws Volcanus\FileUploader\Exception\FilenameException 設定されたエンコーディングに存在しない文字がファイル名に含まれている場合
	 * @throws Volcanus\FileUploader\Exception\ExtensionException ファイルの拡張子が設定された拡張子の許可リストに一致しない場合
	 * @throws Volcanus\FileUploader\Exception\ImageTypeException 画像ファイルの拡張子がファイルの内容と一致しない場合
	 * @throws Volcanus\FileUploader\Exception\UploaderException その他何らかの理由でアップロードが受け付けられない場合
	 */
	public function validate(FileValidatorInterface $validator)
	{
		$validator->validateUploadError($this->file->getError());
		$validator->validateFilename($this->file->getClientOriginalName());
		$validator->validateImageType($this->file->getPathname(), $this->file->getClientOriginalExtension());
		$validator->validateFilesize($this->file->getSize());
		$validator->validateExtension($this->file->getClientOriginalExtension());
		return true;
	}

	/**
	 * アップロードファイルを移動し、移動したファイルのパスを返します。
	 *
	 * @param array | ArrayAccess 設定
	 * @return string 移動したファイルのパス
	 *
	 * @throws Volcanus\FileUploader\Exception\UploaderException アップロードファイルの移動に失敗した場合
	 */
	public function move($options = array())
	{
		if (isset($options['moveDirectory'])) {
			$this->config('moveDirectory', $options['moveDirectory']);
		}
		if (isset($options['moveRetry'])) {
			$this->config('moveRetry', $options['moveRetry']);
		}
		$moveDirectory = $this->config('moveDirectory');
		$moveRetry = $this->config('moveRetry');
		$extension = $this->file->getClientOriginalExtension();
		if (isset($moveDirectory)) {
			while ($moveRetry > 0) {
				$filename = uniqid();
				try {
					$movedFile = $this->file->move($moveDirectory, (strlen($extension) >= 1) ? $filename . '.' . $extension : $filename);
					return $movedFile->getRealpath();
				} catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
				}
				$moveRetry--;
			}
		}
		throw new UploaderException(
			sprintf('A file was not able to be created in moveDirectory:%s.', $moveDirectory)
		);
	}

}
