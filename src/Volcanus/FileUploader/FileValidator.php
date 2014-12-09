<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader;

use Volcanus\FileUploader\File\FileInterface;
use Volcanus\FileUploader\Exception\FilenameException;
use Volcanus\FileUploader\Exception\FilesizeException;
use Volcanus\FileUploader\Exception\ExtensionException;
use Volcanus\FileUploader\Exception\ImageTypeException;
use Volcanus\FileUploader\Exception\ImageWidthException;
use Volcanus\FileUploader\Exception\ImageHeightException;
use Volcanus\FileUploader\Exception\UploaderException;

/**
 * ファイルバリデータ
 *
 * @author k.holy74@gmail.com
 */
class FileValidator
{

	private static $imageExtensions = array(
		'gif',
		'jpeg',
		'jpg',
		'png',
		'swf',
		'psd',
		'bmp',
		'tiff',
		'jpc',
		'jp2',
		'jpf',
		'swc',
		'aiff',
		'wbmp',
		'xbm',
	);

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
	 * @param Symfony\Component\HttpFoundation\File\UploadedFile
	 * @param array | ArrayAccess 設定オプション
	 */
	public function initialize($configurations = array())
	{
		$this->config = array();
		$this->config['maxFilesize'] = null;
		$this->config['allowableType'] = null;
		$this->config['filenameEncoding'] = null;
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
				case 'allowableType':
				case 'filenameEncoding':
					if (!is_string($value)) {
						throw new \InvalidArgumentException(
							sprintf('The config parameter "%s" only accepts string.', $name));
					}
					break;
				case 'maxWidth':
				case 'maxHeight':
					if (!is_int($value) && !ctype_digit($value)) {
						throw new \InvalidArgumentException(
							sprintf('The config parameter "%s" accepts numeric.', $name));
					}
					$value = intval($value);
					break;
				// 数値または数値 + 単位(K|M|G|T|P|E|Z|Y)
				case 'maxFilesize':
					if (!is_int($value) && !preg_match('/\A(\d+)([K|M|G|T|P|E|Z|Y]?)\z/i', $value)) {
						throw new \InvalidArgumentException(
							sprintf('The config parameter "%s" accepts numeric or string.', $name));
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
	 * アップロードエラー定数を検証します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 */
	public function validateUploadError(FileInterface $file)
	{
		switch ($file->getError()) {
		// エラーはなく、ファイルアップロードは成功しています。
		case \UPLOAD_ERR_OK:
			return true;
		// アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
		case \UPLOAD_ERR_INI_SIZE:
			throw new FilesizeException(
				sprintf('The uploaded file is larger than upload_max_filesize:%d.', ini_get('upload_max_filesize'))
			);
		// アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
		case \UPLOAD_ERR_FORM_SIZE:
			throw new FilesizeException('The uploaded file is larger than requested MAX_FILE_SIZE.');
		// アップロードされたファイルは一部のみしかアップロードされていません。
		case \UPLOAD_ERR_PARTIAL:
		// ファイルはアップロードされませんでした。
		case \UPLOAD_ERR_NO_FILE:
		// テンポラリフォルダがありません。
		case \UPLOAD_ERR_NO_TMP_DIR:
		// ディスクへの書き込みに失敗しました。
		case \UPLOAD_ERR_CANT_WRITE:
		// PHP の拡張モジュールがファイルのアップロードを中止しました。
		case \UPLOAD_ERR_EXTENSION:
		default:
			break;
		}
		throw new UploaderException('The uploaded file is invalid for some reasons.');
	}

	/**
	 * ファイル名が指定されたエンコーディングで有効かどうかを検証します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @param string エンコーディング
	 * @retun boolean 検証結果
	 */
	public function validateFilename(FileInterface $file, $encoding = null)
	{
		if (!isset($encoding)) {
			$encoding = $this->config('filenameEncoding');
		}
		if (!isset($encoding)) {
			return;
		}
		if (mb_check_encoding($file->getClientFilename(), $encoding)) {
			return true;
		}
		throw new FilenameException(
			sprintf('The filename is including invalid bytes for encoding:%s.', $encoding)
		);
	}

	/**
	 * ファイルサイズが指定サイズ以内かどうかを検証します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @param mixed ファイルサイズ上限値
	 * @retun boolean 検証結果
	 *
	 * @throws \InvalidArgumentException ファイル最大値の指定が解析不能、またはファイルサイズの取得に失敗した場合
	 * @throws \Volcanus\FileUploader\Exception\FilesizeException ファイルサイズが上限値を超えている場合
	 */
	public function validateFilesize(FileInterface $file, $maxFilesize = null)
	{
		if ($maxFilesize === null) {
			$maxFilesize = $this->config('maxFilesize');
		}
		if ($maxFilesize === null) {
			return;
		}
		$maxBytes = (is_string($maxFilesize))
			? $this->convertToBytes($maxFilesize)
			: $maxFilesize;
		if (false === $maxBytes) {
			throw new \InvalidArgumentException(
				sprintf('The maxFilesize "%s" is invalid format.', $maxFilesize)
			);
		}
		$filesize = $file->getSize();
		if ($filesize < 0) {
			$filesize = sprintf('%u', $filesize);
		}
		if ($filesize <= $maxBytes) {
			return true;
		}
		throw new FilesizeException(
			sprintf('The uploaded file\'s size %d bytes is larger than maxFilesize:"%s"', $filesize, $maxFilesize)
		);
	}

	/**
	 * 拡張子が指定したファイル種別に含まれているかどうかを検証します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @param string 許可する拡張子（カンマ区切りで複数指定可）
	 * @retun boolean 検証結果
	 *
	 * @throws \Volcanus\FileUploader\Exception\ExtensionException 拡張子が許可する拡張子に一致しない場合
	 */
	public function validateExtension(FileInterface $file, $allowableType = null)
	{
		if ($allowableType === null) {
			$allowableType = $this->config('allowableType');
		}
		if ($allowableType === null) {
			return;
		}
		$allowableTypes = explode(',', $allowableType);
		$extension = $file->getClientExtension();
		foreach ($allowableTypes as $type) {
			switch ($type) {
			case 'jpeg':
			case 'jpg':
				if (strcasecmp($extension, 'jpeg') === 0 ||
					strcasecmp($extension, 'jpg') === 0
				) {
					return true;
				}
				break;
			default:
				if (strcasecmp($extension, $type) === 0) {
					return true;
				}
				break;
			}
		}
		throw new ExtensionException(
			sprintf('The uploaded file\'s extension "%s" is not allowable', $extension)
		);
	}

	/**
	 * 拡張子が指定したファイルの画像種別と一致するかどうかを検証します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @retun boolean 検証結果
	 *
	 * @throws \Volcanus\FileUploader\Exception\ImageTypeException 拡張子が内容と一致しない場合
	 */
	public function validateImageType(FileInterface $file)
	{
		$extension = $file->getClientExtension();
		if (!in_array(strtolower($extension), self::$imageExtensions)) {
			return;
		}
		$mimeType = $file->getMimeType();
		$imageType = $this->getImageType($file);
		switch (strtolower($extension)) {
		case 'jpeg':
		case 'jpg':
			if (strcasecmp('jpeg', image_type_to_extension($imageType, false)) === 0 &&
				strcasecmp($mimeType, image_type_to_mime_type($imageType)) === 0
			) {
				return true;
			}
			break;
		default:
			if (strcasecmp($extension, image_type_to_extension($imageType, false)) === 0 &&
				strcasecmp($mimeType, image_type_to_mime_type($imageType)) === 0
			) {
				return true;
			}
			break;
		}
		throw new ImageTypeException(
			sprintf('The file extension "%s" does not match ImageType.', $extension)
		);
	}

	/**
	 * 拡張子が指定したファイルの画像種別と一致するかどうかを検証します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @param int 横幅上限値 (px)
	 * @param int 高さ上限値 (px)
	 * @retun boolean 検証結果
	 */
	public function validateImageSize(FileInterface $file, $maxWidth = null, $maxHeight = null)
	{
		if ($maxWidth === null) {
			$maxWidth = $this->config('maxWidth');
		}
		if ($maxHeight === null) {
			$maxHeight = $this->config('maxHeight');
		}
		if ($maxWidth === null && $maxHeight === null) {
			return;
		}
		$filepath = $file->getPath();
		$extension = $file->getClientExtension();
		if (!in_array($extension, self::$imageExtensions)) {
			return;
		}
		if (false !== (list($width, $height, $type, $attr) = getimagesize($filepath))) {
			if (!empty($maxWidth) && $width > $maxWidth) {
				throw new ImageWidthException(
					sprintf('The image width %d pixels is larger than maxWidth:%d', $width, $maxWidth)
				);
			}
			if (!empty($maxHeight) && $height > $maxHeight) {
				throw new ImageHeightException(
					sprintf('The image height %d pixels is larger than maxHeight:%d', $height, $maxHeight)
				);
			}
			return true;
		}
		throw new \InvalidArgumentException(
			sprintf('The filepath "%s" is invalid image.', $filepath)
		);
	}

	/**
	 * 指定されたファイルのImageType定数を返します。
	 *
	 * @param Volcanus\FileUploader\File\FileInterface アップロードファイル
	 * @retun mixed 定数値またはFALSE
	 */
	private function getImageType(FileInterface $file)
	{
		$filepath = $file->getPath();
		if (function_exists('exif_imagetype')) {
			return exif_imagetype($filepath);
		}
		if (false !== (list($width, $height, $type, $attr) = getimagesize($filepath))) {
			return $type;
		}
		return false;
	}

	/**
	 * 単位付きバイト数をバイト数に変換して返します。
	 * 2GB以上を扱うにはBCMath関数が有効になっている必要があります。
	 * ファイル最大値の指定が解析不能な場合はfalseを返します。
	 *
	 * @param string バイト数または単位付きバイト数(K,M,G,T,P,E,Z,Y)
	 * @return mixed バイト数またはFALSE
	 */
	private function convertToBytes($data)
	{
		if (preg_match('/\A(\d+)([K|M|G|T|P|E|Z|Y]?)\z/i', $data, $matches)) {
			if (isset($matches[2])) {
				$unit = sprintf('%sB', $matches[2]);
				$units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
				$index = array_search($unit, $units);
				if (function_exists('bcpow')) {
					return bcmul($matches[1], bcpow(1024, (int)$index));
				} else {
					return $matches[1] * pow(1024, (int)$index);
				}
			}
			return $matches[1];
		}
		return false;
	}

}
