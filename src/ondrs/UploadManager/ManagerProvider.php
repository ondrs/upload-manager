<?php

namespace ondrs\UploadManager;

use Nette\Http\FileUpload;
use ondrs\UploadManager\Managers\FileManager;
use ondrs\UploadManager\Managers\ImageManager;
use ondrs\UploadManager\Managers\IManager;

class ManagerProvider
{

    /** @var ManagerContainer $managerContainer */
    private $managerContainer;

    /** @var callable[] */
    private $rules = [];


    public function __construct(ManagerContainer $managerContainer, array $rules = [])
    {
        $this->managerContainer = $managerContainer;
        $this->rules = count($rules) ? $rules : self::getBasicRules();
    }


    /**
     * @param FileUpload $fileUpload
     * @return IManager
     */
    public function get(FileUpload $fileUpload)
    {
        foreach ($this->rules as $rule) {
            $result = $rule($fileUpload, $this->managerContainer);

            if ($result instanceof IManager) {
                return $result;
            }
        }

        throw new InvalidArgumentException("No rule matches {$fileUpload->getName()}");
    }


    /**
     * @return callable[]
     */
    public static function getBasicRules()
    {
        return [
            function (FileUpload $fileUpload, ManagerContainer $managerContainer) {

                if ($fileUpload->isImage()) {
                    return $managerContainer->get(ImageManager::class);
                }

                return $managerContainer->get(FileManager::class);
            },
        ];
    }

}
