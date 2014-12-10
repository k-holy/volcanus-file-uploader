<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\File;

/**
 * アップロードファイルインタフェース
 *
 * @author k.holy74@gmail.com
 */
interface FileInterface
{

	/**
	 * アップロードファイルのパスを返します。
	 *
	 * @return string ファイルパス
	 */
	public function getPath();

	/**
	 * アップロードファイルのサイズを返します。
	 *
	 * @return int ファイルサイズ
	 */
	public function getSize();

	/**
	 * アップロードファイルのMIMEタイプを返します。
	 *
	 * @return string MIMEタイプ
	 */
	public function getMimeType();

	/**
	 * アップロードファイルのクライアントファイル名を返します。
	 *
	 * @return mixed クライアントファイル名
	 */
	public function getClientFilename();

	/**
	 * アップロードファイルのクライアントファイル拡張子を返します。
	 *
	 * @return string クライアントファイル拡張子
	 */
	public function getClientExtension();

	/**
	 * アップロードエラーを返します。
	 *
	 * @return int アップロードエラー
	 */
	public function getError();

	/**
	 * アップロードファイルが妥当かどうかを返します。
	 *
	 * @return boolean アップロードファイルが妥当かどうか
	 */
	public function isValid();

	/**
	 * アップロードファイルを指定されたディレクトリに移動し、移動先のファイルパスを返します。
	 *
	 * @param string 移動先ディレクトリ
	 * @param string 移動先ファイル名
	 * @param string 移動先ファイルパス
	 */
	public function move($directory, $filename);

}
