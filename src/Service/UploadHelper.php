<?php

namespace App\Service;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadHelper
{
    const DEFAULT_IMAGE = 'assets/images/default.png';
    const BASE_PATH = 'uploads/';
    private string $publicPath;

    public function __construct(string $publicPath)
    {
        $this->publicPath = $publicPath;
    }

    public function uploadImg(UploadedFile $uploadedFile, $entityName = '') {
        $destination = $this->publicPath . '/public/uploads/' . $entityName;

        $originalFilename = $uploadedFile->getClientOriginalName();
        $baseFileName = pathinfo($originalFilename,PATHINFO_FILENAME);

        $filename = Urlizer::urlize($baseFileName) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

        $uploadedFile->move($destination,$filename);

        return $filename;
    }

    public function fixtureUpload(File $file, $entityName) {
        $destination = $this->publicPath . '/public/uploads/' . $entityName;
        $originalFilename = $file->getFilename();
        $baseFileName = pathinfo($originalFilename,PATHINFO_FILENAME);
        $filename = Urlizer::urlize($baseFileName) . '-' . uniqid() . '.' . $file->guessExtension();

        $fs = new Filesystem();
        $fs->copy($file->getRealPath(), $destination . '/' . $filename, true);

        return $filename;
    }

    public function getUploadPath($entity = '') {
        return self::BASE_PATH . $entity;
    }

}