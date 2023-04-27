<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader;

use Volcanus\FileUploader\Exception\ExtensionException;
use Volcanus\FileUploader\Exception\FilenameException;
use Volcanus\FileUploader\Exception\FilesizeException;
use Volcanus\FileUploader\Exception\ImageTypeException;
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
    private array $config;

    /**
     * コンストラクタ
     *
     * @param array|\ArrayAccess $configurations 設定オプション
     */
    public function __construct(array|\ArrayAccess $configurations = [])
    {
        $this->initialize($configurations);
    }

    /**
     * オブジェクトを初期化します。
     *
     * @param array|\ArrayAccess $configurations 設定オプション
     * @return self
     */
    public function initialize(array|\ArrayAccess $configurations = []): self
    {
        $this->config = [];
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
     * @param string $name 設定名
     * @return mixed 設定値 または $this
     */
    public function config(string $name): mixed
    {
        switch (func_num_args()) {
            case 1:
                return $this->config[$name];
            case 2:
                $value = func_get_arg(1);
                if (isset($value)) {
                    switch ($name) {
                        case 'moveRetry':
                            if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
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
     * @param FileInterface $file アップロードファイル
     * @param FileValidator $validator アップロードファイルバリデータ
     * @return bool
     *
     * @throws FilesizeException ファイルサイズが設定値を超えている場合
     * @throws FilenameException 設定されたエンコーディングに存在しない文字がファイル名に含まれている場合
     * @throws ExtensionException ファイルの拡張子が設定された拡張子の許可リストに一致しない場合
     * @throws ImageTypeException 画像ファイルの拡張子がファイルの内容と一致しない場合
     * @throws UploaderException その他何らかの理由でアップロードが受け付けられない場合
     */
    public function validate(FileInterface $file, FileValidator $validator): bool
    {

        $validator->clearErrors();

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

        return !$validator->hasError();
    }

    /**
     * アップロードファイルを移動し、移動先のファイルパスを返します。
     *
     * @param FileInterface $file アップロードファイル
     * @return string 移動先のファイルパス
     *
     * @throws UploaderException アップロードファイルの移動に失敗した場合
     */
    public function move(FileInterface $file): string
    {
        $moveDirectory = (string)$this->config('moveDirectory');
        if (!isset($moveDirectory) || strlen($moveDirectory) === 0) {
            throw new UploaderException('moveDirectory is not specified.');
        }
        $moveRetry = (int)$this->config('moveRetry');
        if (!isset($moveRetry) || $moveRetry === 0) {
            throw new UploaderException('moveRetry is not specified.');
        }
        if ($file->isValid()) {
            $this->prepareMove($moveDirectory);
            $extension = $file->getClientExtension();
            while ($moveRetry > 0) {
                $filename = sha1(uniqid(mt_rand(), true));
                if (strlen($extension) >= 1) {
                    $filename .= '.' . $extension;
                }
                try {
                    return $file->move($moveDirectory, $filename);
                } catch (FilepathException) {
                }
                $moveRetry--;
            }
        }
        throw new UploaderException(
            sprintf('A temporary file was not able to be created in %s.', $moveDirectory)
        );
    }

    /**
     * @param string $directory
     * @return void
     */
    private function prepareMove(string $directory): void
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
