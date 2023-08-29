<?php declare(strict_types=1);

/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Massolle <bm@presentprogressive.de>
 */

namespace PresProg\ResizeOnUpload\EventListener;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FilesModel;
use Model\Collection;
use PresProg\ResizeOnUpload\ImageResizer;

final class FileUploadListener
{
    private ImageResizer $imageResizer;

    private Adapter $filesModel;

    public function __construct(ContaoFramework $framework, ImageResizer $imageResizer)
    {
        $this->imageResizer = $imageResizer;
        $this->filesModel   = $framework->getAdapter(FilesModel::class);
    }

    /**
     * Check upload folder or parent folders for defined image sizes and create thumbnails accordingly.
     *
     * @Hook("postUpload")
     */
    public function resizeOnUpload(array $arrUploaded): void
    {
        // Get image sizes for folder the user uploaded files to
        $target = $this->filesModel->findByPath(\dirname($arrUploaded[0]));

        if (!$target) {
            return;
        }

        $imageSizes = $this->imageResizer->getImageSizesForFolder($target->path);

        // Return early if no image sizes where found
        if (empty($imageSizes)) {
            return;
        }

        /** @var Collection $uploads */
        $uploads = $this->filesModel->findMultipleByPaths($arrUploaded);

        foreach ($uploads as $upload) {
            $this->imageResizer->resizeImage($upload, $imageSizes);
        }
    }
}
