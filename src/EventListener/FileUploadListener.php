<?php
/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Zinke <bz@presentprogressive.de>
 */

declare(strict_types=1);

namespace PresProg\ContaoResizeOnUploadBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Model\Collection;
use PresProg\ContaoResizeOnUploadBundle\ImageResizer;

/**
 * Class FileUploadListener
 * @package PresProg\ContaoResizeOnUploadBundle\EventListener
 */
class FileUploadListener
{
    /** @var ImageResizer */
    private $imageResizer;

    /** @var ContaoFrameworkInterface */
    private $framework;

    /** @var FilesModel */
    private $filesModel;

    public function __construct(ContaoFrameworkInterface $framework, ImageResizer $imageResizer)
    {
        $this->framework = $framework;
        $this->imageResizer = $imageResizer;
        $this->filesModel = $this->framework->getAdapter(FilesModel::class);
    }

    /**
     * Check upload folder or parent folders for defined image sizes and create thumbnails accordingly.
     * @param array $arrUploaded Array of paths to uploaded files
     * @return void
     */
    public function resizeOnUpload(array $arrUploaded)
    {
        // Get image sizes for folder the user uploaded files to
        $objTargetFolder = $this->filesModel->findByPath(dirname($arrUploaded[0]));
        $imageSizes = $this->imageResizer->getImageSizesForFolder($objTargetFolder->path);

        // Return early if no image sizes where found
        if (empty($imageSizes)) {
            return;
        }

        /** @var Collection $objUploads */
        $objUploads = $this->filesModel->findMultipleByPaths($arrUploaded);

        foreach ($objUploads as $objUpload) {
            $this->imageResizer->resizeImage($objUpload, $imageSizes);
        }
    }
}