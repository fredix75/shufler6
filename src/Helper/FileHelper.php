<?php

namespace App\Helper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileHelper
{
    private Filesystem $filesystem;

    private SluggerInterface $slugger;

    private string $directory;

    public function __construct(
        Filesystem $fileSystem,
        SluggerInterface $slugger,
        ParameterBagInterface $parameterBag
    )
    {
        $this->filesystem = $fileSystem;
        $this->slugger = $slugger;
        $this->directory = $parameterBag->get('uploads');
    }

    /**
     * @throws \Exception
     */
    public function copyFileFromUrl(
        string $filePath,
        string $subPath,
        string $newName = "",
        string $url = ""
    ): string
    {
        $fileUrl = $filePath;
        if (!$this->filesystem->isAbsolutePath($filePath)) {
            if (!$url) {
                throw new \Exception('You have to give a root URl to create a valid path');
            }
            $fileUrl = $url.$filePath;
        }
        $fileUrl = preg_replace('/\s/', '%20', $fileUrl);
        $directory = sprintf('%s%s', $this->directory, $subPath);
        $this->filesystem->copy($fileUrl, sprintf('%s/%s',$directory, 'tmp'));
        $file = new File(sprintf('%s/%s',$directory, 'tmp'));
        $newFile = $file->move($directory, $newName ? sprintf('%s.%s', $newName, $file->guessExtension()) : basename($fileUrl));

        return $subPath.'/'.$newFile->getBasename();
    }

    public function uploadFile(
        UploadedFile $file,
        string $fileName = null,
        string $subPath = null,
        string $imageToDelete = null
    ): string
    {
        $safeFilename = $fileName ? strtolower($this->slugger->slug($fileName)) : md5(uniqid());
        $newFilename = sprintf('%s.%s', $safeFilename, $file->guessExtension());
        $directory = $this->directory;
        $directory .= $subPath ?? '';
        try {
            if ($imageToDelete) {
                $filePath = $subPath ? sprintf('%s/%s', $subPath, $imageToDelete) : $imageToDelete;
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

    public function deleteFile(string $path): bool
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