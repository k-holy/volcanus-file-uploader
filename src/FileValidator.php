<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\FileUploader;

use Volcanus\FileUploader\File\FileInterface;
use Volcanus\FileUploader\Exception\FilenameException;
use Volcanus\FileUploader\Exception\NotFoundException;
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

    /**
     * @var array 設定オプション
     */
    private array $config;

    /**
     * @var array エラー
     */
    private array $errors;

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
     * @return static
     */
    public function initialize(array|\ArrayAccess $configurations = []): static
    {
        $this->config = [];
        $this->config['enableGmp'] = extension_loaded('gmp');
        $this->config['enableBcmath'] = extension_loaded('bcmath');
        $this->config['enableExif'] = extension_loaded('exif');
        $this->config['throwExceptionOnValidate'] = true;
        $this->config['allowableType'] = null;
        $this->config['filenameEncoding'] = null;
        $this->config['maxWidth'] = null;
        $this->config['maxHeight'] = null;
        $this->config['maxFilesize'] = null;
        if (!empty($configurations)) {
            foreach ($configurations as $name => $value) {
                $this->config($name, $value);
            }
        }
        $this->errors = [];
        return $this;
    }

    /**
     * 引数1の場合は指定された設定の値を返します。
     * 引数2の場合は指定された設置の値をセットして$thisを返します。
     *
     * @param string $name 設定名
     * @return mixed 設定値 または $this
     *
     * @throws \InvalidArgumentException 引数の指定が不正
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
                        case 'enableGmp':
                        case 'enableBcmath':
                        case 'enableExif':
                        case 'throwExceptionOnValidate':
                            if (!is_bool($value) && !is_int($value) && (!is_string($value) || !ctype_digit($value))) {
                                throw new \InvalidArgumentException(
                                    sprintf('The config parameter "%s" accepts boolean or numeric.', $name));
                            }
                            $value = (bool)$value;
                            break;
                        case 'allowableType':
                        case 'filenameEncoding':
                            if (!is_string($value)) {
                                throw new \InvalidArgumentException(
                                    sprintf('The config parameter "%s" only accepts string.', $name));
                            }
                            break;
                        case 'maxWidth':
                        case 'maxHeight':
                            if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
                                throw new \InvalidArgumentException(
                                    sprintf('The config parameter "%s" accepts numeric.', $name));
                            }
                            $value = (int)$value;
                            break;
                        // 数値または数値 + 単位(K|M|G|T|P|E|Z|Y)
                        case 'maxFilesize':
                            if (!is_int($value) && !preg_match('/\A(\d+)(K|M|G|T|P|E|Z|Y?)\z/i', $value)) {
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
     * 現在のエラーまたは指定された種別のエラーがあるかどうかを返します。
     *
     * @param string|null $name エラー種別 (例外のクラス名に対応) uploader | notFound | filesize | filename | filesize | extension | imageType | imageWidth | imageHeight
     * @return bool
     */
    public function hasError(string $name = null): bool
    {
        if ($name === null) {
            return (count($this->errors) > 0);
        }
        return array_key_exists($name, $this->errors);
    }

    /**
     * 現在のエラーをクリアします。
     *
     * @return self
     */
    public function clearErrors(): self
    {
        $this->errors = [];
        return $this;
    }

    /**
     * アップロードエラー定数を検証します。
     *
     * @param FileInterface $file アップロードファイル
     * @return bool 検証結果
     *
     * @throws FilesizeException ファイルサイズが上限値を超えている場合
     * @throws UploaderException その他クライアント側で回避不能なエラーの場合
     */
    public function validateUploadError(FileInterface $file): bool
    {
        $throwExceptionOnValidate = $this->config('throwExceptionOnValidate');
        switch ($file->getError()) {
            // エラーはなく、ファイルアップロードは成功しています。
            case \UPLOAD_ERR_OK:
                return true;
            // ファイルはアップロードされませんでした。
            case \UPLOAD_ERR_NO_FILE:
                $this->errors['notFound'] = 1;
                if ($throwExceptionOnValidate) {
                    throw new NotFoundException('No uploaded files.');
                }
                return false;
            // アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
            case \UPLOAD_ERR_INI_SIZE:
                $this->errors['filesize'] = 1;
                if ($throwExceptionOnValidate) {
                    throw new FilesizeException(
                        sprintf('The uploaded file is larger than upload_max_filesize:%d.', ini_get('upload_max_filesize'))
                    );
                }
                return false;
            // アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
            case \UPLOAD_ERR_FORM_SIZE:
                $this->errors['filesize'] = 1;
                if ($throwExceptionOnValidate) {
                    throw new FilesizeException('The uploaded file is larger than requested MAX_FILE_SIZE.');
                }
                return false;
            // アップロードされたファイルは一部のみしかアップロードされていません。
            case \UPLOAD_ERR_PARTIAL:
                // テンポラリフォルダがありません。
            case \UPLOAD_ERR_NO_TMP_DIR:
                // ディスクへの書き込みに失敗しました。
            case \UPLOAD_ERR_CANT_WRITE:
                // PHP の拡張モジュールがファイルのアップロードを中止しました。
            case \UPLOAD_ERR_EXTENSION:
            default:
                break;
        }
        $this->errors['uploader'] = 1;
        if ($throwExceptionOnValidate) {
            throw new UploaderException('The uploaded file is invalid for some reasons.');
        }
        return false;
    }

    /**
     * ファイル名が指定されたエンコーディングで有効かどうかを検証します。
     *
     * @param FileInterface $file アップロードファイル
     * @return bool|null 検証結果
     *
     * @throws FilenameException ファイル名が不正な場合
     */
    public function validateFilename(FileInterface $file): ?bool
    {
        $encoding = $this->config('filenameEncoding');
        if ($encoding === null) {
            return null;
        }
        $filename = $file->getClientFilename();
        if ($filename === null || strlen($filename) === 0) {
            return null;
        }
        if (mb_check_encoding($filename, $encoding)) {
            return true;
        }
        $this->errors['filename'] = $filename;
        if ($this->config('throwExceptionOnValidate')) {
            throw new FilenameException(
                sprintf('The filename is including invalid bytes for encoding:%s.', $encoding)
            );
        }
        return false;
    }

    /**
     * ファイルサイズが指定サイズ以内かどうかを検証します。
     * 整数値の範囲制限により、4GBを越える場合は検証できません。
     *
     * @param FileInterface $file アップロードファイル
     * @return bool|null 検証結果
     *
     * @throws \InvalidArgumentException ファイル最大値の指定が解析不能、またはファイルサイズの取得に失敗した場合
     * @throws FilesizeException ファイルサイズが上限値を超えている場合
     */
    public function validateFilesize(FileInterface $file): ?bool
    {
        $maxFilesize = $this->config('maxFilesize');
        if ($maxFilesize === null) {
            return null;
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
        if ($filesize === null || $filesize === 0) {
            return null;
        }
        if ($filesize < 0) {
            $filesize = sprintf('%u', $filesize);
        }
        if ($this->config('enableGmp')) {
            if (0 <= gmp_cmp(gmp_init($maxBytes, 10), gmp_init($filesize, 10))) {
                return true;
            }
        } elseif ($this->config('enableBcmath')) {
            if (0 <= bccomp($maxBytes, $filesize)) {
                return true;
            }
        } elseif ($filesize <= $maxBytes) {
            return true;
        }
        $this->errors['filesize'] = $filesize;
        if ($this->config('throwExceptionOnValidate')) {
            throw new FilesizeException(
                sprintf('The uploaded file\'s size %s bytes is larger than maxFilesize:"%s"', $filesize, $maxFilesize)
            );
        }
        return false;
    }

    /**
     * 拡張子が指定したファイル種別に含まれているかどうかを検証します。
     *
     * @param FileInterface $file アップロードファイル
     * @return bool|null 検証結果
     *
     * @throws ExtensionException 拡張子が許可する拡張子に一致しない場合
     */
    public function validateExtension(FileInterface $file): ?bool
    {
        $allowableType = $this->config('allowableType');
        if ($allowableType === null) {
            return null;
        }
        $extension = $file->getClientExtension();
        if ($extension === null || strlen($extension) === 0) {
            return null;
        }
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
        $this->errors['extension'] = $extension;
        if ($this->config('throwExceptionOnValidate')) {
            throw new ExtensionException(
                sprintf('The uploaded file\'s extension "%s" is not allowable', $extension)
            );
        }
        return false;
    }

    /**
     * 拡張子が指定したファイルの画像種別と一致するかどうかを検証します。
     *
     * @param FileInterface $file アップロードファイル
     * @return bool|null 検証結果
     *
     * @throws ImageTypeException 拡張子が内容と一致しない場合
     */
    public function validateImageType(FileInterface $file): ?bool
    {
        if (!$file->isImage()) {
            return null;
        }
        $extension = $file->getClientExtension();
        $mimeType = $file->getMimeType();
        $imageType = $this->getImageType($file);
        if (is_int($imageType)) {
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
        }
        $this->errors['imageType'] = $imageType;
        if ($this->config('throwExceptionOnValidate')) {
            throw new ImageTypeException(
                sprintf('The file extension "%s" does not match ImageType.', $extension)
            );
        }
        return false;
    }

    /**
     * 画像の横幅または高さが設定した最大値以下かどうかを検証します。
     *
     * @param FileInterface $file アップロードファイル
     * @return bool|null 検証結果
     *
     * @throws ImageWidthException 画像の横幅が最大値を越えている場合
     * @throws ImageHeightException 画像の高さが最大値を越えている場合
     * @throws \InvalidArgumentException アップロードファイルが画像ではない場合
     */
    public function validateImageSize(FileInterface $file): ?bool
    {
        if (!$file->isImage()) {
            return null;
        }
        $throwExceptionOnValidate = $this->config('throwExceptionOnValidate');
        $maxWidth = $this->config('maxWidth');
        $maxHeight = $this->config('maxHeight');
        if ($maxWidth === null && $maxHeight === null) {
            return null;
        }
        $imageInfo = $file->getImageInfo();
        if (is_array($imageInfo) && isset($imageInfo[0]) && isset($imageInfo[1])) {
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            if (!empty($maxWidth) && $width > $maxWidth) {
                $this->errors['imageWidth'] = $width;
                if ($throwExceptionOnValidate) {
                    throw new ImageWidthException(
                        sprintf('The image width %d pixels is larger than maxWidth:%d', $width, $maxWidth)
                    );
                }
            }
            if (!empty($maxHeight) && $height > $maxHeight) {
                $this->errors['imageHeight'] = $height;
                if ($throwExceptionOnValidate) {
                    throw new ImageHeightException(
                        sprintf('The image height %d pixels is larger than maxHeight:%d', $height, $maxHeight)
                    );
                }
            }
            return !(isset($this->errors['imageWidth']) || isset($this->errors['imageHeight']));
        }
        throw new \InvalidArgumentException(
            sprintf('The file is invalid image. path:%s', $file->getPath())
        );
    }

    /**
     * 指定されたファイルのImageType定数を返します。
     *
     * @param FileInterface $file アップロードファイル
     * @return int|false 定数値またはFALSE
     */
    private function getImageType(FileInterface $file): mixed
    {
        if ($this->config('enableExif')) {
            $filepath = $file->getPath();
            if ($filepath !== null) {
                return exif_imagetype($filepath);
            }
        }
        $imageInfo = $file->getImageInfo();
        if (is_array($imageInfo) && isset($imageInfo[2])) {
            return $imageInfo[2];
        }
        return false;
    }

    /**
     * 単位付きバイト数をバイト数に変換して返します。
     * 2GB以上を扱うにはGMP関数またはBCMath関数が有効になっている必要があります。
     *
     * @param string $data バイト数または単位付きバイト数(K,M,G,T,P,E,Z,Y)
     * @return mixed バイト数またはFALSE
     */
    private function convertToBytes(string $data): mixed
    {
        preg_match('/\A(\d+)(K|M|G|T|P|E|Z|Y?)\z/i', $data, $matches);
        if (!isset($matches[1])) {
            return false;
        }
        if (isset($matches[2])) {
            $index = array_search(sprintf('%sB', $matches[2]), ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']);
            if ($this->config('enableGmp')) {
                return gmp_strval(gmp_mul(gmp_init($matches[1], 10), gmp_pow(gmp_init(1024, 10), (int)$index)), 10);
            } elseif ($this->config('enableBcmath')) {
                return bcmul($matches[1], bcpow(1024, (int)$index));
            } else {
                return $matches[1] * pow(1024, (int)$index);
            }
        }
        return $matches[1];
    }

}
