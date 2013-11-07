<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader;

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
	 * アップロードエラー定数を検証します。
	 *
	 * @param int アップロードエラー定数 (UPLOAD_ERR_***)
	 */
	public function validateUploadError($error)
	{
		switch ($error) {
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
	 * @param string ファイル名
	 * @param string エンコーディング
	 *
	 * @retun boolean 検証結果
	 */
	public function validateFilename($filename, $encoding = null)
	{
		if (!isset($encoding) || strlen($encoding) === 0) {
			$encoding = mb_internal_encoding();
		}
		if (mb_check_encoding($filename, $encoding)) {
			return true;
		}
		throw new FilenameException(
			sprintf('The filename is including invalid bytes for encoding:%s.', $encoding)
		);
	}

	/**
	 * ファイルサイズが指定サイズ以内かどうかを検証します。
	 *
	 * @param mixed ファイルサイズ
	 * @param mixed ファイルサイズ上限値
	 * @retun boolean 検証結果
	 *
	 * @throws \InvalidArgumentException ファイル最大値の指定が解析不能、またはファイルサイズの取得に失敗した場合
	 * @throws \Volcanus\FileUploader\Exception\FilesizeException ファイルサイズが上限値を超えている場合
	 */
	public function validateFilesize($filesize, $maxFilesize)
	{
		$maxBytes = (is_string($maxFilesize)) ? $this->convertToBytes(sprintf('%sB', $maxFilesize)) : $maxFilesize;
		if (false === $maxBytes) {
			throw new \InvalidArgumentException(
				sprintf('The maxFilesize "%s" is invalid format.', $maxFilesize)
			);
		}
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
	 * @param string 拡張子
	 * @param string 許可する拡張子（カンマ区切りで複数指定可）
	 * @retun boolean 検証結果
	 *
	 * @throws \Volcanus\FileUploader\Exception\ExtensionException 拡張子が許可する拡張子に一致しない場合
	 */
	public function validateExtension($extension, $allowableType)
	{
		$allowableTypes = explode(',', $allowableType);
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
	 * @param string ファイルパス
	 * @param string 拡張子
	 *
	 * @throws \Volcanus\FileUploader\Exception\ImageTypeException 拡張子が内容と一致しない場合
	 */
	public function validateImageType($filepath, $extension)
	{
		if (!in_array(strtolower($extension), self::$imageExtensions)) {
			return;
		}
		$imageType = $this->getImageType($filepath);
		if ($imageType & imagetypes()) {
			switch (strtolower($extension)) {
			case 'jpeg':
			case 'jpg':
				if (strcasecmp('jpeg', image_type_to_extension($imageType, false)) === 0) {
					return true;
				}
				break;
			default:
				if (strcasecmp($extension, image_type_to_extension($imageType, false)) === 0) {
					return true;
				}
				break;
			}
		}
		throw new ImageTypeException(
			sprintf('The file extension "%s" does not match ImageType.', $extension)
		);
	}

	/**
	 * 拡張子が指定したファイルの画像種別と一致するかどうかを検証します。
	 *
	 * @param string ファイルパス
	 * @param int 横幅上限値 (px)
	 * @param int 高さ上限値 (px)
	 * @retun boolean 検証結果
	 */
	public function validateImageSize($filepath, $maxWidth, $maxHeight)
	{
		$extension = strtolower(pathinfo($filepath, \PATHINFO_EXTENSION));
		if (!in_array($extension, self::$imageExtensions)) {
			return true;
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
	 * @param string ファイルパス
	 * @retun mixed 定数値またはFALSE
	 */
	private function getImageType($filepath)
	{
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
	 * @param string バイト数または単位付きバイト数(B,KB,MB,GB,TB,PB,EB,ZB,YB)
	 * @return mixed バイト数またはFALSE
	 */
	private function convertToBytes($data)
	{
		$units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
		$pattern = sprintf('/\A(\d+)(%s)*\z/i', implode('|', $units));
		if (preg_match($pattern, $data, $matches)) {
			if (isset($matches[2])) {
				$index = array_search($matches[2], $units);
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
