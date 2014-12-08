<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader\Uploader;

use Volcanus\FileUploader\FileValidatorInterface;

/**
 * アップローダインタフェース
 *
 * @author k.holy74@gmail.com
 */
interface UploaderInterface
{

	/**
	 * アップロードファイルのクライアントファイル名を返します。
	 *
	 * @return mixed クライアントファイル名
	 */
	public function getClientFilename();

	/**
	 * アップロードファイルを検証します。
	 *
	 * @param Volcanus\FileUploader\FileValidatorInterface
	 *
	 * @throws Volcanus\FileUploader\Exception\FilesizeException ファイルサイズが設定値を超えている場合
	 * @throws Volcanus\FileUploader\Exception\FilenameException 設定されたエンコーディングに存在しない文字がファイル名に含まれている場合
	 * @throws Volcanus\FileUploader\Exception\FiletypeException ファイルの拡張子が設定された拡張子の許可リストに一致しない場合
	 * @throws Volcanus\FileUploader\Exception\ImagetypeException 画像ファイルの拡張子がファイルの内容と一致しない場合
	 * @throws Volcanus\FileUploader\Exception\UploaderException その他何らかの理由でアップロードが受け付けられない場合
	 */
	public function validate(FileValidatorInterface $validator);

	/**
	 * アップロードファイルを移動し、移動したファイルのパスを返します。
	 *
	 * @param array | ArrayAccess 設定
	 * @return string 移動したファイルのパス
	 *
	 * @throws Volcanus\FileUploader\Exception\UploaderException アップロードファイルの移動に失敗した場合
	 */
	public function move($options);

}
