<?php

namespace ondrs\Uploader\DI;


use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class UploaderExtension extends CompilerExtension
{

    /** @var array */
    private $defaults = [
        'basePath' => '%wwwDir%',
        '@httpRequest',
    ];


    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('imageManager'))
            ->setClass('ondrs\Uploader\ImageManager', [
                $builder->getDefinition('basePath'),
                $builder->getDefinition('relativePath'),
                $builder->getDefinition('dimensions'),
                $builder->getDefinition('maxSize'),
            ]);

        $builder->addDefinition($this->prefix('fileManager'))
            ->setClass('ondrs\Uploader\ImageManager', [
                $builder->getDefinition('basePath'),
                $builder->getDefinition('relativePath'),
                $builder->getDefinition('blacklist'),
            ]);

        $builder->addDefinition($this->prefix('upload'))
            ->setClass('ondrs\Uploader\Upload', [
                $builder->getDefinition('@httpRequest'),
                $builder->getByType('ondrs\Uploader\ImageManager'),
                $builder->getByType('ondrs\Uploader\FileManager'),
            ]);

    }


    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Compiler $compiler) {
            $compiler->addExtension('uploader', new UploaderExtension());
        };
    }

}
