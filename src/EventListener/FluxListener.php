<?php

namespace App\EventListener;

use App\Entity\Flux;
use App\Helper\FileHelper;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class FluxListener
{
    private FileHelper $fileHelper;
    public function __construct(FileHelper $fileHelper) {
        $this->fileHelper = $fileHelper;
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Flux) {
            return;
        }
        if ($entity->getFile()) {
            $this->fileUpload($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Flux) {
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
        if (!$entity instanceof Flux) {
            return;
        }

        if ($entity->getImage()) {
            $this->fileHelper->deleteFile('logos/'.$entity->getImage());
        }
    }

    private function fileUpload(Flux $flux): void
    {
        $fileName = $this->fileHelper->uploadFile(
            $flux->getFile(),
            $flux->getName(),
            'logos',
            $flux->getOldImage()
        );
        $flux->setImage($fileName);
    }
}