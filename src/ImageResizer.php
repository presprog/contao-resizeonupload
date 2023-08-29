<?php declare(strict_types=1);

/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Massolle <bm@presentprogressive.de>
 */

namespace PresProg\ResizeOnUpload;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\LegacyResizer;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Contao\FilesModel;
use Contao\Image\DeferredImageInterface;
use Contao\Model\Collection;
use Contao\StringUtil;
use Psr\Log\LoggerInterface;

final class ImageResizer
{
    private array $imageSizes = [];

    private PictureFactoryInterface $pictureFactory;

    private LegacyResizer $resizer;

    private LoggerInterface $logger;

    private array $validImageExtensions;

    private string $projectDir;

    private Adapter $filesModel;

    public function __construct(ContaoFramework $framework, PictureFactoryInterface $pictureFactory, LegacyResizer $resizer, LoggerInterface $logger, array $validImageExtensions, string $projectDir)
    {
        $this->pictureFactory       = $pictureFactory;
        $this->resizer              = $resizer;
        $this->logger               = $logger;
        $this->validImageExtensions = $validImageExtensions;
        $this->projectDir           = $projectDir;
        $this->filesModel           = $framework->getAdapter(FilesModel::class);
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
                $this->executeResize($file, $size);
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


    private function executeResize(FilesModel $file, $size): void
    {
        $picture = $this->pictureFactory->create($this->projectDir . '/' . $file->path, $size);

        $img    = $picture->getRawImg();
        $srcset = array_map(static function ($img) {
            return $img[0];
        }, $img['srcset']);

        foreach ([$img['src'], ...$srcset] as $image) {
            if (!($image instanceof DeferredImageInterface)) {
                continue;
            }

            $this->resizer->resizeDeferredImage($image);
        }
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
