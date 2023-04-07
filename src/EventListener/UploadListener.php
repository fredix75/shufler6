<?php

namespace App\EventListener;

use App\Contract\UploadInterface;
use App\Helper\FileHelper;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class UploadListener
{
    private FileHelper $fileHelper;

    public function __construct(FileHelper $fileHelper) {
        $this->fileHelper = $fileHelper;
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof UploadInterface) {
            return;
        }
        if ($entity->getFile()) {
            $this->fileUpload($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof UploadInterface) {
            return;
        }

        if ($entity->getFile()) {
            $this->fileUpload($entity);
        }
        if (!$entity->getImage()) {
            $this->fileHelper->deleteFile('logos/'.$entity->getOldImage());
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof UploadInterface) {
            return;
        }

        if ($entity->getImage()) {
            $this->fileHelper->deleteFile('logos/'.$entity->getImage());
        }
    }

    private function fileUpload(UploadInterface $entity): void
    {
        $fileName = $this->fileHelper->uploadFile(
            $entity->getFile(),
            $entity->getName(),
            'logos',
            $entity->getOldImage()
        );
        $entity->setImage($fileName);
    }

}