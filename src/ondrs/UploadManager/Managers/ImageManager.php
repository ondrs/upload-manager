<?php

namespace ondrs\UploadManager\Managers;

use Nette\Http\FileUpload;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use ondrs\UploadManager\ImageProcessor;
use ondrs\UploadManager\InvalidArgumentException;
use ondrs\UploadManager\Storages\IStorage;
use ondrs\UploadManager\Utils;
use SplFileInfo;

class ImageManager implements IManager
{

    use SmartObject;

    /** @var array */
    private static $methods = [
        'exact' => Image::EXACT,
        'fill' => Image::FILL,
        'fit' => Image::FIT,
        'shrink_only' => Image::SHRINK_ONLY,
        'stretch' => Image::STRETCH,
    ];

    /** @var array */
    private static $types = [
        self::TYPE_JPG => Image::JPEG,
        self::TYPE_PNG => Image::PNG,
        self::TYPE_GIF => Image::GIF,
    ];

    const TYPE_JPG = 'jpg';
    const TYPE_PNG = 'png';
    const TYPE_GIF = 'gif';

    /** @var IStorage */
    private $storage;

    /** @var ImageProcessor */
    private $imageProcessor;

    /** @var string */
    private $tempDir;

    /** @var array */
    private $maxSize = [1680, NULL];

    /** @var array */
    private $dimensions = [
        800 => [
            [800, NULL],
            Image::SHRINK_ONLY,
        ],
        250 => [
            [250, NULL],
            Image::SHRINK_ONLY,
        ],
    ];

    /** @var NULL|int */
    private $quality = NULL;

    /** @var NULL|string */
    private $type = NULL;

    /** @var NULL|string */
    private $suffix = NULL;

    /** @var bool */
    private $saveOriginal = FALSE;


    /**
     * @param IStorage $storage
     * @param ImageProcessor $imageProcessor
     * @param string $tempDir
     * @param NULL|array $dimensions
     * @param NULL|array|int $maxSize
     * @param NULL|int $quality
     * @param NULL|string $type
     */
    public function __construct(IStorage $storage, ImageProcessor $imageProcessor, string $tempDir, $dimensions = NULL, $maxSize = NULL, $quality = NULL, $type = NULL)
    {
        $this->storage = $storage;
        $this->imageProcessor = $imageProcessor;
        $this->tempDir = $tempDir . '/' . uniqid('ImageManager', FALSE);

        if ($dimensions !== NULL) {
            $this->setDimensions($dimensions);
        }

        if ($maxSize !== NULL) {
            $this->setMaxSize($maxSize);
        }

        if ($quality !== NULL) {
            $this->quality = $quality;
        }

        if ($type !== NULL) {
            $this->setType($type);
        }
    }


    public function setDimensions(array $dimensions): void
    {
        $this->dimensions = array_map(function ($i) {

            $i[1] = isset($i[1]) && isset(self::$methods[$i[1]])
                ? self::$methods[$i[1]]
                : Image::SHRINK_ONLY;

            return $i;
        }, $dimensions);
    }


    public function getDimensions(): array
    {
        return $this->dimensions;
    }


    /**
     * @param array|int $maxSize
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = is_array($maxSize) ? $maxSize : [$maxSize, NULL];
    }


    public function getMaxSize(): array
    {
        return $this->maxSize;
    }


    public function getQuality(): ?int
    {
        return $this->quality;
    }


    public function setQuality(int $quality): void
    {
        if ($quality >= 0 && $quality <= 100) {
            $this->quality = $quality;
        }
    }


    /**
     * @param bool $yes
     */
    public function saveOriginal($yes = TRUE): void
    {
        $this->saveOriginal = (bool)$yes;
    }


    /**
     * @return NULL|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }


    public function setType(string $type): ?string
    {
        if (isset(self::$types[$type])) {
            $this->type = self::$types[$type];
            $this->suffix = $type;
        }
    }


    public function getTempDir(): string
    {
        return $this->tempDir;
    }


    public function getStorage(): IStorage
    {
        return $this->storage;
    }


    /**
     * @param string $namespace
     * @param FileUpload $fileUpload
     * @return SplFileInfo
     * @throws \Nette\NotSupportedException
     * @throws \Nette\IOException
     * @throws \ondrs\UploadManager\InvalidArgumentException
     * @throws \Nette\Utils\ImageException
     */
    public function upload(string $namespace, FileUpload $fileUpload): SplFileInfo
    {
        if (!$fileUpload->isImage()) {
            throw new InvalidArgumentException('This is not an image!');
        }

        if (!is_dir($this->tempDir)) {
            Utils::makeDirectoryRecursive($this->tempDir);
        }

        $filename = Utils::sanitizeFileName($fileUpload);
        $suffix = Utils::getSuffix($filename);

        if ($this->type !== NULL) {
            $filename = str_replace(".$suffix", ".$this->suffix", $filename);
        }

        $image = $this->imageProcessor->process($fileUpload);

        if ($this->saveOriginal) {
            $image->save($this->tempDir . '/orig_' . $filename);

            $savedOriginal = [
                "$this->tempDir/orig_$filename",
                "$namespace/orig_$filename",
            ];
        }

        $image->resize($this->maxSize[0], $this->maxSize[1], Image::SHRINK_ONLY);
        $image->save("$this->tempDir/$filename", $this->quality, $this->type);

        $filesToSave = [];

        // has to be first
        $filesToSave[] = [
            "$this->tempDir/$filename",
            "$namespace/$filename",
        ];

        // intently saved at the second position
        if (isset($savedOriginal)) {
            $filesToSave[] = $savedOriginal;
        }

        foreach ($this->dimensions as $prefix => $p) {
            $image->resize($p[0][0], $p[0][1], $p[1]);
            $image->save("$this->tempDir/{$prefix}_{$filename}", $this->quality, $this->type);

            $filesToSave[] = [
                "$this->tempDir/{$prefix}_{$filename}",
                "$namespace/{$prefix}_{$filename}",
            ];
        }

        $results = $this->storage->bulkSave($filesToSave);

        // cleanup temp files
        foreach ($filesToSave as $file) {
            if (is_file($file[0])) {
                FileSystem::delete($file[0]);
            }
        }

        // remove complete directory
        if (is_dir($this->tempDir)) {
            FileSystem::delete($this->tempDir);
        }

        return new SplFileInfo($results[0]);
    }


    public function delete(string $namespace, string $filename): void
    {
        $filter = array_keys($this->dimensions);

        if ($this->saveOriginal) {
            $filter[] = 'orig';
        }

        $filter = array_map(function ($i) use ($filename) {
            return $i . '_' . $filename;
        }, $filter);

        $filter[] = $filename;

        $files = [];

        /** @var SplFileInfo $file */
        foreach ($this->storage->find($namespace, $filter) as $file) {
            $files[] = Utils::normalizePath($namespace . '/' . $file->getFilename());
        }

        $this->storage->bulkDelete($files);
    }
}
