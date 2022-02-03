<?php

namespace App\Service;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class UploadHelper
{
    const DEFAULT_IMAGE = 'assets/images/default.png';
    const DEFAULT_PROFILE_IMAGE = 'assets/images/default_profile.jpg';

    const BASE_PATH = 'uploads/';
    private string $publicPath;

    public function __construct(string $publicPath)
    {
        $this->publicPath = $publicPath;
    }

    public function uploadImg($array, $entityName = '')
    {
        $destination = $this->publicPath . '/public/uploads/' . $entityName;


        if (is_array($array)) {
            $filenameArray = [];

            foreach ($array as $image) {
                $originalFilename = $image->getClientOriginalName();
                $baseFileName = pathinfo($originalFilename, PATHINFO_FILENAME);

                $filename = Urlizer::urlize($baseFileName) . '-' . uniqid() . '.' . $image->guessExtension();

                $image->move($destination, $filename);
                $filenameArray[] = $filename;
            }

            return implode(',', $filenameArray);
        }


        $originalFilename = $array->getClientOriginalName();
        $baseFileName = pathinfo($originalFilename, PATHINFO_FILENAME);

        $filename = Urlizer::urlize($baseFileName) . '-' . uniqid() . '.' . $array->guessExtension();

        $array->move($destination, $filename);
        $filenameArray[] = $filename;


        return implode(',', $filenameArray);

    }

    public function fixtureUpload(File $file, $entityName)
    {
        $destination = $this->publicPath . '/public/uploads/' . $entityName;
        $originalFilename = $file->getFilename();
        $baseFileName = pathinfo($originalFilename, PATHINFO_FILENAME);
        $filename = Urlizer::urlize($baseFileName) . '-' . uniqid() . '.' . $file->guessExtension();

        $fs = new Filesystem();
        $fs->copy($file->getRealPath(), $destination . '/' . $filename, true);

        return $filename;
    }
}