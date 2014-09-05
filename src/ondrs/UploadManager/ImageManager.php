<?php
/**
 * Created by PhpStorm.
 * User: Ondra
 * Date: 8.8.14
 * Time: 19:10
 */

namespace ondrs\UploadManager;


use Nette\Http\FileUpload;
use Nette\Object;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Image;

class ImageManager extends Object implements IUploadManager
{

    /** @var array */
    private static $method = [
        'exact' => Image::EXACT,
        'fill' => Image::FILL,
        'fit' => Image::FIT,
        'shrink_only' => Image::SHRINK_ONLY,
        'stretch' => Image::STRETCH,
    ];

    /** @var array */
    private $maxSize = [1280, NULL];

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

    /** @var string */
    private $basePath;

    /** @var string */
    private $relativePath;


    /**
     * @param $basePath
     * @param $relativePath
     * @param null|array $dimensions
     * @param null|array|string $maxSize
     * @throws InvalidArgumentException
     */
    public function __construct($basePath, $relativePath, $dimensions = NULL, $maxSize = NULL)
    {
        $this->basePath = $basePath;
        $this->relativePath = $relativePath;

        if ($dimensions !== NULL) {
            $this->setDimensions($dimensions);
        }

        if ($maxSize !== NULL) {
            $this->setMaxSize($maxSize);
        }
    }

    /**
     * @param array $dimensions
     */
    public function setDimensions(array $dimensions)
    {
        $this->dimensions = array_map(function ($i) {

            $i[1] = isset($i[1]) && isset(self::$method[$i[1]])
                ? self::$method[$i[1]]
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
     * @param FileUpload $fileUpload
     * @param null $dir
     * @return \SplFileInfo
     * @throws InvalidArgumentException
     */
    public function upload(FileUpload $fileUpload, $dir = NULL)
    {
        if(!$fileUpload->isImage()) {
            throw new InvalidArgumentException('This is not an image!');
        }

        $path = $this->basePath . '/' . $this->relativePath;

        if ($dir !== NULL) {
            $path .= '/' . $dir;
        }

        $path = Utils::normalizePath($path);
        Utils::makeDirectoryRecursive($path);

        $filename = $fileUpload->getSanitizedName();
        $filename = strtolower($filename);

        /** @var \Nette\Utils\Image */
        $image = $fileUpload->toImage();
        $image->resize($this->maxSize[0], $this->maxSize[1], Image::SHRINK_ONLY);
        $image->save($path . '/' . $filename);


        foreach ($this->dimensions as $prefix => $p) {

            $image->resize($p[0][0], $p[0][1], $p[1]);
            $image->save($path . '/' . $prefix . '_' . $filename);
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

        foreach (Finder::findFiles($filter)->in($dir) as $filePath => $file) {
            FileSystem::delete($filePath);
        }
    }
}
