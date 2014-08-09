<?php

namespace ondrs\Uploader\DI;


use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use ondrs\Uploader\Exception;

class UploaderExtension extends CompilerExtension
{

    /** @var array */
    private $defaults = [
        'basePath' => '%wwwDir%',
        'relativePath' => NULL,
        'dimensions' => NULL,
        'maxSize' => NULL,
        'blacklist' => NULL,
        '@httpRequest',
    ];


    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        if($config['relativePath'] === NULL) {
            throw new Exception('reletivePath must be set');
        }

        $builder->addDefinition($this->prefix('imageManager'))
            ->setClass('ondrs\Uploader\ImageManager', [
                $config['basePath'],
                $config['relativePath'],
                $config['dimensions'],
                $config['maxSize'],
            ]);

        $builder->addDefinition($this->prefix('fileManager'))
            ->setClass('ondrs\Uploader\ImageManager', [
                $config['basePath'],
                $config['relativePath'],
                $config['blacklist'],
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
