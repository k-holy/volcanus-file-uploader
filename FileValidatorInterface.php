<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader;

/**
 * ファイルバリデータインタフェース
 *
 * @author k.holy74@gmail.com
 */
interface FileValidatorInterface
{

	/**
	 * アップロードエラー定数を検証します。
	 *
	 * @param int アップロードエラー定数 (UPLOAD_ERR_***)
	 */
	public function validateUploadError($error);

	/**
	 * ファイル名が指定されたエンコーディングで有効かどうかを検証します。
	 *
	 * @param string ファイル名
	 * @param string エンコーディング
	 * @retun boolean 検証結果
	 */
	public function validateFilename($filename, $encoding);

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
	public function validateFilesize($filesize, $maxFilesize);

	/**
	 * 拡張子が指定したファイル種別に含まれているかどうかを検証します。
	 *
	 * @param string 拡張子
	 * @param string 許可する拡張子（カンマ区切りで複数指定可）
	 * @retun boolean 検証結果
	 *
	 * @throws \Volcanus\FileUploader\Exception\ExtensionException 拡張子が許可する拡張子に一致しない場合
	 */
	public function validateExtension($extension, $allowableType);

	/**
	 * 拡張子が指定したファイルの画像種別と一致するかどうかを検証します。
	 *
	 * @param string ファイルパス
	 * @param string 拡張子
	 * @retun boolean 検証結果
	 *
	 * @throws \Volcanus\FileUploader\Exception\ImageTypeException 拡張子が内容と一致しない場合
	 */
	public function validateImageType($filepath, $extension);

	/**
	 * 拡張子が指定したファイルの画像種別と一致するかどうかを検証します。
	 *
	 * @param string ファイルパス
	 * @param int 横幅上限値 (px)
	 * @param int 高さ上限値 (px)
	 * @retun boolean 検証結果
	 */
	public function validateImageSize($filepath, $maxWidth, $maxHeight);

}
