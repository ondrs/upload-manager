<?php

namespace ondrs\UploadManager\DI;


use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use ondrs\UploadManager\Exception;

class Extension extends CompilerExtension
{

    /** @var array */
    private $defaults = [
        'basePath' => '%wwwDir%',
        'relativePath' => NULL,
        'imageManager' => [
            'basePath' => NULL,
            'relativePath' => NULL,
            'dimensions' => NULL,
            'maxSize' => NULL,
            'quality' => NULL,
            'type' => NULL,
        ],
        'fileManager' => [
            'basePath' => NULL,
            'relativePath' => NULL,
            'blacklist' => NULL,
        ],
    ];


    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        if ($config['relativePath'] === NULL) {
            throw new Exception('relativePath must be set');
        }

        $builder->addDefinition($this->prefix('imageManager'))
            ->setClass('ondrs\UploadManager\ImageManager', [
                $config['imageManager']['basePath'] ? $config['imageManager']['basePath'] : $config['basePath'],
                $config['imageManager']['relativePath'] ? $config['imageManager']['relativePath'] : $config['relativePath'],
                $config['imageManager']['dimensions'],
                $config['imageManager']['maxSize'],
                $config['imageManager']['quality'],
                $config['imageManager']['type'],
            ]);

        $builder->addDefinition($this->prefix('fileManager'))
            ->setClass('ondrs\UploadManager\FileManager', [
                $config['fileManager']['basePath'] ? $config['fileManager']['basePath'] : $config['basePath'],
                $config['fileManager']['relativePath'] ? $config['fileManager']['relativePath'] : $config['relativePath'],
                $config['fileManager']['blacklist'],
            ]);

        $builder->addDefinition($this->prefix('upload'))
            ->setClass('ondrs\UploadManager\Upload', [
                $builder->getDefinition($this->prefix('imageManager')),
                $builder->getDefinition($this->prefix('fileManager')),
            ]);

    }


    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Compiler $compiler) {
            $compiler->addExtension('UploadManager', new Extension());
        };
    }

}
