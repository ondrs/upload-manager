<?php

namespace ondrs\UploadManager;


use Nette\Http\FileUpload;
use Nette\Object;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Image;

class ImageManager extends Object implements IUploadManager
{

    /** @var array */
    private static $methods = [
        'exact' => Image::EXACT,
        'fill' => Image::FILL,
        'fit' => Image::FIT,
        'shrink_only' => Image::SHRINK_ONLY,
        'stretch' => Image::STRETCH,
    ];

    private static $types = [
        self::TYPE_JPG => Image::JPEG,
        self::TYPE_PNG => Image::PNG,
        self::TYPE_GIF => Image::GIF,
    ];

    const TYPE_JPG = 'jpg';
    const TYPE_PNG = 'png';
    const TYPE_GIF = 'gif';

    /** @var array */
    private $maxSize = [1680, NULL];

    /** @var array */
    private $dimensions = [
        800 => [
            [800, NULL],
            Image::SHRINK_ONLY
        ],
        250 => [
            [250, NULL],
            Image::SHRINK_ONLY
        ]
    ];

    /** @var NULL|int */
    private $quality = NULL;

    /** @var NULL|string */
    private $type = NULL;

    /** @var NULL|string */
    private $suffix = NULL;

    /** @var string */
    private $basePath;

    /** @var string */
    private $relativePath;


    /**
     * @param $basePath
     * @param $relativePath
     * @param null|array $dimensions
     * @param null|array|string $maxSize
     * @param null|int $quality
     * @param null|string $type
     * @throws InvalidArgumentException
     */
    public function __construct($basePath, $relativePath, $dimensions = NULL, $maxSize = NULL, $quality = NULL, $type = NULL)
    {
        $this->basePath = $basePath;
        $this->relativePath = $relativePath;

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

    /**
     * @param array $dimensions
     */
    public function setDimensions(array $dimensions)
    {
        $this->dimensions = array_map(function ($i) {

            $i[1] = isset($i[1]) && isset(self::$methods[$i[1]])
                ? self::$methods[$i[1]]
                : Image::SHRINK_ONLY;

            return $i;
        }, $dimensions);
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param array|string $maxSize
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = is_array($maxSize) ? $maxSize : [$maxSize, NULL];
    }

    /**
     * @return array
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * @return int|NULL
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @param $quality
     * @return int|NULL
     */
    public function setQuality($quality)
    {
        if ($quality >= 0 && $quality <= 100) {
            $this->quality = $quality;
        }
    }


    /**
     * @return NULL|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return NULL|string
     */
    public function setType($type)
    {
        if (isset(self::$types[$type])) {
            $this->type = self::$types[$type];
            $this->suffix = $type;
        }
    }


    /**
     * @param FileUpload $fileUpload
     * @param null $dir
     * @return \SplFileInfo
     * @throws InvalidArgumentException
     */
    public function upload(FileUpload $fileUpload, $dir = NULL)
    {
        if (!$fileUpload->isImage()) {
            throw new InvalidArgumentException('This is not an image!');
        }

        $path = $this->basePath . '/' . $this->relativePath;

        if ($dir !== NULL) {
            $path .= '/' . $dir;
        }

        $path = Utils::normalizePath($path);
        Utils::makeDirectoryRecursive($path);

        $filename = Utils::sanitizeFileName($fileUpload);

        /** @var \Nette\Utils\Image */
        $image = $fileUpload->toImage();
        $image->save($path . '/orig_' . $filename);

        if($this->type !== NULL) {
            $filename = str_replace('.' . Utils::getSuffix($filename), '.' . $this->suffix, $filename);
        }


        $image->resize($this->maxSize[0], $this->maxSize[1], Image::SHRINK_ONLY);
        $image->save($path . '/' . $filename, $this->quality, $this->type);


        foreach ($this->dimensions as $prefix => $p) {

            $image->resize($p[0][0], $p[0][1], $p[1]);
            $image->save($path . '/' . $prefix . '_' . $filename, $this->quality, $this->type);
        }

        return new \SplFileInfo($filename);
    }


    /**
     * @param $dir
     * @param $filename
     * @return mixed|void
     */
    public function delete($dir, $filename)
    {
        $filter = array_keys($this->dimensions);

        $filter = array_map(function ($i) use ($filename) {
            return $i . '_' . $filename;
        }, $filter);

        $filter[] = $filename;

        $dir = $this->getBasePath() . '/' . $this->getRelativePath() . '/' . $dir;
        $dir = Utils::normalizePath($dir);

        if (!is_dir($dir)) {
            return;
        }

        foreach (Finder::findFiles($filter)->in($dir) as $filePath => $file) {
            FileSystem::delete($filePath);
        }
    }
}
