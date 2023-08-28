<?php declare(strict_types=1);

/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Massolle <bm@presentprogressive.de>
 */

namespace PresProg\ContaoResizeOnUploadBundle;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Contao\FilesModel;
use Contao\Model\Collection;
use Contao\StringUtil;
use Psr\Log\LoggerInterface;

class ImageResizer
{
    private Adapter $filesModel;

    private ImageFactoryInterface $imageFactory;

    private PictureFactoryInterface $pictureFactory;

    private array $validImageExtensions;

    private LoggerInterface $logger;

    private array $imageSizes = [];

    public function __construct(ContaoFramework $framework, ImageFactoryInterface $imageFactory, PictureFactoryInterface $pictureFactory, LoggerInterface $logger, array $validImageExtensions)
    {

        $this->imageFactory         = $imageFactory;
        $this->pictureFactory       = $pictureFactory;
        $this->logger               = $logger;
        $this->filesModel           = $framework->getAdapter(FilesModel::class);
        $this->validImageExtensions = $validImageExtensions;
    }

    /**
     * Overwrite default logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Resize a file by the given image sizes.
     */
    public function resizeImage(FilesModel $file, array $imageSizes): void
    {
        // skip non-image files
        if (!$this->isImage($file)) {
            return;
        }

        foreach ($imageSizes as $size) {
            try {
                $this->imageFactory->create(TL_ROOT . '/' . $file->path, $size)->getUrl(TL_ROOT);
                $this->pictureFactory->create(TL_ROOT . '/' . $file->path, $size);
                $this->logger->notice('Created thumb (ID ' . $size . ') for image ' . $file->path);
            } catch (\Exception $e) {
                $this->logger->error('Image "' . $file->path . '" could not be processed: ' . $e->getMessage());
            }
        }
    }

    public function getAllFoldersWithImageSizes(): ?Collection
    {
        return $this->filesModel->findBy(['type = ?', 'sizes IS NOT NULL'], ['folder'], ['return' => 'Collection']);
    }

    public function getImageSizesForFolder(string $path): array
    {
        // Fetch image sizes if not cached already
        if (empty($this->imageSizes)) {
            $this->fetchImageSizes();
        }

        $sizes = [];

        while ($path !== '.') {
            if (isset($this->imageSizes[$path])) {
                $sizes = array_merge($this->imageSizes[$path], $sizes);
            }

            $path = dirname($path);
        }

        return $sizes;
    }

    /**
     * Fetch folders with pre-defined image sizes from DBAFS
     */
    private function fetchImageSizes(): void
    {
        if (!empty($this->imageSizes)) {
            return;
        }

        $folders = $this->getAllFoldersWithImageSizes();

        foreach ($folders as $folder) {
            $this->imageSizes[$folder->path] = StringUtil::deserialize($folder->sizes, true);
        }
    }

    /**
     * Check if the given file object is an image.
     */
    private function isImage(FilesModel $file): bool
    {
        return (in_array($file->extension, $this->validImageExtensions, true));
    }
}
