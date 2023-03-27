<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileHelper
{
    private SluggerInterface $slugger;

    private string $directory;

    public function __construct(SluggerInterface $slugger, string $directory) {
        $this->slugger = $slugger;
        $this->directory = $directory;
    }

    public function uploadFile (
        UploadedFile $file,
        string $fileName = null,
        string $subPath = null,
        string $imageToDelete = null
    ): string
    {
        $safeFilename = $fileName ? strtolower($this->slugger->slug($fileName)) : md5(uniqid());
        $newFilename = $safeFilename.'.'.$file->guessExtension();
        $directory = $this->directory;
        $directory .= $subPath ?? '';
        try {
            if ($imageToDelete) {
                $filePath = $subPath ? $subPath.'/'.$imageToDelete : $imageToDelete;
                $this->deleteFile($filePath);
            }
            $file->move(
                $directory,
                $newFilename
            );
        } catch (FileException $e) {
            throw new FileException($e->getMessage());
        }

        return $newFilename;
    }

    public function deleteFile (string $path): bool
    {
        $filePath = $this->directory.$path;
        if (!file_exists($filePath)) {
            return true;
        }

        try {
            unlink($filePath);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

}