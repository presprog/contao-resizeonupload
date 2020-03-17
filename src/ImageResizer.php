<?php
/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Zinke <bz@presentprogressive.de>
 */

declare(strict_types=1);

namespace PresProg\ContaoResizeOnUploadBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Contao\FilesModel;
use Contao\StringUtil;
use Psr\Log\LoggerInterface;

/**
 * Class ImageResizer
 * @package PresProg\ContaoResizeOnUploadBundle
 */
class ImageResizer
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /** @var FilesModel */
    private $filesModel;
    /**
     * @var ImageFactoryInterface
     */
    private $imageFactory;
    /**
     * @var PictureFactoryInterface
     */
    private $pictureFactory;
    /**
     * @var array
     */
    private $validImageExtensions;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var array $imageSizes */
    private $imageSizes = [];

    public function __construct(ContaoFrameworkInterface $framework, ImageFactoryInterface $imageFactory, PictureFactoryInterface $pictureFactory, LoggerInterface $logger, array $validImageExtensions)
    {
        $this->framework = $framework;

        $this->imageFactory = $imageFactory;
        $this->pictureFactory = $pictureFactory;
        $this->logger = $logger;

        $this->filesModel = $this->framework->getAdapter(FilesModel::class);
        $this->validImageExtensions = $validImageExtensions;
    }

    /**
     * Overwrite default logger
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Resize a file by the given image sizes.
     * @param FilesModel $objFile Collection of uploaded files
     * @param array $imageSizes Array of image sizes to be used with the resizing services
     * @return void
     */
    public function resizeImage(FilesModel $objFile, array $imageSizes)
    {
        // skip non-image files
        if (!$this->isImage($objFile)) {
            return;
        }

        foreach ($imageSizes as $size) {
            try {
                $this->imageFactory->create(TL_ROOT . '/' . $objFile->path, $size)->getUrl(TL_ROOT);
                $this->pictureFactory->create(TL_ROOT . '/' . $objFile->path, $size);
                $this->logger->notice('Created thumb (ID ' . $size . ') for image ' . $objFile->path);
            } catch (\Exception $e) {
                $this->logger->error('Image "' . $objFile->path . '" could not be processed: ' . $e->getMessage());
            }
        }
    }

    /**
     * @return FilesModel|FilesModel[]|\Contao\Model\Collection|null
     */
    public function getAllFoldersWithImageSizes()
    {
        return $this->filesModel->findBy(['type = ?', 'sizes IS NOT NULL'], ['folder'], ['return' => 'Collection']);
    }

    /**
     * @param string $path
     * @param array $sizes
     * @return array
     */
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

        $objFolders = $this->getAllFoldersWithImageSizes();

        foreach ($objFolders as $objFolder) {
            $deserialize = StringUtil::deserialize($objFolder->sizes, true);
            $this->imageSizes[$objFolder->path] = $deserialize;
        }
    }

    /**
     * Check if the given file object is an image.
     * @param $objFile
     * @return bool
     */
    private function isImage($objFile)
    {
        return (in_array($objFile->extension, $this->validImageExtensions));
    }
}
